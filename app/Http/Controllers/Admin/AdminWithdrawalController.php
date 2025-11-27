<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    /**
     * Display list of withdrawal requests with search and filters
     */
    public function index(Request $request)
    {
        $query = WithdrawRequest::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('wallet_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        if (in_array($sortBy, ['created_at', 'token_amount', 'status'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        return view('dashboard.admin-withdrawals', compact('withdrawals'));
    }

    /**
     * Show withdrawal request details
     */
    public function show(WithdrawRequest $withdrawal)
    {
        $withdrawal->load('user');
        return view('dashboard.admin-withdrawal-details', compact('withdrawal'));
    }

    /**
     * Approve withdrawal request
     */
    public function approve(Request $request, WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Withdrawal already processed.');
        }

        $user = $withdrawal->user;

        // Update withdrawal status
        $withdrawal->update([
            'status' => 'approved',
            'notes' => $request->input('notes', $withdrawal->notes)
        ]);

        // Log transaction (tokens already deducted on submission)
        try {
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_approved',
                'amount' => (int) $withdrawal->token_amount,
                'status' => 'completed',
                'reference' => 'WDR-APPROVED-' . $withdrawal->id,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log withdrawal approval transaction', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawal->id,
            ]);
        }

        // Send email notification
        try {
            $withdrawal->refresh();
            
            Mail::send('emails.withdrawal-approved', [
                'user' => $user,
                'withdrawal' => $withdrawal,
            ], function($m) use ($user) {
                $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                  ->to($user->email, $user->name)
                  ->subject('Withdrawal Request Approved - RWAMP');
            });
            
            Log::info('Withdrawal approval email sent successfully', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send withdrawal approval email', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawal->id,
            ]);
        }

        return back()->with('success', 'Withdrawal approved. Tokens were already deducted on submission. Admin will transfer manually within 24 hours. User will be notified via email.');
    }

    /**
     * Reject withdrawal request
     */
    public function reject(Request $request, WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Withdrawal already processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $user = $withdrawal->user;

        // Update withdrawal status
        $withdrawal->update([
            'status' => 'rejected',
            'notes' => $validated['rejection_reason'] ?? 'Withdrawal request rejected by admin.'
        ]);

        // Return tokens to user since withdrawal was rejected
        $user->addTokens($withdrawal->token_amount, 'Withdrawal request rejected - tokens refunded - WDR-' . $withdrawal->id);

        // Log transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal_refund',
            'amount' => (int) $withdrawal->token_amount,
            'status' => 'completed',
            'reference' => 'WDR-REFUND-' . $withdrawal->id,
        ]);

        // Send email notification
        try {
            $withdrawal->refresh();
            
            Mail::send('emails.withdrawal-rejected', [
                'user' => $user,
                'withdrawal' => $withdrawal,
                'reason' => $validated['rejection_reason'] ?? 'Withdrawal request rejected by admin.',
            ], function($m) use ($user) {
                $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                  ->to($user->email, $user->name)
                  ->subject('Withdrawal Request Rejected - RWAMP');
            });
            
            Log::info('Withdrawal rejection email sent successfully', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send withdrawal rejection email', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawal->id,
            ]);
        }

        return back()->with('success', 'Withdrawal rejected. User will be notified via email.');
    }

    /**
     * Update withdrawal request
     */
    public function update(Request $request, WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Cannot edit processed withdrawal.');
        }

        $validated = $request->validate([
            'wallet_address' => 'required|string|max:255',
            'token_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $withdrawal->user;
        $oldAmount = $withdrawal->token_amount;
        $newAmount = $validated['token_amount'];
        $amountDifference = $newAmount - $oldAmount;

        DB::beginTransaction();
        try {
            if ($amountDifference > 0) {
                // Amount increased - deduct additional tokens
                $additionalAmount = $amountDifference;
                
                if ($user->token_balance < $additionalAmount) {
                    DB::rollBack();
                    return back()->with('error', 'User has insufficient balance for the increased amount. User has ' . number_format($user->token_balance, 2) . ' RWAMP tokens available.');
                }

                $user->deductTokens($additionalAmount, 'Withdrawal request amount increased - additional tokens deducted - WDR-' . $withdrawal->id);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal_adjustment',
                    'amount' => (int) $additionalAmount,
                    'status' => 'completed',
                    'reference' => 'WDR-INCREASE-' . $withdrawal->id,
                ]);
            } elseif ($amountDifference < 0) {
                // Amount decreased - refund the difference
                $refundAmount = abs($amountDifference);
                
                $user->addTokens($refundAmount, 'Withdrawal request amount decreased - tokens refunded - WDR-' . $withdrawal->id);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal_refund',
                    'amount' => (int) $refundAmount,
                    'status' => 'completed',
                    'reference' => 'WDR-DECREASE-REFUND-' . $withdrawal->id,
                ]);
            }

            $oldWalletAddress = $withdrawal->wallet_address;
            $oldNotes = $withdrawal->notes;

            $withdrawal->update($validated);
            $withdrawal->refresh();

            DB::commit();

            // Send email notification
            try {
                $changes = [];
                if ($oldAmount != $newAmount) {
                    $changes[] = 'Amount changed from ' . number_format($oldAmount, 2) . ' to ' . number_format($newAmount, 2) . ' RWAMP';
                }
                if ($oldWalletAddress != $validated['wallet_address']) {
                    $changes[] = 'Wallet address updated';
                }
                if ($oldNotes != ($validated['notes'] ?? '')) {
                    $changes[] = 'Notes updated';
                }

                Mail::send('emails.withdrawal-updated', [
                    'user' => $user,
                    'withdrawal' => $withdrawal,
                    'oldAmount' => $oldAmount,
                    'newAmount' => $newAmount,
                    'amountDifference' => $amountDifference,
                    'changes' => $changes,
                ], function($m) use ($user) {
                    $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                      ->to($user->email, $user->name)
                      ->subject('Withdrawal Request Updated - RWAMP');
                });
                
                Log::info('Withdrawal update email sent successfully', [
                    'user_id' => $user->id,
                    'withdrawal_id' => $withdrawal->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send withdrawal update email', [
                    'error' => $e->getMessage(),
                    'withdrawal_id' => $withdrawal->id,
                ]);
            }

            $message = 'Withdrawal request updated successfully.';
            if ($amountDifference > 0) {
                $message .= ' ' . number_format($additionalAmount, 2) . ' additional RWAMP tokens have been deducted from the user\'s balance.';
            } elseif ($amountDifference < 0) {
                $message .= ' ' . number_format($refundAmount, 2) . ' RWAMP tokens have been refunded to the user\'s balance.';
            }
            $message .= ' User has been notified via email.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update withdrawal request', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawal->id,
            ]);
            return back()->with('error', 'Failed to update withdrawal request. Please try again.');
        }
    }

    /**
     * Delete withdrawal request
     */
    public function destroy(Request $request, WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Cannot delete processed withdrawal.');
        }

        $validated = $request->validate([
            'deletion_reason' => 'nullable|string|max:500',
        ]);

        $user = $withdrawal->user;
        $tokenAmount = $withdrawal->token_amount;
        $withdrawalId = $withdrawal->id;
        $deletionReason = $validated['deletion_reason'] ?? 'Withdrawal request deleted by admin.';

        $withdrawalData = [
            'id' => $withdrawal->id,
            'token_amount' => $withdrawal->token_amount,
            'wallet_address' => $withdrawal->wallet_address,
            'created_at' => $withdrawal->created_at,
        ];

        DB::beginTransaction();
        try {
            // Refund tokens to user before deletion
            $user->addTokens($tokenAmount, 'Withdrawal request deleted - tokens refunded - WDR-' . $withdrawalId);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_refund',
                'amount' => (int) $tokenAmount,
                'status' => 'completed',
                'reference' => 'WDR-DELETE-REFUND-' . $withdrawalId,
            ]);

            $withdrawal->delete();

            DB::commit();

            // Send email notification
            try {
                Mail::send('emails.withdrawal-deleted', [
                    'user' => $user,
                    'withdrawal' => (object) $withdrawalData,
                    'reason' => $deletionReason,
                ], function($m) use ($user) {
                    $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                      ->to($user->email, $user->name)
                      ->subject('Withdrawal Request Deleted - RWAMP');
                });
                
                Log::info('Withdrawal deletion email sent successfully', [
                    'user_id' => $user->id,
                    'withdrawal_id' => $withdrawalId,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send withdrawal deletion email', [
                    'error' => $e->getMessage(),
                    'withdrawal_id' => $withdrawalId,
                ]);
            }

            return back()->with('success', 'Withdrawal request deleted successfully. ' . number_format($tokenAmount, 2) . ' RWAMP tokens have been refunded to the user. User has been notified via email.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete withdrawal request', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawalId,
            ]);
            return back()->with('error', 'Failed to delete withdrawal request. Please try again.');
        }
    }

    /**
     * Submit receipt for withdrawal transfer
     */
    public function submitReceipt(Request $request, WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'approved') {
            return back()->with('error', 'Can only submit receipt for approved withdrawals.');
        }

        $validated = $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'transaction_hash' => 'nullable|string|max:255',
        ]);

        $user = $withdrawal->user;

        try {
            $receiptPath = $request->file('receipt')->store('withdrawal-receipts', 'public');

            $withdrawal->update([
                'receipt_path' => $receiptPath,
                'transaction_hash' => $validated['transaction_hash'] ?? null,
                'transfer_completed_at' => now(),
            ]);

            $withdrawal->refresh();

            // Send email notification
            try {
                Mail::send('emails.withdrawal-completed', [
                    'user' => $user,
                    'withdrawal' => $withdrawal,
                ], function($m) use ($user) {
                    $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                      ->to($user->email, $user->name)
                      ->subject('Withdrawal Transfer Completed - RWAMP');
                });
                
                Log::info('Withdrawal completion email sent successfully', [
                    'user_id' => $user->id,
                    'withdrawal_id' => $withdrawal->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send withdrawal completion email', [
                    'error' => $e->getMessage(),
                    'withdrawal_id' => $withdrawal->id,
                ]);
            }

            return back()->with('success', 'Receipt submitted successfully. User has been notified via email.');
        } catch (\Exception $e) {
            Log::error('Failed to submit withdrawal receipt', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawal->id,
            ]);
            return back()->with('error', 'Failed to submit receipt. Please try again.');
        }
    }
}


<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use App\Models\PaymentSubmission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResellerPaymentController extends Controller
{
    /**
     * Display all pending payments page
     */
    public function index(Request $request)
    {
        $reseller = Auth::user();
        
        $query = CryptoPayment::whereHas('user', function($q) use ($reseller) {
            $q->where('reseller_id', $reseller->id);
        })->with('user');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        // Bank / manual payment submissions assigned to this reseller
        $bankQuery = PaymentSubmission::with('user')
            ->where('recipient_type', 'reseller')
            ->where('recipient_id', $reseller->id);

        // Reuse same filters where it makes sense
        if ($request->has('status') && $request->status) {
            $bankQuery->where('status', $request->status);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $bankQuery->where(function($q) use ($search) {
                $q->where('bank_reference', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $bankSubmissions = $bankQuery->latest()->paginate(20, ['*'], 'bank_submissions')->withQueryString();

        return view('dashboard.reseller-payments', compact('payments', 'bankSubmissions'));
    }

    /**
     * View crypto payment details
     */
    public function show(CryptoPayment $payment)
    {
        $reseller = Auth::user();
        
        // Verify payment belongs to reseller's user
        if ($payment->user->reseller_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        $payment->load('user');

        return view('dashboard.reseller-payment-view', compact('payment'));
    }

    /**
     * Reject crypto payment
     */
    public function reject(Request $request, CryptoPayment $payment)
    {
        $reseller = Auth::user();
        
        // Verify payment belongs to reseller's user
        if ($payment->user->reseller_id !== $reseller->id) {
            return back()->withErrors(['message' => 'Unauthorized']);
        }

        if ($payment->status !== 'pending') {
            return back()->withErrors(['message' => 'Payment already processed']);
        }

        $payment->update([
            'status' => 'rejected',
            'notes' => $request->notes ?? $payment->notes
        ]);

        return back()->with('success', 'Payment rejected successfully.');
    }

    /**
     * Approve crypto payment for own user
     */
    public function approve(Request $request, CryptoPayment $payment)
    {
        $reseller = Auth::user();

        // Verify payment belongs to reseller's user
        if ($payment->user->reseller_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only approve payments for your own users.'
            ], 403);
        }

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already processed.'
            ], 400);
        }

        $payment->update(['status' => 'approved']);

        // Credit tokens to user
        $user = $payment->user;
        $user->addTokens((int) $payment->token_amount, 'Crypto purchase approved by reseller');

        // Log transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'crypto_purchase',
            'amount' => (int) $payment->token_amount,
            'status' => 'completed',
            'reference' => $payment->tx_hash,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment approved and tokens credited.'
        ]);
    }

    /**
     * Approve a manual/bank PaymentSubmission and credit tokens.
     */
    public function approveBank(Request $request, PaymentSubmission $submission)
    {
        $reseller = Auth::user();

        if ($submission->recipient_type !== 'reseller' || $submission->recipient_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only approve bank submissions assigned to you.',
            ], 403);
        }

        if ($submission->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This bank submission has already been processed.',
            ], 400);
        }

        DB::transaction(function () use ($submission, $reseller) {
            $user        = $submission->user;
            $tokenAmount = (int) $submission->token_amount;

            if ($reseller->token_balance < $tokenAmount) {
                throw new \RuntimeException('Insufficient reseller balance to approve this payment.');
            }

            // Update submission status
            $submission->update(['status' => 'approved']);

            // Transfer tokens: reseller -> user
            $reseller->decrement('token_balance', $tokenAmount);
            $user->addTokens($tokenAmount, 'Bank transfer approved by reseller');

            $pricePerCoin = $submission->token_amount > 0
                ? round($submission->fiat_amount / $submission->token_amount, 4)
                : null;

            Transaction::create([
                'user_id'        => $user->id,
                'sender_id'      => $reseller->id,
                'recipient_id'   => $user->id,
                'sender_type'    => 'reseller',
                'type'           => 'crypto_purchase',
                'amount'         => $tokenAmount,
                'price_per_coin' => $pricePerCoin,
                'total_price'    => $submission->fiat_amount,
                'status'         => 'completed',
                'reference'      => 'BANK-' . $submission->id,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Bank payment approved and tokens credited.',
        ]);
    }

    /**
     * Reject a manual/bank PaymentSubmission with a reason.
     */
    public function rejectBank(Request $request, PaymentSubmission $submission)
    {
        $reseller = Auth::user();

        if ($submission->recipient_type !== 'reseller' || $submission->recipient_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only reject bank submissions assigned to you.',
            ], 403);
        }

        if ($submission->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This bank submission has already been processed.',
            ], 400);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $submission->update([
            'status'      => 'rejected',
            'admin_notes' => $data['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank payment rejected.',
        ]);
    }

    /**
     * Fetch user's payment proof based on payment type
     */
    public function fetchUserPaymentProof(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type' => 'required|in:usdt,bank,cash',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $paymentType = $validated['payment_type'];

        $proof = null;
        $message = '';

        if ($paymentType === 'usdt') {
            // Fetch latest USDT transaction hash from user's crypto_payments
            $cryptoPayment = CryptoPayment::where('user_id', $user->id)
                ->whereIn('network', ['TRC20', 'ERC20', 'BEP20'])
                ->whereNotNull('tx_hash')
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($cryptoPayment) {
                $proof = [
                    'type' => 'usdt',
                    'tx_hash' => $cryptoPayment->tx_hash,
                    'network' => $cryptoPayment->network,
                    'amount' => $cryptoPayment->token_amount,
                    'date' => $cryptoPayment->created_at->format('Y-m-d H:i'),
                ];
                $message = 'USDT transaction hash found';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved USDT payment found for this user. Please check user\'s payment history.',
                ], 404);
            }
        } elseif ($paymentType === 'bank') {
            // Fetch latest bank receipt/screenshot from user's crypto_payments
            $cryptoPayment = CryptoPayment::where('user_id', $user->id)
                ->whereNotNull('screenshot')
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($cryptoPayment && $cryptoPayment->screenshot) {
                $proof = [
                    'type' => 'bank',
                    'screenshot' => $cryptoPayment->screenshot,
                    'amount' => $cryptoPayment->token_amount,
                    'date' => $cryptoPayment->created_at->format('Y-m-d H:i'),
                ];
                $message = 'Bank receipt found';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved bank payment receipt found for this user. Please check user\'s payment history.',
                ], 404);
            }
        } elseif ($paymentType === 'cash') {
            // Cash doesn't need proof
            $proof = [
                'type' => 'cash',
                'message' => 'Cash payment - no digital proof required',
            ];
            $message = 'Cash payment selected';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'proof' => $proof,
        ]);
    }
}

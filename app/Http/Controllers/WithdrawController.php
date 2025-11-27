<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WithdrawController extends Controller
{
    /**
     * Store withdrawal request
     */
    public function store(Request $request)
    {
        // Force JSON response
        $request->headers->set('Accept', 'application/json');
        
        try {
            $user = Auth::user();

            // KYC gate - withdrawals require approved KYC
            if ($user->kyc_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC verification required for withdrawals. Please complete your KYC verification first.'
                ], 403);
            }

            $validated = $request->validate([
                'wallet_address' => 'required|string|max:255',
                'token_amount' => 'required|numeric|min:0.01',
            ]);

            $amount = (float) $validated['token_amount'];

            // Validate amount
            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal amount must be greater than 0.'
                ], 422);
            }

            if ($amount > $user->token_balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient token balance. You have ' . number_format($user->token_balance, 2) . ' RWAMP tokens available.'
                ], 422);
            }

            // Create withdrawal request and deduct tokens immediately
            DB::beginTransaction();
            try {
                // Deduct tokens from user balance immediately
                $user->deductTokens($amount, 'Withdrawal request submitted - WDR-' . time());
                
                // Refresh user to get updated balance
                $user->refresh();
                
                // Create withdrawal request
                $withdrawRequest = WithdrawRequest::create([
                    'user_id' => $user->id,
                    'wallet_address' => $validated['wallet_address'],
                    'token_amount' => $amount,
                    'status' => 'pending',
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Withdrawal request submitted successfully. ' . number_format($amount, 2) . ' RWAMP tokens have been deducted from your balance. It will be processed manually by admin within 24 hours.',
                    'request' => $withdrawRequest,
                    'new_balance' => $user->token_balance,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $firstError = collect($errors)->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => $firstError ?: 'Validation failed. Please check your input.',
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Withdrawal request error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your withdrawal request. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * List user's withdrawal requests
     */
    public function index()
    {
        $user = Auth::user();
        $withdrawals = WithdrawRequest::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        // Get official coin price for value calculation
        $officialPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();

        return view('dashboard.user-withdrawals', compact('withdrawals', 'user', 'officialPrice'));
    }

    /**
     * View/download receipt for withdrawal
     */
    public function viewReceipt(WithdrawRequest $withdrawal)
    {
        $user = Auth::user();

        // Ensure user can only view their own withdrawal receipts
        if ($withdrawal->user_id !== $user->id) {
            abort(403, 'Unauthorized access to withdrawal receipt.');
        }

        // Check if receipt exists
        if (!$withdrawal->receipt_path) {
            abort(404, 'Receipt not found for this withdrawal.');
        }

        // Check if file exists in storage
        $filePath = storage_path('app/public/' . $withdrawal->receipt_path);
        if (!file_exists($filePath)) {
            abort(404, 'Receipt file not found.');
        }

        // Determine content type based on file extension
        $extension = pathinfo($withdrawal->receipt_path, PATHINFO_EXTENSION);
        $contentType = match(strtolower($extension)) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        // Return file response
        return response()->file($filePath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="withdrawal-receipt-' . $withdrawal->id . '.' . $extension . '"',
        ]);
    }
}


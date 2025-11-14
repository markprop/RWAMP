<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    /**
     * Store withdrawal request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // KYC gate - withdrawals require approved KYC
        abort_if($user->kyc_status !== 'approved', 403, 'KYC verification required for withdrawals.');

        $validated = $request->validate([
            'wallet_address' => 'required|string|max:255',
            'token_amount' => 'required|numeric|min:1',
        ]);

        $amount = (float) $validated['token_amount'];
        $maxWithdrawable = $user->token_balance * 0.90; // 90% max, 10% reserve

        // Validate amount
        if ($amount > $maxWithdrawable) {
            return response()->json([
                'success' => false,
                'message' => "Maximum withdrawable amount is " . number_format($maxWithdrawable, 2) . " tokens (90% of balance)."
            ], 422);
        }

        if ($amount > $user->token_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient token balance.'
            ], 422);
        }

        // Create withdrawal request
        $withdrawRequest = WithdrawRequest::create([
            'user_id' => $user->id,
            'wallet_address' => $validated['wallet_address'],
            'token_amount' => $amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted. It will be processed manually by admin.',
            'request' => $withdrawRequest,
        ]);
    }
}


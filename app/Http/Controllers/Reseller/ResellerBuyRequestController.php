<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\BuyFromResellerRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResellerBuyRequestController extends Controller
{
    /**
     * List buy requests from users
     */
    public function index(Request $request)
    {
        $reseller = Auth::user();
        
        $query = BuyFromResellerRequest::where('reseller_id', $reseller->id)
            ->with('user')
            ->latest();

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        return view('dashboard.reseller-buy-requests', compact('requests'));
    }

    /**
     * Approve buy request
     */
    public function approve(Request $request, BuyFromResellerRequest $buyRequest)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:usdt,bank,cash',
        ]);

        $reseller = Auth::user();

        // Verify request belongs to this reseller
        if ($buyRequest->reseller_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Verify request is pending
        if ($buyRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request is not pending.'
            ], 400);
        }

        // Note: Balance check removed - resellers can approve requests regardless of balance
        // Resellers can see their own balance in the dashboard when deciding to approve

        try {
            DB::transaction(function() use ($reseller, $buyRequest, $validated) {
                // Deduct from reseller
                $reseller->decrement('token_balance', $buyRequest->coin_quantity);
                
                // Add to user
                $buyRequest->user->increment('token_balance', $buyRequest->coin_quantity);
                
                // Log transaction for reseller (debit)
                Transaction::create([
                    'user_id' => $reseller->id,
                    'sender_id' => $reseller->id,
                    'recipient_id' => $buyRequest->user->id,
                    'type' => 'reseller_sell',
                    'amount' => -$buyRequest->coin_quantity, // Negative for debit
                    'price_per_coin' => $buyRequest->coin_price,
                    'total_price' => $buyRequest->total_amount,
                    'payment_type' => $validated['payment_method'],
                    'payment_status' => $validated['payment_method'] === 'cash' ? 'verified' : 'pending',
                    'sender_type' => 'reseller',
                    'status' => 'completed',
                    'reference' => 'BUY-REQ-' . $buyRequest->id,
                ]);
                
                // Log transaction for user (credit)
                Transaction::create([
                    'user_id' => $buyRequest->user->id,
                    'sender_id' => $reseller->id,
                    'recipient_id' => $buyRequest->user->id,
                    'type' => 'buy_from_reseller',
                    'amount' => $buyRequest->coin_quantity,
                    'price_per_coin' => $buyRequest->coin_price,
                    'total_price' => $buyRequest->total_amount,
                    'payment_type' => $validated['payment_method'],
                    'payment_status' => $validated['payment_method'] === 'cash' ? 'verified' : 'pending',
                    'sender_type' => 'reseller',
                    'status' => 'completed',
                    'reference' => 'BUY-REQ-' . $buyRequest->id,
                ]);

                // Update request
                $buyRequest->status = 'completed';
                $buyRequest->approved_at = now();
                $buyRequest->completed_at = now();
                $buyRequest->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Buy request approved and tokens transferred successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Approve buy request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request. Please try again.'
            ], 500);
        }
    }

    /**
     * Reject buy request
     */
    public function reject(Request $request, BuyFromResellerRequest $buyRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reseller = Auth::user();

        // Verify request belongs to this reseller
        if ($buyRequest->reseller_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Verify request is pending
        if ($buyRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request is not pending.'
            ], 400);
        }

        $buyRequest->status = 'rejected';
        $buyRequest->rejection_reason = $validated['rejection_reason'] ?? null;
        $buyRequest->rejected_at = now();
        $buyRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Buy request rejected.'
        ]);
    }
}


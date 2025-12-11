<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerBuyTransactionController extends Controller
{
    /**
     * Display reseller buy transactions (when reseller buys coins from admin/other resellers)
     */
    public function index(Request $request)
    {
        $reseller = Auth::user();
        
        // Get all crypto payments made by this reseller
        $query = CryptoPayment::where('user_id', $reseller->id);

        // Search by transaction hash, network, or amount
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%")
                  ->orWhere('network', 'like', "%{$search}%")
                  ->orWhere('token_amount', 'like', "%{$search}%")
                  ->orWhere('usd_amount', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Filter by network
        if ($request->filled('network') && in_array($request->network, ['TRC20', 'ERC20', 'BEP20', 'BTC', 'BNB'])) {
            $query->where('network', $request->network);
        }

        // Get all payments first (before pagination) to backfill transactions
        $allPayments = $query->get();
        
        // Backfill missing Transaction records for existing payments
        foreach ($allPayments as $payment) {
            if ($payment->tx_hash) {
                $existingTransaction = Transaction::where('reference', $payment->tx_hash)
                    ->where('user_id', $reseller->id)
                    ->where('type', 'crypto_purchase')
                    ->first();
                
                if (!$existingTransaction) {
                    // Create missing transaction record
                    $tokenAmount = (float) $payment->token_amount;
                    $pkrAmount = (float) $payment->pkr_amount;
                    $pricePerCoin = $tokenAmount > 0 ? ($pkrAmount / $tokenAmount) : 0;
                    
                    Transaction::create([
                        'user_id' => $reseller->id,
                        'type' => 'crypto_purchase',
                        'amount' => $tokenAmount,
                        'price_per_coin' => $pricePerCoin,
                        'total_price' => $pkrAmount,
                        'status' => $payment->status === 'approved' ? 'completed' : ($payment->status === 'rejected' ? 'rejected' : 'pending'),
                        'reference' => $payment->tx_hash,
                        'payment_type' => strtolower($payment->network),
                        'payment_hash' => $payment->tx_hash,
                        'payment_status' => $payment->status === 'approved' ? 'verified' : ($payment->status === 'rejected' ? 'rejected' : 'pending'),
                    ]);
                }
            }
        }
        
        // Now paginate the results
        $payments = $query->latest()->paginate(20)->withQueryString();

        // Get corresponding transaction records for additional details
        $transactions = Transaction::whereIn('reference', $payments->pluck('tx_hash'))
            ->where('user_id', $reseller->id)
            ->where('type', 'crypto_purchase')
            ->get()
            ->keyBy('reference');

        return view('dashboard.reseller-buy-transactions', compact('payments', 'transactions'));
    }

    /**
     * View buy transaction details
     */
    public function show(CryptoPayment $payment)
    {
        $reseller = Auth::user();
        
        // Verify payment belongs to reseller
        if ($payment->user_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        // Get corresponding transaction record
        $transaction = Transaction::where('reference', $payment->tx_hash)
            ->where('user_id', $reseller->id)
            ->where('type', 'crypto_purchase')
            ->first();

        return view('dashboard.reseller-buy-transaction-view', compact('payment', 'transaction'));
    }
}


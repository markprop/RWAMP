<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\BuyFromResellerRequest;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;

class InvestorHistoryController extends Controller
{
    /**
     * Display user payment and transaction history
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $currentCoinPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();

        // Payments query
        $paymentsQuery = CryptoPayment::where('user_id', $userId);
        
        // Search payments
        if ($request->filled('payment_search')) {
            $search = $request->payment_search;
            $paymentsQuery->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%");
            });
        }

        // Filter payments by status
        if ($request->filled('payment_status') && in_array($request->payment_status, ['pending', 'approved', 'rejected'])) {
            $paymentsQuery->where('status', $request->payment_status);
        }

        // Filter payments by network
        if ($request->filled('payment_network') && in_array($request->payment_network, ['TRC20', 'ERC20', 'BEP20', 'BTC', 'BNB'])) {
            $paymentsQuery->where('network', $request->payment_network);
        }

        // Sort payments
        $paymentSort = $request->get('payment_sort', 'created_at');
        $paymentDir = $request->get('payment_dir', 'desc');
        if (in_array($paymentSort, ['created_at', 'token_amount', 'status', 'network'])) {
            $paymentsQuery->orderBy($paymentSort, $paymentDir);
        } else {
            $paymentsQuery->latest();
        }

        $payments = $paymentsQuery->paginate(20, ['*'], 'payments')->withQueryString();

        // Transactions query
        $transactionsQuery = Transaction::where('user_id', $userId);
        
        // Search transactions
        if ($request->filled('transaction_search')) {
            $search = $request->transaction_search;
            $transactionsQuery->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Filter transactions by type
        if ($request->filled('transaction_type')) {
            $transactionsQuery->where('type', $request->transaction_type);
        }

        // Filter transactions by status
        if ($request->filled('transaction_status') && in_array($request->transaction_status, ['pending', 'completed', 'failed'])) {
            $transactionsQuery->where('status', $request->transaction_status);
        }

        // Sort transactions
        $transactionSort = $request->get('transaction_sort', 'created_at');
        $transactionDir = $request->get('transaction_dir', 'desc');
        if (in_array($transactionSort, ['created_at', 'type', 'amount', 'status'])) {
            $transactionsQuery->orderBy($transactionSort, $transactionDir);
        } else {
            $transactionsQuery->latest();
        }

        $transactions = $transactionsQuery->paginate(20, ['*'], 'transactions')->withQueryString();

        // Buy Requests query
        $buyRequestsQuery = BuyFromResellerRequest::where('user_id', $userId)
            ->with('reseller');
        
        // Search buy requests
        if ($request->filled('buy_request_search')) {
            $search = $request->buy_request_search;
            $buyRequestsQuery->where(function($q) use ($search) {
                $q->whereHas('reseller', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Filter buy requests by status
        if ($request->filled('buy_request_status') && in_array($request->buy_request_status, ['pending', 'approved', 'rejected', 'completed'])) {
            $buyRequestsQuery->where('status', $request->buy_request_status);
        }

        // Sort buy requests
        $buyRequestSort = $request->get('buy_request_sort', 'created_at');
        $buyRequestDir = $request->get('buy_request_dir', 'desc');
        if (in_array($buyRequestSort, ['created_at', 'coin_quantity', 'total_amount', 'status'])) {
            $buyRequestsQuery->orderBy($buyRequestSort, $buyRequestDir);
        } else {
            $buyRequestsQuery->latest();
        }

        $buyRequests = $buyRequestsQuery->paginate(20, ['*'], 'buy_requests')->withQueryString();

        return view('dashboard.user-history', compact('payments','transactions','buyRequests','currentCoinPrice'));
    }
}


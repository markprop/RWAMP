<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerTransactionController extends Controller
{
    /**
     * Display all transactions page
     */
    public function index(Request $request)
    {
        $reseller = Auth::user();
        
        if (!$reseller || !$reseller->id) {
            abort(403, 'Unauthorized');
        }
        
        $query = Transaction::where('user_id', $reseller->id);

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        return view('dashboard.reseller-transactions', compact('transactions'));
    }

    /**
     * View transaction details
     * Supports both ULID and numeric ID for backward compatibility
     */
    public function show($transaction)
    {
        $reseller = Auth::user();
        
        // Resolve transaction by ULID or numeric ID
        if ($transaction instanceof Transaction) {
            $transactionModel = $transaction;
        } else {
            // Try ULID first (26 characters, alphanumeric)
            if (is_string($transaction) && strlen($transaction) === 26 && ctype_alnum($transaction)) {
                $transactionModel = Transaction::where('ulid', $transaction)->first();
            } 
            // Try numeric ID
            elseif (is_numeric($transaction)) {
                $transactionModel = Transaction::find((int) $transaction);
            } 
            // Last attempt: try both
            else {
                $transactionModel = Transaction::where('ulid', $transaction)
                    ->orWhere('id', $transaction)
                    ->first();
            }
            
            if (!$transactionModel) {
                abort(404, 'Transaction not found.');
            }
        }
        
        // Verify transaction belongs to reseller
        if ($transactionModel->user_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        // Eager load related users so we can show clean sender/receiver info
        $transactionModel->loadMissing(['sender', 'recipient']);

        return view('dashboard.reseller-transaction-view', ['transaction' => $transactionModel]);
    }
}


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
     */
    public function show(Transaction $transaction)
    {
        $reseller = Auth::user();
        
        // Verify transaction belongs to reseller
        if ($transaction->user_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        // Eager load related users so we can show clean sender/receiver info
        $transaction->loadMissing(['sender', 'recipient']);

        return view('dashboard.reseller-transaction-view', compact('transaction'));
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Helpers\PriceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminCryptoPaymentController extends Controller
{
    /**
     * Display list of crypto payments with search and filters
     */
    public function index(Request $request)
    {
        $query = CryptoPayment::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%")
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

        // Filter by network
        if ($request->filled('network') && in_array($request->network, ['TRC20', 'ERC20', 'BEP20', 'BTC', 'BNB'])) {
            $query->where('network', $request->network);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        if (in_array($sortBy, ['created_at', 'token_amount', 'status', 'network'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('dashboard.admin-crypto', compact('payments'));
    }

    /**
     * Show payment details
     */
    public function show(CryptoPayment $payment)
    {
        $payment->load('user');
        return response()->json([
            'payment' => $payment,
            'user' => $payment->user,
        ]);
    }

    /**
     * Approve crypto payment
     */
    public function approve(Request $request, CryptoPayment $payment)
    {
        if ($payment->status === 'approved') {
            return back()->with('success', 'Payment already approved.');
        }

        $payment->update(['status' => 'approved']);

        // Credit tokens to the user account
        $user = $payment->user;
        if ($user) {
            $user->addTokens((int) $payment->token_amount, 'Crypto purchase approved');

            // Log a transaction record
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'crypto_purchase',
                'amount' => (int) $payment->token_amount,
                'status' => 'completed',
                'reference' => $payment->tx_hash,
            ]);

            // Award commission to reseller if applicable
            if ($user->reseller_id && !$payment->reseller_commission_awarded) {
                $reseller = User::find($user->reseller_id);
                if ($reseller && $reseller->role === 'reseller') {
                    $commissionRate = PriceHelper::getResellerCommissionRate();
                    $commission = (float) $payment->token_amount * $commissionRate;
                    
                    // Award commission to reseller
                    $reseller->increment('token_balance', $commission);
                    
                    // Log commission transaction
                    Transaction::create([
                        'user_id' => $reseller->id,
                        'type' => 'commission',
                        'amount' => (int) $commission,
                        'status' => 'completed',
                        'reference' => 'COMM-' . $payment->id,
                    ]);
                    
                    // Mark commission as awarded
                    $payment->update(['reseller_commission_awarded' => true]);
                }
            }
        }

        return back()->with('success', 'Payment approved and tokens credited.');
    }

    /**
     * Reject crypto payment
     */
    public function reject(Request $request, CryptoPayment $payment)
    {
        $payment->update(['status' => 'rejected']);
        return back()->with('success', 'Payment rejected.');
    }

    /**
     * Update crypto payment
     */
    public function update(Request $request, CryptoPayment $payment)
    {
        $validated = $request->validate([
            'token_amount' => ['required', 'numeric', 'min:0'],
            'usd_amount' => ['nullable', 'string'],
            'pkr_amount' => ['nullable', 'string'],
            'network' => ['required', 'string', 'in:TRC20,ERC20,BEP20,BTC,BNB'],
            'tx_hash' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldStatus = $payment->status;
        $payment->update($validated);

        // If status changed from pending to approved, credit tokens
        if ($oldStatus !== 'approved' && $validated['status'] === 'approved') {
            $user = $payment->user;
            if ($user) {
                // Only credit if not already credited
                $existingTransaction = Transaction::where('reference', $payment->tx_hash)
                    ->where('type', 'crypto_purchase')
                    ->where('status', 'completed')
                    ->first();

                if (!$existingTransaction) {
                    $user->addTokens((int) $validated['token_amount'], 'Crypto purchase approved');
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'crypto_purchase',
                        'amount' => (int) $validated['token_amount'],
                        'status' => 'completed',
                        'reference' => $payment->tx_hash,
                    ]);
                }
            }
        }

        return back()->with('success', 'Payment updated successfully.');
    }

    /**
     * Delete crypto payment
     */
    public function destroy(Request $request, CryptoPayment $payment)
    {
        // Delete screenshot if exists
        if ($payment->screenshot) {
            try {
                Storage::disk('local')->delete($payment->screenshot);
            } catch (\Exception $e) {
                // Ignore if file doesn't exist
            }
        }

        $payment->delete();

        return back()->with('success', 'Payment deleted successfully.');
    }

    /**
     * Download payment screenshot
     */
    public function downloadScreenshot(CryptoPayment $payment)
    {
        if (!$payment->screenshot || !Storage::disk('local')->exists($payment->screenshot)) {
            abort(404, 'Screenshot not found');
        }

        $file = Storage::disk('local')->get($payment->screenshot);
        $mimeType = Storage::disk('local')->mimeType($payment->screenshot);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($payment->screenshot) . '"')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Display payment and transaction history
     */
    public function history(Request $request)
    {
        $currentCoinPrice = (float) (config('crypto.rates.coin_price_rs') ?? config('app.coin_price_rs') ?? 0.70);

        // Payments query
        $paymentsQuery = CryptoPayment::with('user');
        
        // Search payments
        if ($request->filled('payment_search')) {
            $search = $request->payment_search;
            $paymentsQuery->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
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
        $transactionsQuery = Transaction::with('user');
        
        // Search transactions
        if ($request->filled('transaction_search')) {
            $search = $request->transaction_search;
            $transactionsQuery->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
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

        return view('dashboard.admin-history', compact('payments', 'transactions', 'currentCoinPrice'));
    }
}


<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\BuyFromResellerRequest;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerDashboardController extends Controller
{
    /**
     * Display reseller dashboard
     */
    public function index(Request $request)
    {
        $reseller = Auth::user();
        
        // Get official coin price
        $officialPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();
        
        // Calculate average purchase price from all purchases
        $tokenBalance = $reseller->token_balance ?? 0;
        $averagePurchasePrice = $officialPrice; // Default to official price
        
        // Get all crypto payments where reseller purchased coins
        $cryptoPayments = CryptoPayment::where('user_id', $reseller->id)
            ->where('status', 'approved')
            ->whereNotNull('coin_price_rs')
            ->where('coin_price_rs', '>', 0)
            ->get();
        
        // Get all transactions where reseller received coins with price information
        $creditTransactions = Transaction::where('user_id', $reseller->id)
            ->whereIn('type', ['credit', 'crypto_purchase', 'commission', 'admin_transfer_credit'])
            ->where('status', 'completed')
            ->whereNotNull('price_per_coin')
            ->where('price_per_coin', '>', 0)
            ->where('amount', '>', 0) // Only positive amounts (credits)
            ->get();
        
        // Calculate weighted average purchase price
        $totalAmount = 0;
        $totalValue = 0;
        
        // Add crypto payments
        foreach ($cryptoPayments as $payment) {
            $amount = (float) $payment->token_amount;
            $price = (float) $payment->coin_price_rs;
            if ($amount > 0 && $price > 0) {
                $totalAmount += $amount;
                $totalValue += $amount * $price;
            }
        }
        
        // Add credit transactions
        foreach ($creditTransactions as $transaction) {
            $amount = abs((float) $transaction->amount); // Ensure positive
            $price = (float) $transaction->price_per_coin;
            if ($amount > 0 && $price > 0) {
                $totalAmount += $amount;
                $totalValue += $amount * $price;
            }
        }
        
        // Calculate average price
        if ($totalAmount > 0) {
            $averagePurchasePrice = $totalValue / $totalAmount;
        } elseif ($reseller->coin_price) {
            // Fallback to reseller's coin_price if no purchase history
            $averagePurchasePrice = $reseller->coin_price;
        }
        
        // Calculate portfolio values
        $portfolioValue = $tokenBalance * $averagePurchasePrice;
        $officialPortfolioValue = $tokenBalance * $officialPrice;
        
        // Calculate metrics
        $metrics = [
            'total_users' => User::where('reseller_id', $reseller->id)->count(),
            'total_payments' => CryptoPayment::whereHas('user', function($q) use ($reseller) {
                $q->where('reseller_id', $reseller->id);
            })->count(),
            'total_commission' => Transaction::where('user_id', $reseller->id)
                ->where('type', 'commission')
                ->sum('amount'),
            'token_balance' => $tokenBalance,
            'total_transactions' => Transaction::where('user_id', $reseller->id)->count(),
            'portfolio_value' => $portfolioValue,
            'official_portfolio_value' => $officialPortfolioValue,
            'average_purchase_price' => $averagePurchasePrice,
            'official_price' => $officialPrice,
        ];
        
        // Get my users
        $myUsers = User::where('reseller_id', $reseller->id)
            ->withCount(['transactions', 'cryptoPayments'])
            ->latest()
            ->paginate(20, ['*'], 'users_page');

        // Get pending buy requests from users
        $pendingBuyRequests = BuyFromResellerRequest::where('reseller_id', $reseller->id)
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Get all resellers for "Buy from Reseller" feature
        $allResellers = User::where('role', 'reseller')
            ->whereNotNull('referral_code')
            ->get();

        // Fix existing cash transactions: update payment_status from 'pending' to 'verified'
        // This is a one-time fix for existing records
        Transaction::where('payment_type', 'cash')
            ->where('payment_status', 'pending')
            ->where('status', 'completed')
            ->update(['payment_status' => 'verified']);

        // Get recent transactions - only reseller's own transactions
        // Exclude admin transactions (admin_transfer_debit, admin_transfer_credit)
        // Include: reseller_sell, admin_transfer_credit (when admin sends to reseller)
        $recentTransactions = Transaction::where('user_id', $reseller->id)
            ->where(function($query) {
                $query->where('type', 'reseller_sell')
                      ->orWhere(function($q) {
                          $q->where('type', 'admin_transfer_credit')
                            ->where('recipient_id', auth()->id());
                      })
                      ->orWhere('type', 'commission')
                      ->orWhere('type', 'credit')
                      ->orWhere('type', 'debit');
            })
            ->with(['sender', 'recipient'])
            ->latest()
            ->limit(20)
            ->get();

        return view('dashboard.reseller', compact('metrics', 'myUsers', 'pendingBuyRequests', 'allResellers', 'recentTransactions'));
    }
}


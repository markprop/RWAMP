<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\BuyFromResellerRequest;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;

class InvestorDashboardController extends Controller
{
    /**
     * Investor dashboard with compact history widgets.
     */
    public function index(Request $request)
    {
        $investor = $request->user();
        $userId = $investor->id;
        
        // Get official coin price
        $officialPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();
        
        // Calculate average purchase price from all purchases
        $tokenBalance = $investor->token_balance ?? 0;
        $averagePurchasePrice = $officialPrice; // Default to official price
        
        // Get all crypto payments where investor purchased coins
        $cryptoPayments = CryptoPayment::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereNotNull('coin_price_rs')
            ->where('coin_price_rs', '>', 0)
            ->get();
        
        // Get all transactions where investor received coins with price information
        $creditTransactions = Transaction::where('user_id', $userId)
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
        } elseif ($investor->coin_price) {
            // Fallback to investor's coin_price if no purchase history
            $averagePurchasePrice = $investor->coin_price;
        }
        
        // Calculate portfolio values
        $portfolioValue = $tokenBalance * $averagePurchasePrice;
        $officialPortfolioValue = $tokenBalance * $officialPrice;
        
        // Recent payment submissions (pending/approved/rejected)
        $paymentsRecent = CryptoPayment::where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        // Recent token balance transactions
        $transactionsRecent = Transaction::where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        // Get pending buy requests from resellers
        $pendingBuyRequests = BuyFromResellerRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('reseller')
            ->latest()
            ->get();

        $currentCoinPrice = (float) (config('crypto.rates.coin_price_rs') ?? config('app.coin_price_rs') ?? 0.70);
        
        // Prepare metrics for view
        $metrics = [
            'token_balance' => $tokenBalance,
            'portfolio_value' => $portfolioValue,
            'official_portfolio_value' => $officialPortfolioValue,
            'average_purchase_price' => $averagePurchasePrice,
            'official_price' => $officialPrice,
        ];

        return view('dashboard.investor', compact('paymentsRecent','transactionsRecent','pendingBuyRequests','currentCoinPrice','metrics'));
    }
}


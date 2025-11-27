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
        
        // Get all transactions where investor received coins (all purchase types)
        // Include: credit, crypto_purchase, admin_transfer_credit, buy_from_reseller
        $creditTransactions = Transaction::where('user_id', $userId)
            ->whereIn('type', ['credit', 'crypto_purchase', 'commission', 'admin_transfer_credit', 'buy_from_reseller'])
            ->where('status', 'completed')
            ->where('amount', '>', 0) // Only positive amounts (credits)
            ->get();
        
        // Calculate weighted average purchase price
        $totalAmount = 0;
        $totalValue = 0;
        
        // Add crypto payments (direct purchases)
        foreach ($cryptoPayments as $payment) {
            $amount = (float) $payment->token_amount;
            $price = (float) $payment->coin_price_rs;
            if ($amount > 0 && $price > 0) {
                $totalAmount += $amount;
                $totalValue += $amount * $price;
            }
        }
        
        // Add credit transactions (all purchase methods)
        foreach ($creditTransactions as $transaction) {
            $amount = abs((float) $transaction->amount); // Ensure positive
            
            // Try to get price from price_per_coin first
            $price = null;
            if (!empty($transaction->price_per_coin) && $transaction->price_per_coin > 0) {
                $price = (float) $transaction->price_per_coin;
            } 
            // If price_per_coin is not set, calculate from total_price / amount
            elseif (!empty($transaction->total_price) && $transaction->total_price > 0 && $amount > 0) {
                $price = (float) $transaction->total_price / $amount;
            }
            // For buy_from_reseller, try to get price from the transaction
            elseif ($transaction->type === 'buy_from_reseller' && $amount > 0) {
                // Try to find the corresponding reseller_sell transaction to get the price
                $resellerTransaction = Transaction::where('recipient_id', $userId)
                    ->where('sender_id', $transaction->sender_id)
                    ->where('type', 'reseller_sell')
                    ->where('created_at', '>=', $transaction->created_at->subMinutes(5))
                    ->where('created_at', '<=', $transaction->created_at->addMinutes(5))
                    ->whereNotNull('price_per_coin')
                    ->where('price_per_coin', '>', 0)
                    ->first();
                
                if ($resellerTransaction) {
                    $price = (float) $resellerTransaction->price_per_coin;
                }
            }
            
            // Only add if we have a valid price
            if ($amount > 0 && $price !== null && $price > 0) {
                $totalAmount += $amount;
                $totalValue += $amount * $price;
            }
        }
        
        // Calculate average price (weighted average)
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


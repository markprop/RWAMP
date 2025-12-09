<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\BuyFromResellerRequest;
use App\Models\CryptoPayment;
use App\Models\GameSession;
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
        $reseller->refresh();
        
        // Clean up stuck game states before rendering
        $isInGame = false;
        $hasPin = false;
        
        \Log::info('[ResellerDashboard] Checking game state', [
            'user_id' => $reseller->id,
            'is_in_game_flag' => $reseller->is_in_game
        ]);
        
        try {
            // Check for active game session using direct query
            $activeSession = GameSession::where('user_id', $reseller->id)
                ->where('status', 'active')
                ->first();
            
            \Log::info('[ResellerDashboard] Active session check', [
                'user_id' => $reseller->id,
                'session_found' => $activeSession ? true : false,
                'session_id' => $activeSession ? $activeSession->id : null
            ]);
            
            if ($activeSession) {
                // User has an active session
                $isInGame = true;
                \Log::info('[ResellerDashboard] Active session found, setting isInGame to true');
                // Ensure the flag is set
                if (!$reseller->is_in_game) {
                    $reseller->is_in_game = true;
                    $reseller->save();
                    \Log::info('[ResellerDashboard] Updated is_in_game flag to true');
                }
            } else {
                // No active session - reset the flag
                if ($reseller->is_in_game) {
                    \Log::warning('[ResellerDashboard] No active session but is_in_game flag is true, resetting');
                    $reseller->is_in_game = false;
                    $reseller->save();
                }
                $isInGame = false;
                \Log::info('[ResellerDashboard] No active session, isInGame set to false');
            }
            
            // Check if user has PIN
            $hasPin = !empty($reseller->game_pin_hash);
            \Log::info('[ResellerDashboard] Game state determined', [
                'isInGame' => $isInGame,
                'hasPin' => $hasPin
            ]);
        } catch (\Exception $e) {
            \Log::error('[ResellerDashboard] Error checking game state', [
                'user_id' => $reseller->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // If there's any error, ensure flags are false
            $isInGame = false;
            $hasPin = !empty($reseller->game_pin_hash ?? '');
            // Also reset the flag if it's set
            if ($reseller->is_in_game) {
                $reseller->is_in_game = false;
                $reseller->save();
            }
        }
        
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
        
        // Get all transactions where reseller received coins (all purchase types)
        $creditTransactions = Transaction::where('user_id', $reseller->id)
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
                $resellerTransaction = Transaction::where('recipient_id', $reseller->id)
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

        return view('dashboard.reseller', compact('metrics', 'myUsers', 'pendingBuyRequests', 'allResellers', 'recentTransactions', 'isInGame', 'hasPin'));
    }
}


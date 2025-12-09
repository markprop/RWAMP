<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\GameTrade;
use App\Models\GamePriceHistory;
use App\Services\GamePriceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GameController extends Controller
{
    protected $priceEngine;

    public function __construct(GamePriceEngine $priceEngine)
    {
        $this->priceEngine = $priceEngine;
        $this->middleware('auth');
    }

    /**
     * Show game selection page
     */
    public function select()
    {
        $user = Auth::user();
        
        if (!$user->canEnterGame()) {
            return redirect()->route('dashboard.' . ($user->role === 'reseller' ? 'reseller' : 'investor'))
                ->with('error', 'You cannot access games. Please ensure KYC is approved, you have a token balance, and your game PIN is not locked.');
        }

        return view('game.select');
    }

    /**
     * Show game interface
     */
    public function index()
    {
        $user = Auth::user();
        
        Log::info('[GameController] index() called', [
            'user_id' => $user->id,
            'role' => $user->role
        ]);
        
        if (!$user->canEnterGame()) {
            Log::warning('[GameController] User cannot enter game', [
                'user_id' => $user->id,
                'kyc_status' => $user->kyc_status,
                'token_balance' => $user->token_balance,
                'is_in_game' => $user->is_in_game,
                'pin_locked' => $user->isGamePinLocked()
            ]);
            return redirect()->route('dashboard.' . ($user->role === 'reseller' ? 'reseller' : 'investor'))
                ->with('error', 'You cannot access the game. Please ensure KYC is approved, you have a token balance, and your game PIN is not locked.');
        }

        // Check if user has active session
        // Refresh user to ensure we have latest data
        $user->refresh();
        
        // Use direct query instead of relationship to ensure we get the session
        $session = GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        Log::info('[GameController] Session check', [
            'user_id' => $user->id,
            'is_in_game_flag' => $user->is_in_game,
            'session_found' => $session ? true : false,
            'session_id' => $session ? $session->id : null,
            'relationship_check' => $user->activeGameSession ? $user->activeGameSession->id : null
        ]);
        
        if (!$session) {
            // Check all sessions for this user to debug
            $allSessions = GameSession::where('user_id', $user->id)->get();
            $activeSessions = GameSession::where('user_id', $user->id)->where('status', 'active')->get();
            
            Log::warning('[GameController] No active session found - redirecting to dashboard', [
                'user_id' => $user->id,
                'is_in_game_flag' => $user->is_in_game,
                'all_sessions_count' => $allSessions->count(),
                'active_sessions_count' => $activeSessions->count(),
                'all_sessions' => $allSessions->map(function($s) {
                    return ['id' => $s->id, 'status' => $s->status, 'created_at' => $s->created_at];
                })->toArray()
            ]);
            
            // If user is marked as in game but no session found, reset the flag
            if ($user->is_in_game) {
                $user->is_in_game = false;
                $user->save();
                Log::info('[GameController] Reset is_in_game flag');
            }
            
            $dashboardRoute = 'dashboard.' . ($user->role === 'reseller' ? 'reseller' : 'investor');
            Log::info('[GameController] Redirecting to dashboard', ['route' => $dashboardRoute]);
            
            // Use withInput to preserve any data and add a flash message
            return redirect()->route($dashboardRoute)
                ->with('error', 'No active game session found. Please enter the game from your dashboard first.')
                ->with('game_error', 'No active session');
        }
        
        Log::info('[GameController] Game page loaded successfully', [
            'user_id' => $user->id,
            'session_id' => $session->id
        ]);

        // Get current prices
        $prices = $this->priceEngine->getCurrentPrices(
            $session->anchor_btc_usd,
            $session->anchor_mid_price
        );

        return view('game.index', [
            'session' => $session,
            'initialPrices' => $prices,
            'initialBalance' => $session->calculateCurrentBalance(),
            'gameBalanceStart' => (float) $session->game_balance_start,
            'realBalanceStart' => (float) $session->real_balance_start,
        ]);
    }

    /**
     * Set or update game PIN
     */
    public function setPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'PIN must be exactly 4 digits',
            ], 422);
        }

        $user = Auth::user();
        
        if ($user->setGamePin($request->pin)) {
            Log::info('Game: User set game PIN', ['user_id' => $user->id]);
            return response()->json([
                'success' => true,
                'message' => 'Game PIN set successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to set game PIN',
        ], 500);
    }

    /**
     * Enter game - create session and lock balance
     */
    public function enter(Request $request)
    {
        $user = Auth::user();
        
        Log::info('[GameController] enter() called', [
            'user_id' => $user->id,
            'has_pin' => !empty($user->game_pin_hash)
        ]);
        
        $validator = Validator::make($request->all(), [
            'pin'    => ['required', 'string', 'regex:/^\d{4}$/'],
            // Amount of real RWAMP the user wants to stake in the game
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($validator->fails()) {
            Log::warning('[GameController] PIN validation failed', [
                'user_id' => $user->id,
                'errors' => $validator->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'PIN must be exactly 4 digits',
            ], 422);
        }

        // Check prerequisites
        if (!$user->canEnterGame()) {
            Log::warning('[GameController] User cannot enter game', [
                'user_id' => $user->id,
                'kyc_status' => $user->kyc_status,
                'token_balance' => $user->token_balance
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You cannot enter the game. Please ensure KYC is approved and you have a token balance.',
            ], 403);
        }

        // Verify PIN
        if (!$user->verifyGamePin($request->pin)) {
            if ($user->isGamePinLocked()) {
                $lockedUntil = $user->game_pin_locked_until->diffForHumans();
                Log::warning('[GameController] PIN locked', [
                    'user_id' => $user->id,
                    'locked_until' => $lockedUntil
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "PIN locked. Try again in {$lockedUntil}.",
                    'locked' => true,
                ], 403);
            }

            $attemptsLeft = 3 - $user->game_pin_failed_attempts;
            Log::warning('[GameController] Invalid PIN', [
                'user_id' => $user->id,
                'attempts_left' => $attemptsLeft
            ]);
            return response()->json([
                'success' => false,
                'message' => "Invalid PIN. {$attemptsLeft} attempts remaining.",
                'attempts_left' => $attemptsLeft,
            ], 403);
        }

        Log::info('[GameController] PIN verified successfully', ['user_id' => $user->id]);

        // Check if user already has active session
        $activeSession = $user->activeGameSession;
        if ($activeSession) {
            Log::info('[GameController] Active session found, returning existing session', [
                'user_id' => $user->id,
                'session_id' => $activeSession->id
            ]);
            $prices = $this->priceEngine->getCurrentPrices(
                $activeSession->anchor_btc_usd,
                $activeSession->anchor_mid_price
            );
            
            return response()->json([
                'success' => true,
                'session_id' => $activeSession->id,
                'game_balance' => $activeSession->calculateCurrentBalance(),
                'buy_price' => $prices['buy_price'],
                'sell_price' => $prices['sell_price'],
                'mid_price' => $prices['mid_price'],
                'btc_usd' => $prices['btc_usd'],
                'usd_pkr' => $prices['usd_pkr'],
                'redirect_url' => route('game.index'),
            ]);
        }

        // Create new session with a specific stake amount
        Log::info('[GameController] Creating new game session', [
            'user_id' => $user->id,
            'token_balance' => $user->token_balance
        ]);
        
        $stakeAmount = (float) $request->input('amount');

        // Make sure user has enough real balance to stake
        if ($stakeAmount > $user->token_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient token balance for the requested stake amount.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Lock a specific stake from the real account
            $realBalanceStart = $stakeAmount;
            $gameBalanceStart = $realBalanceStart * 10;

            // Get current prices for anchor
            $prices = $this->priceEngine->getCurrentPrices();
            
            Log::info('[GameController] Game session parameters', [
                'user_id' => $user->id,
                'real_balance_start' => $realBalanceStart,
                'game_balance_start' => $gameBalanceStart,
                'anchor_btc_usd' => $prices['btc_usd'],
                'anchor_mid_price' => $prices['mid_price']
            ]);
            
            $session = GameSession::create([
                'user_id' => $user->id,
                'real_balance_start' => $realBalanceStart,
                'game_balance_start' => $gameBalanceStart,
                'anchor_btc_usd' => $prices['btc_usd'],
                'anchor_mid_price' => $prices['mid_price'],
                'status' => 'active',
                'started_at' => now(),
            ]);
            
            Log::info('[GameController] Game session created', [
                'user_id' => $user->id,
                'session_id' => $session->id
            ]);

            // Deduct the staked amount from real balance and mark user as in game
            // NOTE: We only touch the real wallet here; the game balance lives on the session.
            $user->deductTokens($realBalanceStart, 'Game stake locked - SESSION-' . $session->id);
            $user->is_in_game = true;
            $user->save();

            DB::commit();

            // Refresh user model to ensure session relationship is loaded
            $user->refresh();
            $session->refresh();

            Log::info('Game: User entered game', [
                'user_id' => $user->id,
                'session_id' => $session->id,
                'real_balance' => $realBalanceStart,
                'game_balance' => $gameBalanceStart,
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'game_balance' => $gameBalanceStart,
                'buy_price' => $prices['buy_price'],
                'sell_price' => $prices['sell_price'],
                'mid_price' => $prices['mid_price'],
                'btc_usd' => $prices['btc_usd'],
                'usd_pkr' => $prices['usd_pkr'],
                'redirect_url' => route('game.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Game: Failed to create session', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enter game. Please try again.',
            ], 500);
        }
    }

    /**
     * Get current game prices
     */
    public function price(Request $request)
    {
        $user = Auth::user();
        $session = $user->activeGameSession;

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active game session',
            ], 404);
        }

        // Get current prices using anchor
        $prices = $this->priceEngine->getCurrentPrices(
            $session->anchor_btc_usd,
            $session->anchor_mid_price
        );

        // Record price history (every 5 seconds max)
        $lastPrice = $session->priceHistory()
            ->latest('recorded_at')
            ->first();

        if (!$lastPrice || $lastPrice->recorded_at->diffInSeconds(now()) >= 5) {
            GamePriceHistory::create([
                'session_id' => $session->id,
                'mid_price' => $prices['mid_price'],
                'buy_price' => $prices['buy_price'],
                'sell_price' => $prices['sell_price'],
                'btc_usd' => $prices['btc_usd'],
                'usd_pkr' => $prices['usd_pkr'],
                'recorded_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'buy_price' => $prices['buy_price'],
            'sell_price' => $prices['sell_price'],
            'mid_price' => $prices['mid_price'],
            'btc_usd' => $prices['btc_usd'],
            'usd_pkr' => $prices['usd_pkr'],
        ]);
    }

    /**
     * Execute a trade (BUY or SELL)
     */
    public function trade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'side' => ['required', 'in:BUY,SELL'],
            'quantity' => ['required', 'numeric', 'min:0.00000001'],
            'idempotency_key' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = Auth::user();
        $session = $user->activeGameSession;

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active game session',
            ], 404);
        }

        // Check idempotency
        $idempotencyKey = $request->idempotency_key ?? Str::uuid()->toString();
        $existingTrade = GameTrade::where('idempotency_key', $idempotencyKey)->first();
        if ($existingTrade) {
            return response()->json([
                'success' => true,
                'message' => 'Trade already processed',
                'trade_id' => $existingTrade->id,
            ]);
        }

        // Get current prices
        $prices = $this->priceEngine->getCurrentPrices(
            $session->anchor_btc_usd,
            $session->anchor_mid_price
        );

        $params = $this->priceEngine->getGameParameters();
        $quantity = (float) $request->quantity;
        $side = $request->side;

        // Calculate current RWAMP token balance and PKR balance
        $currentRwampBalance = $session->calculateCurrentBalance();
        $currentPkrBalance = $session->calculateCurrentPkrBalance();

        DB::beginTransaction();
        try {
            if ($side === 'BUY') {
                $price = $prices['buy_price'];
                $totalCost = ($quantity * $price);
                $fee = $totalCost * $params['buy_fee_pct'];
                $totalRequired = $totalCost + $fee;

                // Check if user has enough PKR to buy
                if ($currentPkrBalance < $totalRequired) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient PKR balance to complete purchase',
                    ], 400);
                }

                // BUY: User spends PKR to get RWAMP tokens, so RWAMP balance increases
                $newBalance = $currentRwampBalance + $quantity;
            } else { // SELL
                $price = $prices['sell_price'];
                $totalValue = ($quantity * $price);
                $fee = $totalValue * $params['sell_fee_pct'];
                $totalReceived = $totalValue - $fee;

                // Check if user has enough RWAMP tokens to sell
                if ($currentRwampBalance < $quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient RWAMP tokens to sell',
                    ], 400);
                }

                // SELL: User sells RWAMP tokens to get PKR, so RWAMP balance decreases
                $newBalance = $currentRwampBalance - $quantity;
            }

            // Calculate spread revenue (platform profit)
            $spreadRevenue = ($quantity * $params['spread_pkr']) / 2;

            // Create trade record
            // Note: game_balance_after now represents RWAMP token balance, not PKR
            $trade = GameTrade::create([
                'session_id' => $session->id,
                'side' => $side,
                'quantity' => $quantity,
                'price_pkr' => $price,
                'fee_pkr' => $fee,
                'spread_revenue_pkr' => $spreadRevenue,
                'game_balance_after' => $newBalance, // RWAMP token balance after this trade
                'idempotency_key' => $idempotencyKey,
            ]);

            // Update session revenue
            $session->increment('total_platform_revenue', $spreadRevenue + $fee);

            DB::commit();

            Log::info('Game: Trade executed', [
                'user_id' => $user->id,
                'session_id' => $session->id,
                'trade_id' => $trade->id,
                'side' => $side,
                'quantity' => $quantity,
                'price' => $price,
                'new_rwamp_balance' => $newBalance,
                'new_pkr_balance' => $session->calculateCurrentPkrBalance(),
            ]);

            return response()->json([
                'success' => true,
                'trade_id' => $trade->id,
                'game_balance' => $newBalance, // RWAMP token balance
                'price' => $price,
                'fee' => $fee,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Game: Trade failed', [
                'user_id' => $user->id,
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Trade failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get game history (trades and price history)
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $session = GameSession::where('user_id', $user->id)
                ->where('id', $sessionId)
                ->firstOrFail();
        } else {
            $session = $user->activeGameSession;
            if (!$session) {
                // If no session but user is marked as in game, reset the flag
                if ($user->is_in_game) {
                    $user->is_in_game = false;
                    $user->save();
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'No active game session',
                    'session' => null,
                ], 200); // Return 200 instead of 404 to prevent fetch errors
            }
        }

        $trades = $session->trades()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $priceHistory = $session->priceHistory()
            ->orderBy('recorded_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'trades' => $trades,
            'price_history' => $priceHistory,
            'session' => [
                'id' => $session->id,
                'game_balance_start' => $session->game_balance_start,
                'current_balance' => $session->calculateCurrentBalance(),
                'total_revenue' => $session->total_platform_revenue,
                'started_at' => $session->started_at,
            ],
        ]);
    }

    /**
     * Force reset game state (for stuck states)
     */
    public function forceReset()
    {
        $user = Auth::user();
        
        // Reset is_in_game flag
        $user->is_in_game = false;
        $user->save();
        
        // Mark any active sessions as abandoned
        GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->update([
                'status' => 'abandoned',
                'ended_at' => now(),
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Game state reset successfully',
        ]);
    }

    /**
     * Exit game - apply results to real balance
     */
    public function exit(Request $request)
    {
        $user = Auth::user();
        $session = $user->activeGameSession;

        if (!$session) {
            // If no session but user is marked as in game, reset the flag
            if ($user->is_in_game) {
                $user->is_in_game = false;
                $user->save();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No active game session',
            ], 404);
        }

        // Check idempotency
        $idempotencyKey = $request->header('Idempotency-Key') ?? Str::uuid()->toString();
        $cacheKey = "game_exit_{$session->id}_{$idempotencyKey}";
        
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return response()->json($cached);
        }

        DB::beginTransaction();
        try {
            // Calculate final balances
            $finalGameBalance = $session->calculateCurrentBalance();

            // Convert game balance back to real RWAMP.
            // NOTE: We use a 10x multiplier for entering the game (stake * 10),
            // but on exit, we divide by 100 to get the real payout amount.
            $finalRealBalance = $finalGameBalance / 100;

            // Calculate P&L relative to the original stake for this session
            $netPnl = $finalRealBalance - $session->real_balance_start;

            // Update session-level metrics
            $session->game_balance_end = $finalGameBalance;
            // real_balance_end represents the amount returned from the game
            $session->real_balance_end = $finalRealBalance;
            $session->net_user_pnl_pkr = $netPnl;
            $session->status = 'completed';
            $session->ended_at = now();
            $session->save();

            // Add the payout back to the user's real wallet balance
            $user->addTokens($finalRealBalance, 'Game session settled - SESSION-' . $session->id);
            $user->is_in_game = false;
            $user->save();

            DB::commit();

            // Cache result for idempotency
            $result = [
                'success' => true,
                'final_game_balance' => $finalGameBalance,
                'final_real_balance' => $finalRealBalance,
                'real_balance_start' => $session->real_balance_start,
                'net_pnl' => $netPnl,
                'total_platform_revenue' => $session->total_platform_revenue,
            ];
            
            Cache::put($cacheKey, $result, now()->addMinutes(10));

            Log::info('Game: User exited game', [
                'user_id' => $user->id,
                'session_id' => $session->id,
                'final_real_balance' => $finalRealBalance,
                'net_pnl' => $netPnl,
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Game: Exit failed', [
                'user_id' => $user->id,
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to exit game. Please try again.',
            ], 500);
        }
    }
}

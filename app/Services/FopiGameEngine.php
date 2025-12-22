<?php

namespace App\Services;

use App\Models\FopiGameEvent;
use App\Models\GameSession;
use App\Models\User;
use App\Models\GameSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FopiGameEngine
{
    protected const EXCHANGE_RATE = 100;
    protected const FOPI_PER_RWAMP = 1000;
    protected const FEE_PERCENT = 0.02;
    protected const BASE_SELL_DELAY = 2;
    protected const RWAMP_MAX_SUPPLY = 100000000000;

    /**
     * Start a new FOPI game session
     */
    public function startSession(User $user, float $stakeRwamp): GameSession
    {
        DB::beginTransaction();
        try {
            // Check if user has active session (any type)
            $activeSession = $user->activeGameSession;
            if ($activeSession) {
                // Auto-exit existing session
                $this->exitSession($activeSession);
            }

            // Deduct stake from user balance
            if ($user->token_balance < $stakeRwamp) {
                throw new \Exception('Insufficient RWAMP balance');
            }

            $user->token_balance -= $stakeRwamp;
            $user->save();

            // Convert RWAMP to FOPI
            $initialFopi = $stakeRwamp * self::FOPI_PER_RWAMP;

            // Initialize FOPI game state
            $state = $this->initializeState($user, $stakeRwamp, $initialFopi);

            // Create game session (FOPI sessions don't rely on BTC anchor pricing,
            // so we store zeroes for anchor fields to satisfy NOT NULL constraints)
            $session = GameSession::create([
                'user_id' => $user->id,
                'type' => 'fopi',
                'real_balance_start' => $stakeRwamp,
                'game_balance_start' => $initialFopi,
                'anchor_btc_usd' => 0,
                'anchor_mid_price' => 0,
                'status' => 'active',
                'started_at' => now(),
                'state_json' => json_encode($state),
            ]);

            // Mark user as in game
            $user->is_in_game = true;
            $user->save();

            // Log entry event
            FopiGameEvent::create([
                'user_id' => $user->id,
                'session_id' => $session->id,
                'event_type' => 'enter',
                'details' => [
                    'stake_rwamp' => $stakeRwamp,
                    'initial_fopi' => $initialFopi,
                ],
            ]);

            DB::commit();
            return $session;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FOPI session start failed', [
                'user_id' => $user->id,
                'stake_rwamp' => $stakeRwamp,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Initialize FOPI game state
     */
    protected function initializeState(User $user, float $initialRwamp, float $initialFopi): array
    {
        // Use configured rate if available, otherwise fall back to constant
        $gameSettings = GameSetting::current();
        $fopiPerRwamp = $gameSettings->fopi_per_rwamp ?? self::FOPI_PER_RWAMP;

        return [
            'user' => [
                'rwamp' => $initialRwamp,
                'initialRwamp' => $initialRwamp,
                'rwampStartingBalance' => $initialRwamp,
                'fopi' => $initialFopi,
                'currentMonth' => 1,
                'gameDay' => 1,
                'totalRentCollected' => 0,
                'unclaimedRent' => 0,
                'playerTag' => 'Novice',
                'syndicateStaked' => 0,
                'syndicateCostBasis' => 0,
                'achievements' => [],
                'totalRwampMined' => 0,
                'generatedRevenue' => 0,
                'totalRwampEarned' => $initialRwamp,
                'totalRwampSpent' => 0,
                'rwampPnL' => 0,
                'stats' => [
                    'jumps' => 0,
                    'claims' => 0,
                    'conversions' => 0,
                    'rentCollections' => 0,
                    'totalRealizedPL' => 0,
                ],
                'missions' => [],
                'awards' => [],
            ],
            'properties' => $this->getInitialProperties(),
            'holdings' => [],
            'sellQueue' => [],
            'installmentPlans' => [],
            'club' => [
                'id' => 'ae1',
                'target' => 20000,
                'committed' => 6400,
                'completed' => false,
            ],
            'news' => [
                ['month' => 1, 'text' => 'Welcome to the Metaverse.', 'type' => 'pos'],
            ],
            'burn' => [
                'rwampInitialSupply' => self::RWAMP_MAX_SUPPLY,
                'maxSupply' => self::RWAMP_MAX_SUPPLY,
                'totalRwampBurned' => 0,
                'rwampSimulatedSupply' => self::RWAMP_MAX_SUPPLY,
                'rwampFeesThisMonth' => 0,
                'lastBurnAmount' => 0,
                'history' => [],
            ],
            'sys' => [
                'lastTick' => now()->timestamp * 1000, // milliseconds
            ],
        ];
    }

    /**
     * Get initial properties configuration
     */
    protected function getInitialProperties(): array
    {
        return [
            [
                'id' => 'pk1', 'name' => 'Blue World City', 'region' => 'PK', 'status' => 'UC',
                'pricePerSqft' => 12500, 'annualYieldPct' => 0, 'annualAppreciationPct' => 12,
                'handoverMonth' => 24, 'originalHandover' => 24,
                'history' => [12000, 12100, 12200, 12300, 12400, 12500],
                'delayRiskPct' => 0.2, 'maxDelayMonths' => 6, 'hasUcYield' => false,
                'ucAnnualYieldPct' => 0, 'minRwamp' => 1000,
            ],
            [
                'id' => 'pk2', 'name' => 'Bahria Town Karachi 2', 'region' => 'PK', 'status' => 'READY',
                'pricePerSqft' => 18500, 'annualYieldPct' => 6.2, 'annualAppreciationPct' => 8,
                'handoverMonth' => 0, 'history' => [18000, 18100, 18200, 18300, 18400, 18500],
                'minRwamp' => 2000,
            ],
            [
                'id' => 'pk3', 'name' => 'Capital Smart City', 'region' => 'PK', 'status' => 'UC',
                'pricePerSqft' => 15200, 'annualYieldPct' => 0, 'annualAppreciationPct' => 14,
                'handoverMonth' => 18, 'originalHandover' => 18,
                'history' => [14500, 14600, 14800, 14900, 15000, 15200],
                'delayRiskPct' => 0.1, 'maxDelayMonths' => 3, 'hasUcYield' => false,
                'ucAnnualYieldPct' => 0, 'minRwamp' => 1500,
            ],
            [
                'id' => 'pk4', 'name' => 'DHA Quetta', 'region' => 'PK', 'status' => 'UC',
                'pricePerSqft' => 10800, 'annualYieldPct' => 0, 'annualAppreciationPct' => 10,
                'handoverMonth' => 36, 'originalHandover' => 36,
                'history' => [10000, 10200, 10300, 10500, 10600, 10800],
                'delayRiskPct' => 0.3, 'maxDelayMonths' => 8, 'hasUcYield' => false,
                'ucAnnualYieldPct' => 0, 'minRwamp' => 800,
            ],
            [
                'id' => 'ae1', 'name' => 'Neon Views Dubai', 'region' => 'AE', 'status' => 'UC',
                'pricePerSqft' => 65000, 'annualYieldPct' => 0, 'annualAppreciationPct' => 12,
                'handoverMonth' => 8, 'originalHandover' => 8,
                'history' => [60000, 61000, 62000, 63000, 64000, 65000],
                'delayRiskPct' => 0.1, 'maxDelayMonths' => 2, 'hasUcYield' => true,
                'ucAnnualYieldPct' => 2.5, 'minRwamp' => 5000,
            ],
            [
                'id' => 'ae2', 'name' => 'Palm Jumeirah Pod', 'region' => 'AE', 'status' => 'READY',
                'pricePerSqft' => 120000, 'annualYieldPct' => 7.2, 'annualAppreciationPct' => 8,
                'handoverMonth' => 0, 'history' => [110000, 112000, 114000, 116000, 118000, 120000],
                'minRwamp' => 10000,
            ],
            [
                'id' => 'intl1', 'name' => 'London Neural Hub', 'region' => 'INTL', 'status' => 'READY',
                'pricePerSqft' => 180000, 'annualYieldPct' => 4.5, 'annualAppreciationPct' => 3,
                'handoverMonth' => 0, 'history' => [178000, 178500, 179000, 179500, 179800, 180000],
                'minRwamp' => 15000,
            ],
            [
                'id' => 'club1', 'name' => 'Orbital Penthouse', 'region' => 'CLUB', 'status' => 'READY',
                'pricePerSqft' => 450000, 'annualYieldPct' => 8.5, 'annualAppreciationPct' => 6,
                'handoverMonth' => 0, 'history' => [440000, 442000, 444000, 446000, 448000, 450000],
                'minRwamp' => 50000, 'baseFairValue' => 400000,
            ],
        ];
    }

    /**
     * Load state from session
     */
    public function loadState(GameSession $session): array
    {
        $state = $session->getFopiState();
        if (!$state) {
            throw new \Exception('Invalid game state');
        }

        // Auto-progress months if needed
        $this->autoProgressMonths($state, $session);

        return $state;
    }

    /**
     * Auto-progress months based on time elapsed
     */
    protected function autoProgressMonths(array &$state, GameSession $session): void
    {
        $lastTick = $state['sys']['lastTick'] ?? ($session->started_at->timestamp * 1000);
        $now = now()->timestamp * 1000;
        $elapsedMs = $now - $lastTick;

        // 60 seconds = 1 game day, 30 days = 1 month
        $secondsPerDay = 60;
        $daysPerMonth = 30;
        $msPerDay = $secondsPerDay * 1000;
        $msPerMonth = $msPerDay * $daysPerMonth;

        $daysElapsed = floor($elapsedMs / $msPerDay);
        $monthsElapsed = floor($daysElapsed / $daysPerMonth);

        if ($monthsElapsed > 0) {
            for ($i = 0; $i < $monthsElapsed; $i++) {
                $this->processMonth($state, false);
            }
            $state['sys']['lastTick'] = $now;
            $this->saveState($session, $state);
        } elseif ($daysElapsed > 0) {
            $state['user']['gameDay'] = ($state['user']['gameDay'] + $daysElapsed) % 30;
            if ($state['user']['gameDay'] === 0) {
                $state['user']['gameDay'] = 30;
            }
            $state['sys']['lastTick'] = $now;
            $this->saveState($session, $state);
        }
    }

    /**
     * Process month jump
     */
    public function jumpMonth(GameSession $session): array
    {
        $state = $this->loadState($session);
        $this->processMonth($state, true);
        $this->saveState($session, $state);

        FopiGameEvent::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'event_type' => 'jump_month',
            'details' => [
                'current_month' => $state['user']['currentMonth'] ?? null,
            ],
        ]);

        return $state;
    }

    /**
     * Process a month (core game logic)
     */
    protected function processMonth(array &$state, bool $isManualJump): void
    {
        $user = &$state['user'];
        $properties = &$state['properties'];
        $holdings = &$state['holdings'];
        $sellQueue = &$state['sellQueue'];
        $burn = &$state['burn'];

        // Increment month
        $user['currentMonth']++;
        $user['gameDay'] = 1;

        if ($isManualJump) {
            $user['stats']['jumps']++;
        }

        // Update property prices (appreciation)
        foreach ($properties as &$prop) {
            $appreciation = $prop['annualAppreciationPct'] / 12; // Monthly
            $newPrice = $prop['pricePerSqft'] * (1 + $appreciation / 100);
            $prop['pricePerSqft'] = $newPrice;

            // Update history
            $history = $prop['history'] ?? [];
            $history[] = $newPrice;
            if (count($history) > 12) {
                array_shift($history);
            }
            $prop['history'] = $history;

            // Handle UC delays
            if ($prop['status'] === 'UC' && $prop['handoverMonth'] > 0) {
                if (rand(1, 100) <= ($prop['delayRiskPct'] * 100)) {
                    $delay = rand(1, $prop['maxDelayMonths']);
                    $prop['handoverMonth'] += $delay;
                }
            }
        }

        // Process holdings (rent, appreciation, handover)
        foreach ($holdings as &$holding) {
            $holding['monthsHeld']++;

            $prop = collect($properties)->firstWhere('id', $holding['propertyId']);
            if (!$prop) continue;

            // Calculate rent
            if ($prop['status'] === 'READY' || ($prop['status'] === 'UC' && $prop['hasUcYield'])) {
                $yieldPct = $prop['status'] === 'READY' ? $prop['annualYieldPct'] : $prop['ucAnnualYieldPct'];
                $monthlyRent = ($holding['sqftOwned'] * $prop['pricePerSqft']) * ($yieldPct / 12 / 100);
                $user['unclaimedRent'] += $monthlyRent;
            }

            // Update unrealized P/L
            $currentValue = $holding['sqftOwned'] * $prop['pricePerSqft'];
            $holding['unrealizedPl'] = $currentValue - $holding['costBasis'];
        }

        // Process sell queue
        foreach ($sellQueue as &$sell) {
            $sell['monthsRemaining']--;
            if ($sell['monthsRemaining'] <= 0) {
                $prop = collect($properties)->firstWhere('id', $sell['propertyId']);
                if ($prop) {
                    $proceeds = $sell['sqft'] * $prop['pricePerSqft'];
                    $user['fopi'] += $proceeds;
                    $user['stats']['totalRealizedPL'] += ($proceeds - $sell['costBasis']);
                }
            }
        }
        $sellQueue = array_filter($sellQueue, fn($s) => $s['monthsRemaining'] > 0);

        // Process installment plans
        foreach ($state['installmentPlans'] as &$plan) {
            if ($plan['completed']) continue;

            $prop = collect($properties)->firstWhere('id', $plan['propertyId']);
            if (!$prop) continue;

            $this->buyPropertyInternal($state, $plan['propertyId'], $plan['monthlySqft'], 'FOPI', false);
            $plan['monthsRemaining']--;

            if ($plan['monthsRemaining'] <= 0 || $plan['sqftCompleted'] >= $plan['targetSqft']) {
                $plan['completed'] = true;
            }
        }

        // Process burn
        $burn['lastBurnAmount'] = $burn['rwampFeesThisMonth'];
        $burn['totalRwampBurned'] += $burn['lastBurnAmount'];
        $burn['rwampSimulatedSupply'] = max(0, $burn['rwampSimulatedSupply'] - $burn['lastBurnAmount']);
        $burn['history'][] = [
            'month' => $user['currentMonth'],
            'amount' => $burn['lastBurnAmount'],
        ];
        $burn['rwampFeesThisMonth'] = 0;
    }

    /**
     * Buy property
     */
    public function buyProperty(GameSession $session, string $propertyId, float $sqft, string $feeMethod = 'RWAMP'): array
    {
        $state = $this->loadState($session);
        $this->buyPropertyInternal($state, $propertyId, $sqft, $feeMethod, true);
        $this->saveState($session, $state);

        FopiGameEvent::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'event_type' => 'buy',
            'details' => [
                'property_id' => $propertyId,
                'sqft' => $sqft,
                'fee_method' => $feeMethod,
            ],
        ]);

        return $state;
    }

    /**
     * Internal buy property logic
     */
    protected function buyPropertyInternal(array &$state, string $propertyId, float $sqft, string $feeMethod, bool $applyFee): void
    {
        $prop = collect($state['properties'])->firstWhere('id', $propertyId);
        if (!$prop) {
            throw new \Exception('Property not found');
        }

        $cost = $sqft * $prop['pricePerSqft'];
        $fee = $applyFee ? ($cost * ($feeMethod === 'RWAMP' ? 0.01 : 0.02)) : 0;

        if ($feeMethod === 'RWAMP') {
            if ($state['user']['rwamp'] < $fee) {
                throw new \Exception('Insufficient RWAMP for fee');
            }
            $state['user']['rwamp'] -= $fee;
            $state['user']['totalRwampSpent'] += $fee;
            $state['burn']['rwampFeesThisMonth'] += $fee;
        } else {
            if ($state['user']['fopi'] < ($cost + $fee)) {
                throw new \Exception('Insufficient FOPI');
            }
            $state['user']['fopi'] -= $fee;
        }

        if ($state['user']['fopi'] < $cost) {
            throw new \Exception('Insufficient FOPI');
        }

        $state['user']['fopi'] -= $cost;

        // Add/update holding
        $existingHolding = collect($state['holdings'])->firstWhere('propertyId', $propertyId);
        if ($existingHolding) {
            $totalCost = $existingHolding['costBasis'] + $cost;
            $totalSqft = $existingHolding['sqftOwned'] + $sqft;
            $existingHolding['costBasis'] = $totalCost;
            $existingHolding['sqftOwned'] = $totalSqft;
            $existingHolding['avgPricePerSqft'] = $totalCost / $totalSqft;
        } else {
            $state['holdings'][] = [
                'propertyId' => $propertyId,
                'sqftOwned' => $sqft,
                'costBasis' => $cost,
                'avgPricePerSqft' => $prop['pricePerSqft'],
                'monthsHeld' => 0,
                'unrealizedPl' => 0,
            ];
        }

        // Check achievements
        if (count($state['holdings']) === 1 && !in_array('first_buy', $state['user']['achievements'])) {
            $state['user']['achievements'][] = 'first_buy';
        }
    }

    /**
     * Claim rent
     */
    public function claimRent(GameSession $session): array
    {
        $state = $this->loadState($session);
        $rent = $state['user']['unclaimedRent'];
        $state['user']['fopi'] += $rent;
        $state['user']['totalRentCollected'] += $rent;
        $state['user']['unclaimedRent'] = 0;
        $state['user']['stats']['claims']++;
        $state['user']['stats']['rentCollections']++;

        if ($state['user']['stats']['rentCollections'] === 1 && !in_array('first_rent', $state['user']['achievements'])) {
            $state['user']['achievements'][] = 'first_rent';
        }

        $this->saveState($session, $state);

        FopiGameEvent::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'event_type' => 'claim_rent',
            'details' => [
                'claimed_rent' => $rent,
            ],
        ]);

        return $state;
    }

    /**
     * Convert FOPI to RWAMP
     */
    public function convertFopiToRwamp(GameSession $session, float $fopiAmount): array
    {
        $state = $this->loadState($session);
        $gameSettings = GameSetting::current();
        $fopiPerRwamp = $gameSettings->fopi_per_rwamp ?? self::FOPI_PER_RWAMP;

        if ($state['user']['fopi'] < $fopiAmount) {
            throw new \Exception('Insufficient FOPI');
        }

        // Only allow conversion from profit
        $profit = $this->calculateProfit($state);
        if ($fopiAmount > $profit) {
            throw new \Exception('Can only convert profit to RWAMP');
        }

        $rwampAmount = floor($fopiAmount / $fopiPerRwamp);
        $state['user']['fopi'] -= $fopiAmount;
        $state['user']['rwamp'] += $rwampAmount;
        $state['user']['totalRwampMined'] += ($rwampAmount * 1000); // stored in FOPI units
        $state['user']['totalRwampEarned'] += $rwampAmount;
        $state['user']['stats']['conversions']++;

        $this->saveState($session, $state);

        FopiGameEvent::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'event_type' => 'convert',
            'details' => [
                'fopi_amount' => $fopiAmount,
                'rwamp_amount' => $rwampAmount,
            ],
        ]);

        return $state;
    }

    /**
     * Calculate profit (FOPI balance above initial)
     */
    protected function calculateProfit(array $state): float
    {
        $initialFopi = $state['user']['initialRwamp'] * self::FOPI_PER_RWAMP;
        return max(0, $state['user']['fopi'] - $initialFopi);
    }

    /**
     * Save state to session
     */
    protected function saveState(GameSession $session, array $state): void
    {
        $session->setFopiState($state);
        $session->save();
    }

    /**
     * Exit game session
     */
    public function exitSession(GameSession $session): array
    {
        $state = $this->loadState($session);
        $gameSettings = GameSetting::current();
        $fopiPerRwamp = $gameSettings->fopi_per_rwamp ?? self::FOPI_PER_RWAMP;

        // Convert remaining FOPI back to RWAMP
        $remainingFopi = $state['user']['fopi'];
        $rwampReturned = $remainingFopi / $fopiPerRwamp;

        // Apply exit fee if configured
        if ($gameSettings->exit_fee_rate > 0) {
            $exitFee = $rwampReturned * ($gameSettings->exit_fee_rate / 100);
            $rwampReturned -= $exitFee;
        }

        // Return RWAMP to user
        $user = $session->user;
        $user->token_balance += $rwampReturned;
        $user->is_in_game = false;
        $user->save();

        // Update session
        $session->status = 'completed';
        $session->ended_at = now();
        $session->game_balance_end = $remainingFopi;
        $session->real_balance_end = $rwampReturned;
        $session->save();

        FopiGameEvent::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'event_type' => 'exit',
            'details' => [
                'remaining_fopi' => $remainingFopi,
                'rwamp_returned' => $rwampReturned,
                'exit_fee_rate' => $gameSettings->exit_fee_rate ?? 0,
            ],
        ]);

        return $state;
    }

    /**
     * Get current state (for API)
     */
    public function getState(GameSession $session): array
    {
        return $this->loadState($session);
    }
}

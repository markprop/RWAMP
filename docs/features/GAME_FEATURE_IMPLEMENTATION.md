# Play Game Feature - Implementation Summary

## ✅ Completed Implementation

### 1. Database Schema

**Migrations Created:**
- `2025_11_28_104654_create_user_game_sessions_table.php` - Core session metadata
- `2025_11_28_104658_create_game_trades_table.php` - All buy/sell events
- `2025_11_28_104659_create_game_price_history_table.php` - Price history for charts
- `2025_11_28_104702_add_game_fields_to_users_table.php` - PIN and game state fields

**Key Fields:**
- `user_game_sessions`: real_balance_start, game_balance_start, anchor_btc_usd, anchor_mid_price, chart_state
- `game_trades`: side, quantity, price_pkr, fee_pkr, spread_revenue_pkr, idempotency_key
- `game_price_history`: mid_price, buy_price, sell_price, btc_usd, usd_pkr, recorded_at
- `users`: game_pin_hash, is_in_game, game_pin_locked_until, game_pin_failed_attempts

### 2. Models

**GameSession Model:**
- Relationships: user, trades, priceHistory
- Methods: `calculateCurrentBalance()`, `isActive()`
- Casts: All decimal fields, chart_state as array

**GameTrade Model:**
- Relationship: session
- Idempotency key support for replay prevention

**GamePriceHistory Model:**
- Relationship: session
- Indexed on session_id and recorded_at for performance

**User Model Extensions:**
- `setGamePin()` - Set 4-digit PIN (bcrypt hashed)
- `verifyGamePin()` - Verify PIN with 3-attempt lockout
- `isGamePinLocked()` - Check lock status
- `canEnterGame()` - Check all prerequisites
- Relationships: `gameSessions()`, `activeGameSession()`

### 3. Price Engine Service

**GamePriceEngine Service:**
- `getBtcUsdPrice()` - Fetches from Binance API with cache fallback
- `getUsdPkrRate()` - Fetches from exchangerate-api.com with cache fallback
- `getGameParameters()` - Retrieves from system_settings table
- `calculateAnchoredPrice()` - Implements velocity multiplier formula
- `calculatePrices()` - Applies spread and fees
- `getCurrentPrices()` - Main method returning all prices

**Price Calculation Formula:**
```php
$btcPctChange = ($currentBtcUsd / $anchorBtcUsd) - 1;
$rwampPctChange = $btcPctChange * $velocityMultiplier;
$anchoredMid = $anchorMidPrice * (1 + $rwampPctChange);
```

### 4. GameController

**Methods Implemented:**
1. `index()` - Show game interface (requires active session)
2. `setPin()` - Set/update game PIN
3. `enter()` - Create session, lock balance, ×10 multiplier
4. `price()` - Get current prices, record history
5. `trade()` - Execute BUY/SELL with idempotency
6. `history()` - Get trades and price history for replay
7. `exit()` - Apply results, unlock balance, finalize session

**Security Features:**
- PIN verification with 3-attempt lockout (5 minutes)
- Balance locking (`is_in_game` flag)
- Idempotency keys for trade/exit requests
- Transaction-based operations (DB::beginTransaction)

### 5. Routes

**Game Routes (KYC-approved only):**
```php
Route::middleware(['auth', 'kyc.approved'])->prefix('game')->name('game.')->group(function () {
    Route::get('/', [GameController::class, 'index'])->name('index');
    Route::post('/set-pin', [GameController::class, 'setPin'])->name('set-pin');
    Route::post('/enter', [GameController::class, 'enter'])->name('enter');
    Route::get('/price', [GameController::class, 'price'])->name('price');
    Route::post('/trade', [GameController::class, 'trade'])->name('trade');
    Route::get('/history', [GameController::class, 'history'])->name('history');
    Route::post('/exit', [GameController::class, 'exit'])->name('exit');
});
```

### 6. Middleware

**EnsureNotInGame Middleware:**
- Prevents users in game from performing non-game RWAMP mutations
- Returns 403 if `is_in_game` is true

### 7. Frontend Components

**Alpine.js Components:**
1. `gameDashboard` - PIN setup, PIN entry, game entry flow
2. `gameSession` - Trading interface, price polling, trade execution

**Game Modals Component:**
- Warning modal (non-dismissible) with financial responsibility notice
- PIN setup modal (first-time users)
- PIN entry modal (returning users)

**Dashboard Cards:**
- Added "🎮 Play Game" card to both investor and reseller dashboards
- Only visible if KYC approved and token_balance > 0
- Shows "Already in Game" if user is in active session

**Game Interface (`resources/views/game/index.blade.php`):**
- Real-time price display (buy, sell, mid)
- Buy/Sell trading forms with fee calculations
- Game balance display
- Exit game modal with final balance summary
- Price polling every 5 seconds

### 8. Scheduled Commands

**PruneGamePriceHistory Command:**
- Deletes price history older than 7 days (configurable)
- Scheduled daily at 2 AM
- Command: `php artisan game:prune-price-history --days=7`

## 🔧 Configuration Required

### System Settings (Database)

Add these records to `system_settings` table:

```sql
INSERT INTO system_settings (key, value, type, description) VALUES
('tokens_per_btc', '1000000', 'float', 'Number of RWAMP tokens per BTC'),
('spread_pkr', '0.5', 'float', 'Spread in PKR for buy/sell prices'),
('buy_fee_pct', '0.01', 'float', 'Buy fee percentage (1%)'),
('sell_fee_pct', '0.01', 'float', 'Sell fee percentage (1%)'),
('velocity_multiplier', '1.0', 'float', 'Velocity multiplier for BTC price changes');
```

### Environment Variables

No new environment variables required. Uses existing:
- `APP_URL` - For API calls
- Cache system for price storage

## 📋 Deployment Steps

1. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

2. **Seed System Settings:**
   ```sql
   -- Run the SQL above or create a seeder
   ```

3. **Clear Caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

4. **Build Frontend Assets:**
   ```bash
   npm run build
   ```

5. **Test Game Entry:**
   - Login as KYC-approved user
   - Click "🎮 Play Game" card
   - Set PIN (first time)
   - Enter PIN and start trading

## 🔒 Security Features

1. **PIN Protection:**
   - 4-digit PIN (bcrypt hashed)
   - 3 failed attempts → 5-minute lockout
   - Required on every entry

2. **Balance Locking:**
   - `is_in_game` flag prevents non-game mutations
   - Enforced at middleware level

3. **Idempotency:**
   - Trade and exit requests use idempotency keys
   - Prevents duplicate processing

4. **Transaction Safety:**
   - All critical operations use DB transactions
   - Rollback on errors

## 📊 Game Mechanics

**Balance Conversion:**
- Entry: `game_balance = real_balance × 10`
- Exit: `real_balance = game_balance ÷ 10`

**Price Calculation:**
- Anchored to BTC price at session start
- Velocity multiplier amplifies BTC movements
- Spread and fees applied to buy/sell prices

**Platform Revenue:**
- Spread revenue: `quantity × spread_pkr / 2`
- Fee revenue: `total × fee_percentage`
- Tracked in `total_platform_revenue`

## 🐛 Known Limitations

1. **Token Holdings:** Currently simplified - doesn't track individual token purchases for selling. All selling is allowed as long as balance allows.

2. **Price History Retention:** Auto-pruned after 7 days (configurable).

3. **Chart Replay:** Price history stored but chart UI not fully implemented yet.

## 🚀 Next Steps (Optional Enhancements)

1. **Chart Implementation:**
   - Integrate Chart.js or similar
   - Display candlestick/line charts from price history
   - Support multiple timeframes (5m, 15m, 1h)

2. **Token Holdings Tracking:**
   - Track individual token purchases
   - FIFO/LIFO selling logic
   - Portfolio view with average cost

3. **Analytics Dashboard:**
   - Game session statistics
   - Platform revenue tracking
   - User P&L analytics

4. **Nova Resources:**
   - GameSession resource
   - GameTrade resource
   - Filtering and analytics

## ✅ Testing Checklist

- [ ] User can set game PIN
- [ ] PIN lockout works after 3 failed attempts
- [ ] User can enter game (balance × 10)
- [ ] Prices update in real-time (5s polling)
- [ ] User can execute BUY trades
- [ ] User can execute SELL trades
- [ ] Fees and spread are calculated correctly
- [ ] Game balance updates after trades
- [ ] User can exit game (balance ÷ 10)
- [ ] Real balance updates correctly on exit
- [ ] Price history is recorded
- [ ] Old price history is pruned (scheduled command)
- [ ] Balance is locked during game session
- [ ] Non-game mutations are blocked when in game

## 📝 Notes

- All game state is persisted for audit and replay
- Price engine uses same cache keys as AdminPriceController for consistency
- Chart state (timeframe, chartType) is stored in session for persistence
- Exit confirmation modal shows final balance impact before applying


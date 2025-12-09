# Game Feature - Deployment Guide

## 🚀 Quick Deployment Steps

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed System Settings
```bash
php artisan db:seed --class=GameSystemSettingsSeeder
```

Or manually insert:
```sql
INSERT INTO system_settings (key, value, type, description) VALUES
('tokens_per_btc', '1000000', 'float', 'Number of RWAMP tokens per BTC'),
('spread_pkr', '0.5', 'float', 'Spread in PKR for buy/sell prices'),
('buy_fee_pct', '0.01', 'float', 'Buy fee percentage (1%)'),
('sell_fee_pct', '0.01', 'float', 'Sell fee percentage (1%)'),
('velocity_multiplier', '1.0', 'float', 'Velocity multiplier for BTC price changes')
ON DUPLICATE KEY UPDATE value=VALUES(value);
```

### 3. Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### 4. Build Frontend Assets
```bash
npm run build
# or for development
npm run dev
```

### 5. Test Game Entry
1. Login as KYC-approved user with token_balance > 0
2. Navigate to dashboard (investor or reseller)
3. Click "🎮 Play Game" card
4. Set PIN (first time) or enter PIN
5. Start trading!

## 📋 Files Created/Modified

### New Files:
- `database/migrations/2025_11_28_104654_create_user_game_sessions_table.php`
- `database/migrations/2025_11_28_104658_create_game_trades_table.php`
- `database/migrations/2025_11_28_104659_create_game_price_history_table.php`
- `database/migrations/2025_11_28_104702_add_game_fields_to_users_table.php`
- `app/Models/GameSession.php`
- `app/Models/GameTrade.php`
- `app/Models/GamePriceHistory.php`
- `app/Services/GamePriceEngine.php`
- `app/Http/Controllers/GameController.php`
- `app/Http/Middleware/EnsureNotInGame.php`
- `app/Console/Commands/PruneGamePriceHistory.php`
- `database/seeders/GameSystemSettingsSeeder.php`
- `resources/views/game/index.blade.php`
- `resources/views/components/game-modals.blade.php`

### Modified Files:
- `routes/web.php` - Added game routes
- `app/Http/Kernel.php` - Registered middleware alias
- `app/Models/User.php` - Added game methods and relationships
- `app/Console/Kernel.php` - Added scheduled command
- `resources/js/app.js` - Added game components
- `resources/views/dashboard/investor.blade.php` - Added game card
- `resources/views/dashboard/reseller.blade.php` - Added game card

## 🔧 Configuration

### System Settings Required:
All settings are stored in `system_settings` table:
- `tokens_per_btc`: 1000000 (default)
- `spread_pkr`: 0.5 (default)
- `buy_fee_pct`: 0.01 (1% default)
- `sell_fee_pct`: 0.01 (1% default)
- `velocity_multiplier`: 1.0 (default)

### API Endpoints Used:
- Binance: `https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT`
- ExchangeRate API: `https://open.er-api.com/v6/latest/USD`
- Fallback: Uses existing `PriceHelper` methods

## 🔒 Security Features

1. **PIN Protection:**
   - 4-digit PIN (bcrypt hashed)
   - 3 failed attempts → 5-minute lockout
   - Required on every entry

2. **Balance Locking:**
   - `is_in_game` flag prevents non-game mutations
   - Middleware: `EnsureNotInGame`

3. **Idempotency:**
   - Trade and exit requests use idempotency keys
   - Prevents duplicate processing

## 📊 Game Mechanics

**Balance Conversion:**
- Entry: `game_balance = real_balance × 10`
- Exit: `real_balance = game_balance ÷ 10`

**Price Calculation:**
```php
$btcPctChange = ($currentBtcUsd / $anchorBtcUsd) - 1;
$rwampPctChange = $btcPctChange * $velocityMultiplier;
$anchoredMid = $anchorMidPrice * (1 + $rwampPctChange);
```

**Platform Revenue:**
- Spread: `quantity × spread_pkr / 2`
- Fees: `total × fee_percentage`
- Tracked in `total_platform_revenue`

## 🧹 Maintenance

**Prune Price History:**
```bash
php artisan game:prune-price-history --days=7
```

Scheduled to run daily at 2 AM automatically.

## ⚠️ Important Notes

1. **Token Holdings:** Currently simplified - selling doesn't require prior purchases. This is a known limitation.

2. **Price History:** Auto-pruned after 7 days (configurable via command).

3. **Chart UI:** Price history is stored but chart visualization UI is not fully implemented yet.

4. **Balance Lock:** Users cannot perform non-game RWAMP mutations while `is_in_game = true`.

## 🐛 Troubleshooting

**Issue: "No active game session"**
- User must enter game from dashboard first
- Check if session exists: `GameSession::where('user_id', $userId)->where('status', 'active')->first()`

**Issue: "PIN locked"**
- Wait 5 minutes or reset via database: `UPDATE users SET game_pin_locked_until = NULL, game_pin_failed_attempts = 0 WHERE id = ?`

**Issue: Prices not updating**
- Check API endpoints are accessible
- Check cache: `php artisan cache:clear`
- Check system_settings table has required keys

**Issue: Balance not updating after trade**
- Check `game_trades` table for trade records
- Verify `calculateCurrentBalance()` method in GameSession model
- Check browser console for JavaScript errors

## ✅ Testing Checklist

- [x] Migrations run successfully
- [x] System settings seeded
- [x] User can set game PIN
- [x] PIN lockout works (3 attempts)
- [x] User can enter game
- [x] Prices update in real-time
- [x] BUY trades execute
- [x] SELL trades execute
- [x] Fees calculated correctly
- [x] Game balance updates
- [x] User can exit game
- [x] Real balance updates on exit
- [x] Price history recorded
- [x] Balance locked during game

## 📝 Next Steps (Optional)

1. Implement chart visualization (Chart.js integration)
2. Add token holdings tracking (FIFO/LIFO)
3. Create Nova resources for admin management
4. Add analytics dashboard
5. Implement chart replay functionality


# Database Documentation

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=06D6A0&center=true&vCenter=true&width=600&lines=Database+Setup+%26+Migration+Guides" alt="Database Header" />
</p>

This directory contains database setup, migration, and synchronization documentation for the RWAMP platform.

## 📄 Documents

### Setup & Analysis
- **DATABASE_SETUP_GUIDE.md** - Complete database setup instructions
- **DATABASE_ANALYSIS.md** - Database schema and structure analysis

### Migration & Sync
- **USER_SYNC_MIGRATION_GUIDE.md** - User synchronization migration guide
- **LIVE_DATABASE_SYNC_INSTRUCTIONS.md** - Live database synchronization procedures
- **DIAGNOSE_USER_SYNC.md** - Diagnosing user sync issues
- **QUICK_FIX_USER_SYNC.md** - Quick fixes for user sync problems

## 🗄️ Database Structure

The RWAMP application uses **MySQL/MariaDB** with the following key tables:

### Core Tables
- **`users`** - User accounts with roles, KYC, 2FA, game fields, ULID
- **`crypto_payments`** - Crypto payment records with ULID, commission tracking
- **`transactions`** - Transaction history with payment tracking
- **`reseller_applications`** - Reseller program applications with ULID
- **`withdraw_requests`** - Withdrawal requests with ULID, receipt tracking

### Game System Tables
- **`user_game_sessions`** - Trading game session metadata
- **`game_trades`** - Game buy/sell trade records
- **`game_price_history`** - Price history for charts
- **`game_settings`** - Game system configuration

### Chat System Tables
- **`chats`** - Chat conversations (private and group)
- **`chat_participants`** - Chat participant relationships
- **`chat_messages`** - Chat messages with media support
- **`chat_message_reads`** - Message read receipts

### Supporting Tables
- **`contacts`** - Contact form submissions
- **`newsletter_subscriptions`** - Newsletter subscribers
- **`buy_from_reseller_requests`** - Buy-from-reseller requests
- **`processed_crypto_transactions`** - Processed blockchain transactions
- **`password_reset_tokens`** - Password reset tokens
- **`system_settings`** - System-wide settings

## 🔧 Common Tasks

### Initial Setup
1. Follow **DATABASE_SETUP_GUIDE.md** for complete setup
2. Run migrations: `php artisan migrate`
3. Seed initial data: `php artisan db:seed`
4. Verify database connection and tables

### User Synchronization
1. Review **USER_SYNC_MIGRATION_GUIDE.md** for migration procedures
2. Follow **LIVE_DATABASE_SYNC_INSTRUCTIONS.md** for live database sync
3. Use **DIAGNOSE_USER_SYNC.md** if synchronization issues occur
4. Apply **QUICK_FIX_USER_SYNC.md** for quick fixes

### Database Migrations
```bash
# Run all pending migrations
php artisan migrate

# Fresh migration (drops all tables and re-runs migrations)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset
```

### Database Seeders
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=AdminUserSeeder

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

## 📊 Database Statistics

- **Total Migrations**: 40+ migration files
- **Total Tables**: 20+ database tables
- **ULID Support**: 5 tables with ULID obfuscation
- **Relationships**: Complex foreign key relationships
- **Indexes**: Optimized indexes for performance

## 🔍 Key Database Features

### ULID Obfuscation
The following tables use ULID instead of numeric IDs:
- `crypto_payments` - ULID column for secure routing
- `users` - ULID column for user management
- `reseller_applications` - ULID column for applications
- `withdraw_requests` - ULID column for withdrawals

### Game System
- Session-based game state management
- Price history with automatic pruning
- Trade records with idempotency keys
- Game settings for configuration

### Chat System
- Multi-participant chat support
- Message read tracking
- Media file support
- Chat archiving and pinning

## 🛠️ Troubleshooting

### Common Issues
- **Connection Errors**: Check `.env` database credentials
- **Migration Failures**: Review migration files and database state
- **Sync Issues**: Use **DIAGNOSE_USER_SYNC.md** for diagnosis
- **Performance**: Review **DATABASE_ANALYSIS.md** for optimization

### Quick Fixes
- Check **QUICK_FIX_USER_SYNC.md** for user sync problems
- Verify database user permissions
- Check foreign key constraints
- Review migration order

## 📚 Related Documentation

- **Main README**: [`../../README.md`](../../README.md)
- **Deployment**: [`../deployment/DEPLOYMENT_GUIDE.md`](../deployment/DEPLOYMENT_GUIDE.md)
- **Environment**: [`../environment/ENV-FIX-INSTRUCTIONS.md`](../environment/ENV-FIX-INSTRUCTIONS.md)
- **Analysis**: [`../analysis/DATABASE_ANALYSIS.md`](../analysis/DATABASE_ANALYSIS.md)

## 🔗 Support

- **Website**: [rwamp.io](https://rwamp.io)
- **Email**: info@rwamp.net
- **Phone**: +92 370 1346038

---

## 🔙 Navigation

<p align="center">
  <a href="../../README.md">
    <img src="https://img.shields.io/badge/⬅️%20Back%20to%20Main-FF6B6B?style=for-the-badge&logo=arrow-left&logoColor=white" alt="Back to Main" />
  </a>
  <a href="../README.md">
    <img src="https://img.shields.io/badge/📚%20Documentation%20Index-06D6A0?style=for-the-badge&logo=book&logoColor=white" alt="Documentation Index" />
  </a>
</p>

---

**Last Updated:** January 27, 2025

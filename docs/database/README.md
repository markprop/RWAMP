# Database Documentation

This directory contains database setup, migration, and sync documentation.

## 📄 Documents

### Setup & Analysis
- **DATABASE_SETUP_GUIDE.md** - Complete database setup instructions
- **DATABASE_ANALYSIS.md** - Database schema and structure analysis

### Migration & Sync
- **USER_SYNC_MIGRATION_GUIDE.md** - User synchronization migration guide
- **LIVE_DATABASE_SYNC_INSTRUCTIONS.md** - Live database synchronization
- **DIAGNOSE_USER_SYNC.md** - Diagnosing user sync issues
- **QUICK_FIX_USER_SYNC.md** - Quick fixes for user sync problems

## 🗄️ Database Structure

The RWAMP application uses MySQL/MariaDB with the following key tables:
- `users` - User accounts with roles and KYC
- `crypto_payments` - Crypto payment records
- `transactions` - Transaction history
- `reseller_applications` - Reseller program applications
- `withdraw_requests` - Withdrawal requests
- `game_sessions` - Trading game sessions
- `chats` - Chat system tables

## 🔧 Common Tasks

### Initial Setup
1. Follow **DATABASE_SETUP_GUIDE.md**
2. Run migrations: `php artisan migrate`
3. Seed initial data: `php artisan db:seed`

### User Synchronization
1. Review **USER_SYNC_MIGRATION_GUIDE.md**
2. Follow **LIVE_DATABASE_SYNC_INSTRUCTIONS.md** for live sync
3. Use **DIAGNOSE_USER_SYNC.md** if issues occur

### Troubleshooting
- Check **QUICK_FIX_USER_SYNC.md** for common issues
- Review **DATABASE_ANALYSIS.md** for schema understanding

---

**Last Updated:** 2025-01-27


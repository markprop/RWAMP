# Database Migration Guide for Laravel

## Step-by-Step Migration Process

### Step 1: Verify Database Configuration

First, make sure your `.env` file has the correct database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**For Hostinger/Remote Databases:**
- `DB_HOST` might be `localhost` or `mysql.hostinger.com`
- Check your hosting control panel for the correct hostname

### Step 2: Clear Configuration Cache

Before running migrations, clear Laravel's configuration cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Test Database Connection

Test if you can connect to the database:

```bash
php artisan tinker
```

Then in tinker, type:
```php
DB::connection()->getPdo();
```

If successful, you'll see the PDO object. Type `exit` to leave tinker.

**Alternative:** Check connection status:
```bash
php artisan db:show
```

### Step 4: Check Migration Status

See which migrations have been run and which are pending:

```bash
php artisan migrate:status
```

This shows:
- ✅ **Ran** - Migration has been executed
- ⏳ **Pending** - Migration hasn't been run yet

### Step 5: Run Migrations

#### Option A: Run All Pending Migrations (Recommended)

```bash
php artisan migrate
```

This will run all migrations that haven't been executed yet.

#### Option B: Run Migrations with Output

```bash
php artisan migrate --verbose
```

Shows detailed output of what's happening.

#### Option C: Run Migrations in Production

```bash
php artisan migrate --force
```

Use `--force` flag in production to skip confirmation prompts.

### Step 6: Run Migrations with Seeders (Optional)

If you have seeders (initial data), run them after migrations:

```bash
php artisan db:seed
```

Or run migrations and seeders together:

```bash
php artisan migrate --seed
```

---

## Fresh Start (⚠️ WARNING: Deletes All Data!)

### Option A: Fresh Migration (Drops all tables and re-runs migrations)

```bash
php artisan migrate:fresh
```

**⚠️ WARNING:** This will:
- Drop all tables
- Delete all data
- Re-run all migrations from scratch

### Option B: Fresh Migration with Seeders

```bash
php artisan migrate:fresh --seed
```

This drops everything, re-runs migrations, and seeds initial data.

### Option C: Refresh (Rollback and re-run)

```bash
php artisan migrate:refresh
```

This will:
- Rollback all migrations
- Re-run all migrations
- **Preserves data** (if migrations support it)

---

## Rollback Migrations

### Rollback Last Batch

```bash
php artisan migrate:rollback
```

Rolls back the last batch of migrations.

### Rollback Specific Steps

```bash
php artisan migrate:rollback --step=3
```

Rolls back the last 3 migration batches.

### Rollback All Migrations

```bash
php artisan migrate:reset
```

⚠️ **WARNING:** This rolls back ALL migrations and deletes all data.

---

## Common Migration Commands

| Command | Description |
|---------|-------------|
| `php artisan migrate` | Run all pending migrations |
| `php artisan migrate:status` | Show migration status |
| `php artisan migrate:rollback` | Rollback last batch |
| `php artisan migrate:refresh` | Rollback and re-run all |
| `php artisan migrate:fresh` | Drop all tables and re-run |
| `php artisan migrate:fresh --seed` | Fresh start with seeders |
| `php artisan db:seed` | Run seeders only |
| `php artisan migrate:reset` | Rollback all migrations |

---

## Troubleshooting

### Issue 1: "Access Denied" Error

**Error:** `Access denied for user 'username'@'localhost'`

**Solutions:**
1. Check database credentials in `.env`
2. Verify database exists in phpMyAdmin/hosting panel
3. Ensure user has proper permissions
4. Try `DB_HOST=localhost` instead of `127.0.0.1`

### Issue 2: "Unknown Database" Error

**Error:** `Unknown database 'database_name'`

**Solution:**
1. Create the database in phpMyAdmin or hosting control panel
2. Grant privileges to the user
3. Update `.env` with correct database name

### Issue 3: "Table Already Exists" Error

**Error:** `SQLSTATE[42S01]: Base table or view already exists`

**Solutions:**
1. Check migration status: `php artisan migrate:status`
2. If migration shows as "Ran" but table doesn't exist, manually mark it:
   ```bash
   php artisan migrate --pretend
   ```
3. Or use fresh migration (⚠️ deletes data):
   ```bash
   php artisan migrate:fresh
   ```

### Issue 4: Migration Stuck or Failed

**Solution:**
1. Check Laravel logs: `storage/logs/laravel.log`
2. Rollback the failed migration:
   ```bash
   php artisan migrate:rollback
   ```
3. Fix the migration file
4. Run migrations again:
   ```bash
   php artisan migrate
   ```

### Issue 5: Foreign Key Constraint Errors

**Error:** `Cannot add foreign key constraint`

**Solution:**
1. Check migration order - tables must be created before foreign keys
2. Ensure referenced tables exist
3. Check column types match (e.g., `unsignedBigInteger` for foreign keys)

---

## Best Practices

### 1. Always Backup Before Migrations

```bash
# Export database
mysqldump -u username -p database_name > backup.sql
```

### 2. Test Migrations Locally First

Always test migrations on a local/staging environment before production.

### 3. Use Transactions (Laravel does this automatically)

Laravel wraps migrations in transactions, so if one fails, all are rolled back.

### 4. Check Migration Status Regularly

```bash
php artisan migrate:status
```

### 5. Don't Modify Existing Migrations

If you need to change a migration that's already been run:
- Create a new migration to alter the table
- Or use `migrate:fresh` (only in development)

---

## Quick Reference for Your Project

### Your Current Migrations:

1. `2014_10_12_200000_add_two_factor_columns_to_users_table.php`
2. `2024_01_01_000000_create_users_table.php`
3. `2024_01_01_000001_create_contacts_table.php`
4. `2024_01_01_000002_create_reseller_applications_table.php`
5. `2024_01_01_000003_create_newsletter_subscriptions_table.php`
6. `2024_01_01_000004_create_password_reset_tokens_table.php`
7. `2025_10_14_000100_add_role_and_reseller_fields_to_users_table.php`
8. `2025_10_15_000200_add_wallet_and_token_balance_to_users_table.php`
9. `2025_10_15_000300_create_crypto_payments_table.php`
10. `2025_10_15_000400_create_transactions_table.php`
11. `2025_10_17_121316_drop_crypto_payments_table.php`
12. `2025_11_04_000001_create_processed_crypto_tx_table.php`
13. `2025_11_06_111927_recreate_crypto_payments_table.php`
14. `2025_11_06_121500_add_coin_price_to_crypto_payments.php`
15. `2025_11_10_102459_add_kyc_fields_to_users_table.php`

### Recommended Migration Flow:

```bash
# 1. Clear cache
php artisan config:clear

# 2. Check status
php artisan migrate:status

# 3. Run migrations
php artisan migrate

# 4. Verify tables were created
php artisan tinker
DB::select('SHOW TABLES');
exit
```

---

## For Hostinger Specifically

If you're using Hostinger, follow these steps:

1. **Get Database Details from Hostinger Panel:**
   - Go to Hostinger Control Panel → Databases → MySQL Databases
   - Note the database name, username, and hostname

2. **Update `.env` file:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=localhost
   # OR the hostname provided by Hostinger (e.g., mysql.hostinger.com)
   DB_PORT=3306
   DB_DATABASE=u945985759_rwamp_database
   DB_USERNAME=u945985759_admin
   DB_PASSWORD=your_password
   ```

3. **Clear config and test:**
   ```bash
   php artisan config:clear
   php artisan tinker
   DB::connection()->getPdo();
   exit
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

---

## Need Help?

If you encounter issues:
1. Check `storage/logs/laravel.log` for detailed error messages
2. Verify database credentials in hosting control panel
3. Test connection using phpMyAdmin or MySQL Workbench
4. Ensure database user has ALL PRIVILEGES on the database


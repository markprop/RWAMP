# Database Setup Guide for RWAMP Laravel

## Step-by-Step Guide to Activate New Database

### Step 1: Verify .env File Configuration

Make sure your `.env` file has the correct database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u945985759_rwamp_database
DB_USERNAME=u945985759_admin
DB_PASSWORD=WeMark2001$
```

**Important Notes:**
- If you're using **Hostinger** or a remote database, `DB_HOST` might need to be the actual server hostname (e.g., `mysql.hostinger.com` or the IP address provided by Hostinger)
- For local databases, use `127.0.0.1` or `localhost`
- Make sure there are **no spaces** around the `=` sign
- Make sure the password doesn't have any extra quotes

### Step 2: Clear Laravel Cache

Run these commands to clear all cached configurations:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Test Database Connection

**Option A: Test via Tinker (Recommended)**
```bash
php artisan tinker
```
Then in tinker, type:
```php
DB::connection()->getPdo();
```
If it connects successfully, you'll see the PDO object. Type `exit` to leave tinker.

**Option B: Create Database if it doesn't exist**
If the database doesn't exist yet, you need to create it first:
1. Log into your hosting control panel (cPanel/phpMyAdmin)
2. Create a new database named: `u945985759_rwamp_database`
3. Make sure the user `u945985759_admin` has full privileges on this database

### Step 4: Run Migrations

Once the connection is working, run migrations:

```bash
# Check migration status
php artisan migrate:status

# Run all pending migrations
php artisan migrate

# If you need to reset and re-run all migrations (WARNING: This will delete all data!)
php artisan migrate:fresh

# If you want to reset, re-run migrations, and seed data
php artisan migrate:fresh --seed
```

### Step 5: Seed Initial Data (Optional)

If you have seeders, run them:

```bash
php artisan db:seed
```

### Troubleshooting Common Issues

#### Issue 1: Access Denied Error
**Error:** `Access denied for user 'u945985759_admin'@'localhost'`

**Solutions:**
1. **Check if database exists:** Log into phpMyAdmin and verify the database exists
2. **Check user permissions:** Make sure the user has ALL PRIVILEGES on the database
3. **Check host:** For remote databases (Hostinger), the host might be different:
   - Try: `mysql.hostinger.com` or the hostname provided by your hosting
   - Check your hosting control panel for the correct MySQL hostname
4. **Verify password:** Double-check the password in your `.env` file
5. **Check user host:** The user might be configured for a specific host. Try:
   - `%` (all hosts)
   - Your server's IP address
   - The actual hostname

#### Issue 2: Database Doesn't Exist
**Error:** `Unknown database 'u945985759_rwamp_database'`

**Solution:**
1. Create the database via phpMyAdmin or hosting control panel
2. Grant privileges to the user
3. Try connecting again

#### Issue 3: Connection Timeout
**Error:** `Connection timed out` or `Can't connect to MySQL server`

**Solutions:**
1. **For remote databases:** Make sure remote MySQL connections are enabled in your hosting
2. **Check firewall:** Ensure port 3306 is open
3. **Verify host:** Use the correct hostname/IP provided by your hosting provider

### Quick Setup Commands (Copy & Paste)

```bash
# Navigate to project directory
cd rwamp-laravel

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Test connection (in tinker)
php artisan tinker
# Then type: DB::connection()->getPdo();
# Type 'exit' to leave tinker

# Run migrations
php artisan migrate

# If you need to start fresh (WARNING: Deletes all data!)
php artisan migrate:fresh --seed
```

### For Hostinger Specifically

If you're using Hostinger, the database host might be:
- `localhost` (if using localhost connection)
- `mysql.hostinger.com` (if using remote connection)
- Or check your Hostinger control panel → Databases → MySQL Databases for the exact hostname

Update your `.env` file accordingly:
```env
DB_HOST=localhost
# OR
DB_HOST=mysql.hostinger.com
```

### Verify Everything Works

After migrations complete successfully, verify:
1. Check if tables were created:
   ```bash
   php artisan tinker
   DB::select('SHOW TABLES');
   exit
   ```

2. Test a simple query:
   ```bash
   php artisan tinker
   \App\Models\User::count();
   exit
   ```

### Need Help?

If you're still having issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database credentials in hosting control panel
3. Test connection using a database client (phpMyAdmin, MySQL Workbench, etc.)


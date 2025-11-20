# Dashboard 500 Error - Fix Guide

## Issues Fixed

### 1. **Recovery Codes Error (Most Likely Cause)**
The dashboard view was calling `recoveryCodes()` method even when `two_factor_recovery_codes` was null, causing a decryption error.

**Fixed in:** `resources/views/dashboard/admin.blade.php`
- Added null check before calling `recoveryCodes()`
- Added try-catch block to handle any exceptions
- Added fallback message when recovery codes are not available

### 2. **Error Handling in Controller**
Added comprehensive error handling in `AdminController@dashboard` method to catch and log any errors instead of throwing 500 errors.

**Fixed in:** `app/Http/Controllers/AdminController.php`
- Wrapped dashboard logic in try-catch
- Logs errors with full stack trace
- Returns view with error message instead of crashing

## Additional Steps to Debug (If Issue Persists)

### Step 1: Check Laravel Logs
```bash
# On your server, check the latest error logs
tail -f storage/logs/laravel.log
```

Or check the log file directly:
```bash
cat storage/logs/laravel.log | tail -50
```

### Step 2: Check Database Tables
Ensure all required tables exist:
```bash
php artisan migrate:status
```

If migrations are missing, run:
```bash
php artisan migrate
```

### Step 3: Check 2FA Middleware
The dashboard requires 2FA to be enabled. If you haven't set up 2FA yet:

1. Visit: `https://dev.rwamp.net/admin/2fa/setup`
2. Enable 2FA by clicking "Enable Two-Factor Authentication"
3. Scan the QR code with an authenticator app (Google Authenticator, Authy, etc.)
4. Save your recovery codes
5. Try accessing the dashboard again

### Step 4: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Step 5: Check Environment Variables
Ensure your `.env` file has all required configurations:
- Database connection settings
- APP_KEY is set
- APP_DEBUG should be `false` in production

### Step 6: Check File Permissions
```bash
# Ensure storage and cache directories are writable
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 7: Verify User Role
Ensure your logged-in user has the `admin` role:
```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'your-email@example.com')->first();
>>> $user->role;
>>> exit
```

### Step 8: Test Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

## Common Issues and Solutions

### Issue: "Class not found" errors
**Solution:** Run `composer dump-autoload`

### Issue: "Table doesn't exist" errors
**Solution:** Run `php artisan migrate`

### Issue: "View not found" errors
**Solution:** Run `php artisan view:clear`

### Issue: Middleware redirecting incorrectly
**Solution:** Check `app/Http/Middleware/EnsureAdminTwoFactorEnabled.php` - it should redirect to `/admin/2fa/setup` if 2FA is not enabled

## Testing the Fix

1. **Clear all caches:**
   ```bash
   php artisan optimize:clear
   ```

2. **Try accessing the dashboard:**
   - URL: `https://dev.rwamp.net/dashboard/admin`
   - If 2FA is not enabled, you should be redirected to `/admin/2fa/setup`
   - If 2FA is enabled, the dashboard should load

3. **Check browser console:**
   - Open Developer Tools (F12)
   - Check Console tab for JavaScript errors
   - Check Network tab for failed requests

## If Still Getting 500 Error

1. **Enable debug mode temporarily:**
   - In `.env`, set `APP_DEBUG=true`
   - This will show the actual error message
   - **IMPORTANT:** Set it back to `false` after debugging

2. **Check the actual error:**
   - The error message will now be visible on the page
   - Share this error message for further debugging

3. **Check server error logs:**
   - Apache: `/var/log/apache2/error.log`
   - Nginx: `/var/log/nginx/error.log`
   - PHP-FPM: Check PHP-FPM error logs

## Contact Points

If the issue persists after following all steps:
1. Check `storage/logs/laravel.log` for the exact error
2. Note the error message, file, and line number
3. Share these details for further assistance


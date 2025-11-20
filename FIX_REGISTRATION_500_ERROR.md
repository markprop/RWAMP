# 🔧 Fix Registration 500 Error on Live Server

This guide will help you fix the 500 error when submitting the reseller application form on your live server.

## 🔍 Common Causes

1. **Database Table Missing** - `reseller_applications` table doesn't exist
2. **Email Configuration** - Mail settings not configured properly
3. **Missing Email Templates** - Email view files missing
4. **File Permissions** - Storage/logs not writable
5. **Environment Variables** - Missing or incorrect `.env` settings
6. **Cache Issues** - Old cached config causing conflicts

---

## ✅ Step-by-Step Fix

### Step 1: Check Laravel Logs

**Via SSH:**
```bash
tail -50 storage/logs/laravel.log
```

**Via cPanel File Manager:**
- Navigate to `storage/logs/`
- Open `laravel.log`
- Check the last 50 lines for errors

**Look for:**
- "Table 'reseller_applications' doesn't exist"
- "Class 'Mail' not found"
- "View [emails.reseller-notification] not found"
- "Connection refused" (database/email)

---

### Step 2: Verify Database Table Exists

**Via SSH:**
```bash
php artisan tinker
```

Then:
```php
// Check if table exists
DB::select("SHOW TABLES LIKE 'reseller_applications'");
// Should return array with table name

// Check table structure
DB::select("DESCRIBE reseller_applications");
// Should show all columns

exit
```

**Via phpMyAdmin:**
1. Log in to phpMyAdmin
2. Select your database
3. Check if `reseller_applications` table exists
4. If missing, run migrations (see Step 3)

---

### Step 3: Run Database Migrations

**If table is missing:**

**Via SSH:**
```bash
php artisan migrate --force
```

**Check migration status:**
```bash
php artisan migrate:status
```

**If migrations fail, check:**
- Database credentials in `.env`
- Database user has CREATE TABLE permissions
- Database connection works: `php artisan tinker` → `DB::connection()->getPdo();`

---

### Step 4: Check Email Configuration

**Verify `.env` has mail settings:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your_email@yourdomain.com
MAIL_FROM_NAME="RWAMP"
```

**Test email configuration:**
```bash
php artisan tinker
```

Then:
```php
Mail::raw('Test email', function($m) {
    $m->to('your-email@example.com')->subject('Test');
});
exit
```

**If email fails:**
- Check mail credentials
- Verify SMTP settings with Hostinger
- Temporarily disable email sending (see Step 6)

---

### Step 5: Verify Email Templates Exist

**Check if these files exist:**
- `resources/views/emails/reseller-notification.blade.php`
- `resources/views/emails/reseller-confirmation.blade.php`

**Via SSH:**
```bash
ls -la resources/views/emails/
```

**If missing:**
- Upload from your local project
- Or create placeholder templates (see below)

---

### Step 6: Temporarily Disable Email (Quick Fix)

If email is causing the issue, you can temporarily disable it:

**Edit:** `app/Http/Controllers/AuthController.php`

Find the `registerResellerApplication` method and comment out email sending:

```php
// Send notification emails
try {
    // Temporarily disabled for debugging
    // $emailService = new \App\Services\EmailService();
    // $emailService->sendResellerNotification($application);
    Log::info("Reseller application created (email disabled)", [
        'application_id' => $application->id,
        'email' => $application->email,
    ]);
} catch (\Exception $e) {
    Log::error("Failed to send reseller application notification: " . $e->getMessage());
    // Continue even if email fails
}
```

**After fixing email, re-enable it.**

---

### Step 7: Clear All Caches

**Via SSH:**
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Step 8: Check File Permissions

**Via SSH:**
```bash
# Ensure storage is writable
chmod -R 775 storage
chown -R your_username:your_username storage

# Ensure logs directory is writable
chmod -R 775 storage/logs
```

---

### Step 9: Enable Debug Mode Temporarily

**In `.env`:**
```env
APP_DEBUG=true
```

**Clear config cache:**
```bash
php artisan config:clear
php artisan config:cache
```

**Try submitting the form again** - you'll see the actual error message.

**⚠️ IMPORTANT:** Set `APP_DEBUG=false` after fixing!

---

## 🚨 Quick Diagnostic Script

Create a file `test-registration.php` in your project root:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Registration Diagnostic ===\n\n";

// 1. Check database connection
try {
    DB::connection()->getPdo();
    echo "✅ Database connection: OK\n";
} catch (\Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "\n";
}

// 2. Check reseller_applications table
try {
    $exists = DB::select("SHOW TABLES LIKE 'reseller_applications'");
    if (!empty($exists)) {
        echo "✅ reseller_applications table: EXISTS\n";
    } else {
        echo "❌ reseller_applications table: MISSING\n";
    }
} catch (\Exception $e) {
    echo "❌ reseller_applications table: ERROR - " . $e->getMessage() . "\n";
}

// 3. Check email configuration
try {
    $mailHost = config('mail.mailers.smtp.host');
    $mailPort = config('mail.mailers.smtp.port');
    if ($mailHost && $mailPort) {
        echo "✅ Email configuration: SET (Host: $mailHost, Port: $mailPort)\n";
    } else {
        echo "⚠️  Email configuration: INCOMPLETE\n";
    }
} catch (\Exception $e) {
    echo "❌ Email configuration: ERROR - " . $e->getMessage() . "\n";
}

// 4. Check email templates
$templates = [
    'resources/views/emails/reseller-notification.blade.php',
    'resources/views/emails/reseller-confirmation.blade.php',
];
foreach ($templates as $template) {
    if (file_exists($template)) {
        echo "✅ Email template exists: " . basename($template) . "\n";
    } else {
        echo "❌ Email template missing: " . basename($template) . "\n";
    }
}

// 5. Check storage permissions
if (is_writable(storage_path('logs'))) {
    echo "✅ Storage/logs: WRITABLE\n";
} else {
    echo "❌ Storage/logs: NOT WRITABLE\n";
}

echo "\n=== End Diagnostic ===\n";
```

**Run it:**
```bash
php test-registration.php
```

---

## 🔧 Most Common Fix

**90% of the time, the issue is one of these:**

### Fix 1: Missing Database Table
```bash
php artisan migrate --force
```

### Fix 2: Email Configuration
- Update `.env` with correct mail settings
- Clear config cache: `php artisan config:clear && php artisan config:cache`

### Fix 3: Missing Email Templates
- Upload email templates from local project
- Or temporarily disable email (see Step 6)

### Fix 4: Cache Issues
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 After Fixing

1. **Test the form:**
   - Fill out reseller application
   - Submit
   - Should redirect with success message

2. **Check logs:**
   ```bash
   tail -20 storage/logs/laravel.log
   ```
   - Should show no errors
   - Should show "Reseller application created" log

3. **Verify in database:**
   ```bash
   php artisan tinker
   ```
   ```php
   \App\Models\ResellerApplication::latest()->first();
   // Should show your test application
   exit
   ```

4. **Set APP_DEBUG back to false:**
   ```env
   APP_DEBUG=false
   ```
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

---

## 🆘 Still Not Working?

1. **Check the exact error:**
   - Enable `APP_DEBUG=true`
   - Submit form
   - Copy the full error message

2. **Check server error logs:**
   - cPanel → Error Logs
   - Look for PHP errors

3. **Verify all files uploaded:**
   - Check `app/Http/Controllers/AuthController.php` exists
   - Check `app/Models/ResellerApplication.php` exists
   - Check `app/Services/EmailService.php` exists

4. **Test database directly:**
   ```bash
   php artisan tinker
   ```
   ```php
   // Try creating a test application
   \App\Models\ResellerApplication::create([
       'name' => 'Test',
       'email' => 'test@test.com',
       'phone' => '+1234567890',
       'password' => bcrypt('password'),
       'investment_capacity' => '1-10k',
       'status' => 'pending',
   ]);
   // If this works, the issue is in the controller
   exit
   ```

---

## ✅ Success Checklist

- [ ] Database table `reseller_applications` exists
- [ ] Migrations run successfully
- [ ] Email configuration set in `.env`
- [ ] Email templates exist
- [ ] Storage/logs is writable
- [ ] All caches cleared and rebuilt
- [ ] Form submission works
- [ ] Success message displays
- [ ] Application saved in database
- [ ] No errors in logs
- [ ] `APP_DEBUG=false` in production

---

**Fix Guide Version:** 1.0
**Last Updated:** 2024


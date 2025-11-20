# 🔍 Post-Deployment Verification Guide

After deploying your RWAMP Laravel application to Hostinger, use this guide to verify everything is working correctly.

## 🚀 Quick Verification (5 minutes)

### 1. Homepage Check
```
✅ Visit: https://yourdomain.com
✅ Should load without errors
✅ CSS and JavaScript should load
✅ Images should display
✅ No console errors (F12 → Console)
```

### 2. Login Check
```
✅ Visit: https://yourdomain.com/login
✅ Login form should display
✅ Try logging in with test account
✅ Should redirect to appropriate dashboard
```

### 3. Dashboard Check
```
✅ Admin: https://yourdomain.com/dashboard/admin
✅ Investor: https://yourdomain.com/dashboard/investor
✅ Reseller: https://yourdomain.com/dashboard/reseller
✅ Should load without 500 errors
```

---

## 🔍 Detailed Verification

### A. File System Checks

**Via SSH:**
```bash
# Check storage permissions
ls -la storage/
# Expected: drwxrwxr-x

# Check bootstrap/cache permissions
ls -la bootstrap/cache/
# Expected: drwxrwxr-x

# Check storage symlink
ls -la public/storage
# Expected: lrwxrwxrwx → ../storage/app/public

# Check .env exists
ls -la .env
# Expected: -rw------- (600)
```

**Via cPanel File Manager:**
- Navigate to `storage/` → Check permissions show 775
- Navigate to `bootstrap/cache/` → Check permissions show 775
- Navigate to `public/storage` → Should be a symlink
- Check `.env` file exists in root

---

### B. Database Checks

**Via SSH:**
```bash
php artisan tinker
```

Then in tinker:
```php
// Test database connection
DB::connection()->getPdo();
// Should return: PDO object

// Check if tables exist
DB::select('SHOW TABLES');
// Should show all your tables

// Check users table
\App\Models\User::count();
// Should return number of users

// Check if admin user exists
\App\Models\User::where('role', 'admin')->count();
// Should return at least 1

exit
```

**Via phpMyAdmin:**
- Log in to phpMyAdmin
- Select your database
- Check if all tables exist:
  - `users`
  - `crypto_payments`
  - `transactions`
  - `reseller_applications`
  - etc.

---

### C. Environment Checks

**Via SSH:**
```bash
php artisan tinker
```

Then:
```php
// Check APP_ENV
config('app.env');
// Expected: "production"

// Check APP_DEBUG
config('app.debug');
// Expected: false

// Check APP_URL
config('app.url');
// Expected: "https://yourdomain.com"

// Check database connection
config('database.default');
// Expected: "mysql"

// Check cache driver
config('cache.default');
// Expected: "file"

exit
```

---

### D. Asset Checks

**Via Browser:**
1. Open homepage
2. Press F12 → Network tab
3. Reload page
4. Check for:
   - ✅ `build/assets/app-*.css` loads (200 OK)
   - ✅ `build/assets/app-*.js` loads (200 OK)
   - ✅ No 404 errors for assets

**Via File Manager:**
- Navigate to `public/build/`
- Check files exist:
  - `manifest.json`
  - `assets/app-*.css`
  - `assets/app-*.js`

---

### E. Storage Checks

**Via SSH:**
```bash
# Check storage directories exist
ls -la storage/app/
ls -la storage/framework/
ls -la storage/logs/

# Check if storage is writable
touch storage/test.txt
rm storage/test.txt
# Should work without errors
```

**Via Browser:**
1. Log in as admin
2. Try uploading a file (KYC document or payment screenshot)
3. Check if file appears in `storage/app/`
4. Check if file is accessible via `public/storage/`

---

### F. Cache Checks

**Via SSH:**
```bash
# Check if caches are built
ls -la bootstrap/cache/config.php
# Should exist

ls -la bootstrap/cache/routes-v7.php
# Should exist

# Check view cache
ls -la storage/framework/views/
# Should contain compiled views
```

**Clear and rebuild if needed:**
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### G. Log Checks

**Via SSH:**
```bash
# Check latest logs
tail -50 storage/logs/laravel.log

# Check for errors
grep -i error storage/logs/laravel.log | tail -20

# Check for exceptions
grep -i exception storage/logs/laravel.log | tail -20
```

**What to look for:**
- ❌ No "Class not found" errors
- ❌ No "Table doesn't exist" errors
- ❌ No "Permission denied" errors
- ❌ No "File not found" errors
- ✅ Only expected warnings (if any)

---

### H. Feature-Specific Checks

#### 1. Authentication
```
✅ Registration works
✅ Email verification works (if enabled)
✅ Login works
✅ Logout works
✅ Password reset works
✅ Remember me works
```

#### 2. Admin Dashboard
```
✅ Dashboard loads: /dashboard/admin
✅ 2FA setup works: /admin/2fa/setup
✅ User management works
✅ Crypto payments management works
✅ Applications management works
✅ KYC management works
✅ Price management works
```

#### 3. Crypto Payments
```
✅ Purchase page loads: /purchase
✅ QR code generation works
✅ Payment submission works
✅ Payment approval works
✅ Transaction history works
```

#### 4. File Uploads
```
✅ KYC document upload works
✅ Payment screenshot upload works
✅ Files are stored correctly
✅ Files are accessible via URL
```

#### 5. Email
```
✅ Password reset email sends
✅ OTP email sends
✅ Notification emails send
✅ Email templates render correctly
```

---

## 🐛 Common Issues & Solutions

### Issue: 500 Internal Server Error

**Check:**
1. `storage/logs/laravel.log` for specific error
2. File permissions (storage, bootstrap/cache)
3. `.env` file exists and is configured
4. `APP_KEY` is set
5. Database connection

**Fix:**
```bash
# Clear caches
php artisan optimize:clear

# Fix permissions
chmod -R 775 storage bootstrap/cache

# Regenerate key if needed
php artisan key:generate
```

---

### Issue: Assets Not Loading

**Check:**
1. `public/build/` directory exists
2. `public/build/manifest.json` exists
3. Vite configuration is correct

**Fix:**
```bash
# Rebuild assets locally
npm run build

# Upload public/build/ to server
# Or rebuild on server if Node.js available
```

---

### Issue: Storage Files Not Accessible

**Check:**
1. Storage symlink exists: `public/storage` → `storage/app/public`
2. File permissions are correct
3. Files exist in `storage/app/`

**Fix:**
```bash
# Recreate symlink
php artisan storage:link

# Fix permissions
chmod -R 775 storage
```

---

### Issue: Database Connection Error

**Check:**
1. Database credentials in `.env`
2. Database exists in cPanel
3. User has proper permissions

**Fix:**
```bash
# Test connection
php artisan tinker
DB::connection()->getPdo();

# Update .env with correct credentials
```

---

### Issue: Dashboard Crashes

**Check:**
1. User has correct role (`admin`, `investor`, `reseller`)
2. 2FA is enabled for admin (if required)
3. Recovery codes are not corrupted
4. Check logs for specific error

**Fix:**
```bash
# Check user role
php artisan tinker
$user = \App\Models\User::find(1);
$user->role; // Should be 'admin'

# Enable 2FA if needed
# Visit: /admin/2fa/setup

# Clear corrupted recovery codes if needed
$user->two_factor_recovery_codes = null;
$user->save();
```

---

## 📊 Performance Checks

### 1. Page Load Time
- Homepage should load in < 3 seconds
- Dashboard should load in < 2 seconds
- Use browser DevTools → Network tab

### 2. Database Queries
- Check for N+1 query problems
- Use Laravel Debugbar (if enabled in dev)
- Monitor slow queries

### 3. Asset Size
- CSS bundle should be < 500KB
- JS bundle should be < 500KB
- Images should be optimized

---

## 🔒 Security Checks

### 1. Environment
- ✅ `APP_DEBUG=false`
- ✅ `.env` not accessible via web
- ✅ `.env` permissions: 600

### 2. SSL
- ✅ HTTPS enabled
- ✅ SSL certificate valid
- ✅ Mixed content warnings resolved

### 3. Headers
- ✅ Security headers present
- ✅ CSRF protection working
- ✅ XSS protection enabled

---

## 📝 Verification Report Template

```
Deployment Verification Report
==============================

Date: _______________
Domain: _______________
Deployed By: _______________

File System: ✅ / ❌
Database: ✅ / ❌
Environment: ✅ / ❌
Assets: ✅ / ❌
Storage: ✅ / ❌
Cache: ✅ / ❌
Logs: ✅ / ❌

Features Tested:
- Homepage: ✅ / ❌
- Login: ✅ / ❌
- Dashboard: ✅ / ❌
- File Upload: ✅ / ❌
- Email: ✅ / ❌

Issues Found:
1. 
2. 
3. 

Resolutions:
1. 
2. 
3. 

Status: ✅ Ready for Production / ⚠️ Needs Fixes / ❌ Not Ready

Notes:
```

---

## ✅ Final Checklist

- [ ] All verification steps completed
- [ ] No critical errors in logs
- [ ] All features working
- [ ] Performance acceptable
- [ ] Security checks passed
- [ ] Backup configured
- [ ] Monitoring set up
- [ ] Team notified

---

**Verification Guide Version:** 1.0
**Last Updated:** 2024


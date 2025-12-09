# 🔄 Safe Update Guide - Prevent Crashes When Updating Files

This guide explains how to safely update files on your live Hostinger deployment without causing crashes.

## ⚠️ Why Updates Cause Crashes

Common reasons for crashes during updates:

1. **Cache Issues**: Old cached files conflict with new code
2. **File Permissions**: New files don't have correct permissions
3. **Missing Dependencies**: New code requires dependencies not installed
4. **Database Changes**: New migrations not run
5. **Asset Issues**: New assets not built/uploaded
6. **Environment Changes**: `.env` file overwritten or corrupted
7. **Partial Uploads**: Files uploaded partially or in wrong order

---

## 🛡️ Pre-Update Checklist

Before updating ANY files:

- [ ] **Backup Everything**
  - [ ] Database backup (via phpMyAdmin or command)
  - [ ] `.env` file backup
  - [ ] Full file backup (optional but recommended)

- [ ] **Test Locally First**
  - [ ] Test changes on local/staging environment
  - [ ] Verify no errors in local logs
  - [ ] Test all affected features

- [ ] **Check Dependencies**
  - [ ] Review `composer.json` changes (if any)
  - [ ] Review `package.json` changes (if any)
  - [ ] Note any new environment variables needed

- [ ] **Plan the Update**
  - [ ] List all files to be updated
  - [ ] Determine update order (if critical)
  - [ ] Schedule update during low-traffic period

---

## 📋 Safe Update Process

### Step 1: Backup (5 minutes)

**Via SSH:**
```bash
# Backup database
mysqldump -u your_db_user -p your_db_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup .env
cp .env .env.backup_$(date +%Y%m%d_%H%M%S)

# Backup critical files (optional)
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz storage/ config/
```

**Via cPanel:**
1. Go to phpMyAdmin
2. Select your database
3. Click "Export" → "Go"
4. Save the SQL file

**Via File Manager:**
1. Right-click `.env` → Copy
2. Rename copy to `.env.backup`

---

### Step 2: Put Site in Maintenance Mode (Optional)

**Via SSH:**
```bash
php artisan down
```

This shows a maintenance page to users while you update.

**To bring back up:**
```bash
php artisan up
```

---

### Step 3: Upload Files

**Method A: Upload Changed Files Only (Recommended)**

1. **Via FTP/SFTP:**
   - Connect to server
   - Upload only changed files
   - Maintain directory structure
   - Don't overwrite `.env` unless necessary

2. **Via cPanel File Manager:**
   - Navigate to target directory
   - Upload files one by one or as ZIP
   - Extract if ZIP
   - Delete ZIP after extraction

**Method B: Upload All Files (If Major Update)**

1. Upload all files
2. **DON'T DELETE:**
   - `storage/` directory
   - `vendor/` directory (unless composer.json changed)
   - `public/build/` directory (unless assets changed)
   - `.env` file (unless you have a new one)

---

### Step 4: Set File Permissions

After uploading new files:

**Via SSH:**
```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Fix storage and cache permissions
chmod -R 775 storage bootstrap/cache
```

**Via cPanel:**
- Right-click new directories → Change Permissions → 755
- Right-click new files → Change Permissions → 644
- Fix `storage/` and `bootstrap/cache/` → 775

---

### Step 5: Update Dependencies (If Needed)

**If `composer.json` changed:**

**Via SSH:**
```bash
composer install --no-dev --optimize-autoloader
```

**If `package.json` changed:**

**Locally:**
```bash
npm install
npm run build
```

Then upload `public/build/` directory to server.

---

### Step 6: Run Database Migrations (If Needed)

**If new migrations exist:**

**Via SSH:**
```bash
php artisan migrate --force
```

**Check migration status first:**
```bash
php artisan migrate:status
```

---

### Step 7: Clear and Rebuild Caches

**This is CRITICAL - Do this after EVERY update:**

**Via SSH:**
```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Why this is important:**
- Config cache stores old configuration
- Route cache stores old routes
- View cache stores old compiled views
- These MUST be cleared after code changes

---

### Step 8: Verify Update

1. **Check Homepage:**
   - Visit: `https://yourdomain.com`
   - Should load without errors

2. **Check Logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```
   - Should show no new errors

3. **Test Critical Features:**
   - Login
   - Dashboard
   - File uploads
   - Forms

4. **Check Browser Console:**
   - Press F12 → Console tab
   - Should show no JavaScript errors

---

### Step 9: Bring Site Back Online

**If you put site in maintenance mode:**
```bash
php artisan up
```

---

## 🚨 Common Update Scenarios

### Scenario 1: Updating a Single Controller

**Files to update:**
- `app/Http/Controllers/YourController.php`

**Steps:**
1. Backup `.env` (safety)
2. Upload new controller file
3. Set permissions: `chmod 644 app/Http/Controllers/YourController.php`
4. Clear caches: `php artisan optimize:clear`
5. Rebuild caches: `php artisan config:cache && php artisan route:cache`
6. Test the affected routes

**Time:** 2-3 minutes

---

### Scenario 2: Updating Views

**Files to update:**
- `resources/views/your-view.blade.php`

**Steps:**
1. Upload new view file
2. Set permissions: `chmod 644 resources/views/your-view.blade.php`
3. Clear view cache: `php artisan view:clear`
4. Rebuild view cache: `php artisan view:cache`
5. Test the page

**Time:** 1-2 minutes

---

### Scenario 3: Updating Routes

**Files to update:**
- `routes/web.php`

**Steps:**
1. Upload new routes file
2. Set permissions: `chmod 644 routes/web.php`
3. Clear route cache: `php artisan route:clear`
4. Rebuild route cache: `php artisan route:cache`
5. Test all affected routes

**Time:** 2-3 minutes

---

### Scenario 4: Updating CSS/JavaScript

**Files to update:**
- `resources/css/app.css`
- `resources/js/app.js`

**Steps:**
1. **Locally:** Build assets
   ```bash
   npm run build
   ```
2. Upload updated source files to `resources/`
3. Upload entire `public/build/` directory
4. Clear browser cache (Ctrl+F5)
5. Test pages

**Time:** 5-10 minutes

---

### Scenario 5: Adding New Dependencies

**Files to update:**
- `composer.json` (PHP dependencies)
- `package.json` (Node dependencies)

**Steps:**
1. Backup everything
2. Upload updated `composer.json` or `package.json`
3. **For PHP:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
4. **For Node:**
   - Build locally: `npm install && npm run build`
   - Upload `public/build/` directory
5. Clear all caches
6. Test thoroughly

**Time:** 10-15 minutes

---

### Scenario 6: Database Schema Changes

**Files to update:**
- `database/migrations/new_migration.php`

**Steps:**
1. **CRITICAL:** Backup database first!
2. Upload new migration file
3. Run migration:
   ```bash
   php artisan migrate --force
   ```
4. Verify database changes in phpMyAdmin
5. Test affected features
6. Clear caches

**Time:** 5-10 minutes

---

### Scenario 7: Updating Configuration

**Files to update:**
- `config/your-config.php`

**Steps:**
1. Upload new config file
2. Set permissions: `chmod 644 config/your-config.php`
3. **IMPORTANT:** Clear config cache:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```
4. Test features using that config

**Time:** 2-3 minutes

---

### Scenario 8: Updating Environment Variables

**Files to update:**
- `.env`

**Steps:**
1. **CRITICAL:** Backup current `.env` file!
2. Download current `.env` from server
3. Make changes locally
4. Upload new `.env` file
5. Set permissions: `chmod 600 .env`
6. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```
7. Test all features

**Time:** 3-5 minutes

---

## ❌ NEVER Do These During Updates

1. **❌ Don't Delete `storage/` Directory**
   - Contains user uploads, logs, sessions
   - Will cause immediate crash
   - **Fix:** Only delete specific files if needed

2. **❌ Don't Delete `vendor/` Directory**
   - Contains all PHP dependencies
   - Will cause immediate crash
   - **Fix:** Run `composer install` if needed

3. **❌ Don't Delete `public/build/` Without Rebuilding**
   - Contains compiled CSS/JS
   - Site will have no styling/functionality
   - **Fix:** Rebuild assets first, then upload

4. **❌ Don't Overwrite `.env` Without Backup**
   - Contains critical configuration
   - Wrong values = site crash
   - **Fix:** Always backup first

5. **❌ Don't Change `APP_KEY` After 2FA Enabled**
   - Will corrupt 2FA recovery codes
   - Admin dashboard will crash
   - **Fix:** Regenerate recovery codes after key change

6. **❌ Don't Run `php artisan migrate:fresh`**
   - Deletes ALL database data
   - **Fix:** Use `php artisan migrate` instead

7. **❌ Don't Update During High Traffic**
   - Users may experience errors
   - **Fix:** Schedule during low-traffic period

8. **❌ Don't Skip Cache Clearing**
   - Old cached code will conflict
   - **Fix:** Always clear caches after updates

---

## 🔧 Quick Recovery Commands

If your site crashes after an update:

### 1. Clear All Caches
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Fix Permissions
```bash
chmod -R 775 storage bootstrap/cache
chmod 644 .env
```

### 3. Restore from Backup
```bash
# Restore .env
cp .env.backup .env

# Restore database (via phpMyAdmin or command)
mysql -u user -p database < backup.sql
```

### 4. Check Logs
```bash
tail -50 storage/logs/laravel.log
```

### 5. Enable Debug Temporarily
In `.env`:
```
APP_DEBUG=true
```
Check error, fix it, then set back to `false`.

---

## 📝 Update Log Template

Keep a log of all updates:

```
Update Log
==========

Date: 2024-XX-XX
Time: XX:XX
Updated By: [Your Name]

Files Updated:
- app/Http/Controllers/ExampleController.php
- resources/views/example.blade.php

Changes Made:
- Fixed bug in payment processing
- Updated dashboard layout

Dependencies Changed:
- None

Database Changes:
- None

Caches Cleared:
- ✅ Config cache
- ✅ Route cache
- ✅ View cache

Testing:
- ✅ Homepage loads
- ✅ Login works
- ✅ Dashboard works
- ✅ Payment processing works

Issues Encountered:
- None

Status: ✅ Success / ⚠️ Issues / ❌ Failed

Notes:
- Everything working correctly
```

---

## ✅ Post-Update Checklist

After every update:

- [ ] All files uploaded successfully
- [ ] File permissions set correctly
- [ ] Dependencies updated (if needed)
- [ ] Migrations run (if needed)
- [ ] All caches cleared
- [ ] All caches rebuilt
- [ ] Homepage loads without errors
- [ ] Login works
- [ ] Dashboard works
- [ ] No errors in logs
- [ ] No JavaScript errors in console
- [ ] All features tested
- [ ] Site brought back online (if in maintenance mode)
- [ ] Update logged

---

## 🎯 Best Practices

1. **Always Backup First**
   - Database and `.env` at minimum
   - Full backup for major updates

2. **Test Locally First**
   - Never deploy untested code
   - Use staging environment if possible

3. **Update During Low Traffic**
   - Schedule updates during off-peak hours
   - Notify users if maintenance needed

4. **Clear Caches After Every Update**
   - This is the #1 cause of update crashes
   - Make it a habit

5. **Update Incrementally**
   - Don't update everything at once
   - Update related files together
   - Test after each group

6. **Keep Update Logs**
   - Track what was changed
   - Helps with troubleshooting
   - Useful for rollback

7. **Monitor After Update**
   - Check logs for 24-48 hours
   - Monitor error rates
   - Watch for user reports

---

## 🆘 Emergency Rollback

If update causes critical issues:

### Quick Rollback Steps:

1. **Restore Files:**
   - Revert changed files from backup
   - Or restore from Git (if using version control)

2. **Restore Database:**
   ```bash
   mysql -u user -p database < backup.sql
   ```

3. **Restore .env:**
   ```bash
   cp .env.backup .env
   ```

4. **Clear Caches:**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Verify:**
   - Test homepage
   - Test login
   - Check logs

---

**Remember:** When in doubt, backup first, update carefully, clear caches, and test thoroughly!

---

**Update Guide Version:** 1.0
**Last Updated:** 2024


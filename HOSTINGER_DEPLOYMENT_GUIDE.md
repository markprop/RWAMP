# 🚀 Complete Hostinger Deployment Guide for RWAMP Laravel

This guide will help you deploy your RWAMP Laravel application to Hostinger shared hosting without any crashes or errors.

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ Hostinger shared hosting account
- ✅ Access to cPanel or File Manager
- ✅ Access to MySQL database (via phpMyAdmin or cPanel)
- ✅ SSH access (optional but recommended)
- ✅ Domain name configured

---

## 🔧 Step 1: Prepare Your Local Project

### 1.1 Build Assets for Production

```bash
# Install Node.js dependencies (if not already done)
npm install

# Build production assets
npm run build
```

This will create optimized assets in `public/build/` directory.

### 1.2 Optimize Laravel for Production

```bash
# Clear all caches
php artisan optimize:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.3 Prepare Environment File

Create a `.env` file for production (DO NOT upload your local `.env` file):

```env
APP_NAME=RWAMP
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your_email@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Add your crypto configuration
CRYPTO_PAYMENTS_ENABLED=true
WALLETCONNECT_ENABLED=true
# ... (add other crypto settings)
```

**⚠️ IMPORTANT:** Generate a new `APP_KEY` for production:
```bash
php artisan key:generate
```
Copy the generated key to your production `.env` file.

---

## 📤 Step 2: Upload Files to Hostinger

### 2.1 File Structure on Hostinger

On Hostinger shared hosting, you have two options:

#### Option A: Standard Laravel Structure (Recommended)
```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← This is your document root
│   ├── index.php
│   ├── .htaccess
│   └── build/       ← Vite build assets
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
├── composer.json
└── composer.lock
```

**Document Root:** Point your domain to `public_html/public/` directory

#### Option B: Modified Structure (If you can't change document root)
```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
├── composer.json
├── composer.lock
├── index.php        ← Move public/index.php here
├── .htaccess        ← Move public/.htaccess here
└── build/           ← Move public/build/ here
```

**Document Root:** Point your domain to `public_html/` directory

### 2.2 Upload Files

1. **Via FTP/SFTP:**
   - Use FileZilla or similar FTP client
   - Upload all files except:
     - `node_modules/` (don't upload)
     - `.git/` (don't upload)
     - `.env` (create new one on server)
     - `storage/logs/*.log` (let Laravel create these)

2. **Via cPanel File Manager:**
   - Upload as ZIP file
   - Extract on server
   - Delete ZIP file after extraction

### 2.3 Set File Permissions

After uploading, set correct permissions via SSH or cPanel:

```bash
# Navigate to your project root
cd ~/public_html

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make storage and cache writable
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# If www-data doesn't work, try your username
chown -R your_username:your_username storage bootstrap/cache
```

**Via cPanel File Manager:**
- Right-click `storage/` → Change Permissions → 775
- Right-click `bootstrap/cache/` → Change Permissions → 775
- Right-click all files in `storage/` → Change Permissions → 644

---

## 🗄️ Step 3: Database Setup

### 3.1 Create Database

1. Log in to cPanel
2. Go to **MySQL Databases**
3. Create a new database (e.g., `rwamp_production`)
4. Create a new database user
5. Add user to database with **ALL PRIVILEGES**
6. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### 3.2 Import Database Schema

**Option A: Via phpMyAdmin**
1. Go to phpMyAdmin in cPanel
2. Select your database
3. Click **Import** tab
4. Upload your database SQL file or run migrations

**Option B: Via SSH (Recommended)**
```bash
# Connect via SSH
ssh your_username@your_domain.com

# Navigate to project
cd ~/public_html

# Run migrations
php artisan migrate --force
```

**Option C: Via Artisan (if you have SSH)**
```bash
php artisan migrate --force
```

### 3.3 Update .env with Database Credentials

Update your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

---

## ⚙️ Step 4: Configure Environment

### 4.1 Create .env File on Server

1. Via cPanel File Manager:
   - Create new file named `.env`
   - Copy content from your prepared `.env` file
   - Save

2. Via SSH:
   ```bash
   nano .env
   # Paste your .env content
   # Save: Ctrl+X, then Y, then Enter
   ```

### 4.2 Verify .env Configuration

Ensure these are set correctly:
- ✅ `APP_ENV=production`
- ✅ `APP_DEBUG=false`
- ✅ `APP_KEY` is set (generate if not: `php artisan key:generate`)
- ✅ `APP_URL` matches your domain (with https://)
- ✅ Database credentials are correct
- ✅ Mail settings are configured

---

## 🔗 Step 5: Create Storage Symlink

Laravel needs a symbolic link from `public/storage` to `storage/app/public`:

**Via SSH:**
```bash
php artisan storage:link
```

**Via cPanel (if SSH not available):**
1. Go to File Manager
2. Navigate to `public/` directory
3. Create a symbolic link:
   - Link name: `storage`
   - Target: `../storage/app/public`

**Manual Method (if symlinks don't work):**
1. Copy `storage/app/public/` contents to `public/storage/`
2. Keep both directories in sync (not recommended for production)

---

## 📦 Step 6: Install Dependencies

### 6.1 Install Composer Dependencies

**Via SSH:**
```bash
cd ~/public_html
composer install --no-dev --optimize-autoloader
```

**Via cPanel Terminal:**
- Open Terminal in cPanel
- Run the same command

**If Composer is not available:**
- Upload the `vendor/` folder from your local project
- Ensure all dependencies are included

### 6.2 Verify Assets

Ensure `public/build/` directory exists with:
- `build/assets/app-*.js`
- `build/assets/app-*.css`
- `build/manifest.json`

If missing, upload from your local `public/build/` directory.

---

## 🧹 Step 7: Clear and Optimize Caches

**Via SSH:**
```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

**If you don't have SSH access:**
- These commands will run automatically on first request
- But it's better to run them manually

---

## ✅ Step 8: Verify Deployment

### 8.1 Test Basic Functionality

1. **Homepage:**
   - Visit: `https://yourdomain.com`
   - Should load without errors

2. **Login:**
   - Visit: `https://yourdomain.com/login`
   - Should display login form

3. **Dashboard (after login):**
   - Visit: `https://yourdomain.com/dashboard/admin`
   - Should load admin dashboard

### 8.2 Check for Errors

**Check Laravel Logs:**
```bash
tail -f storage/logs/laravel.log
```

**Common Issues:**
- 500 Error → Check logs, permissions, .env
- White Screen → Check APP_DEBUG, logs, PHP errors
- Assets not loading → Check build directory, Vite manifest
- Database errors → Check DB credentials, connection

### 8.3 Verify File Permissions

```bash
# Check storage permissions
ls -la storage/
# Should show: drwxrwxr-x

# Check bootstrap/cache permissions
ls -la bootstrap/cache/
# Should show: drwxrwxr-x
```

---

## 🔒 Step 9: Security Hardening

### 9.1 Secure .env File

```bash
# Set .env permissions to 600 (owner read/write only)
chmod 600 .env
```

### 9.2 Update .htaccess in public/

Ensure `public/.htaccess` exists and has:
```apache
<IfModule mod_rewrite.c>
    Options -Indexes
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
    RewriteRule ^ index.php [L]
</IfModule>
```

### 9.3 Disable Directory Listing

Add to `.htaccess` in project root:
```apache
Options -Indexes
```

---

## 🚨 Step 10: Troubleshooting Common Issues

### Issue 1: 500 Internal Server Error

**Solution:**
1. Check `storage/logs/laravel.log`
2. Verify file permissions
3. Check `.env` file exists and is configured
4. Verify `APP_KEY` is set
5. Check database connection

### Issue 2: Dashboard Not Loading

**Solution:**
1. Check if 2FA is enabled (admin only)
2. Visit `/admin/2fa/setup` to enable 2FA
3. Check logs for specific errors
4. Verify user has `admin` role in database

### Issue 3: Assets Not Loading (CSS/JS)

**Solution:**
1. Verify `public/build/` directory exists
2. Check `public/build/manifest.json` exists
3. Run `npm run build` locally and upload `public/build/`
4. Clear browser cache
5. Check Vite configuration

### Issue 4: Storage Files Not Accessible

**Solution:**
1. Create storage symlink: `php artisan storage:link`
2. Check `public/storage` exists
3. Verify symlink target: `ls -la public/storage`
4. Check file permissions

### Issue 5: Database Connection Error

**Solution:**
1. Verify database credentials in `.env`
2. Check database exists in cPanel
3. Verify user has proper permissions
4. Test connection: `php artisan tinker` → `DB::connection()->getPdo();`

### Issue 6: Permission Denied Errors

**Solution:**
```bash
# Fix storage permissions
chmod -R 775 storage
chown -R your_username:your_username storage

# Fix bootstrap/cache permissions
chmod -R 775 bootstrap/cache
chown -R your_username:your_username bootstrap/cache
```

### Issue 7: APP_KEY Error

**Solution:**
```bash
# Generate new APP_KEY
php artisan key:generate

# Or manually set in .env
APP_KEY=base64:your_generated_key_here
```

---

## 📝 Step 11: Post-Deployment Checklist

- [ ] All files uploaded successfully
- [ ] `.env` file created and configured
- [ ] Database created and migrations run
- [ ] File permissions set correctly (775 for storage, 644 for files)
- [ ] Storage symlink created
- [ ] Composer dependencies installed
- [ ] Assets built and uploaded (`public/build/`)
- [ ] Caches cleared and optimized
- [ ] `APP_KEY` generated
- [ ] `APP_DEBUG=false` in production
- [ ] Database credentials correct
- [ ] Mail settings configured
- [ ] Homepage loads without errors
- [ ] Login works
- [ ] Dashboard loads (after 2FA setup)
- [ ] File uploads work (test KYC or payment screenshot)
- [ ] Email sending works (test password reset)

---

## 🔄 Step 12: Updating Files (Without Crashing)

### Safe Update Process

1. **Backup First:**
   ```bash
   # Backup database
   php artisan db:backup  # If you have backup package
   # Or export via phpMyAdmin
   
   # Backup .env file
   cp .env .env.backup
   ```

2. **Upload New Files:**
   - Upload only changed files
   - Don't overwrite `.env` unless necessary
   - Don't delete `storage/` directory

3. **After Upload:**
   ```bash
   # Clear caches
   php artisan optimize:clear
   
   # Rebuild caches
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   
   # If you updated composer.json
   composer install --no-dev --optimize-autoloader
   
   # If you updated assets
   npm run build
   # Then upload public/build/
   ```

4. **Verify:**
   - Test homepage
   - Test login
   - Test dashboard
   - Check logs for errors

### ⚠️ NEVER Do These During Updates:

- ❌ Don't delete `storage/` directory
- ❌ Don't delete `vendor/` directory
- ❌ Don't overwrite `.env` without backup
- ❌ Don't delete `public/build/` without rebuilding
- ❌ Don't change `APP_KEY` after 2FA is enabled
- ❌ Don't run `php artisan migrate:fresh` (deletes all data)

---

## 🆘 Emergency Recovery

If your site crashes after an update:

1. **Restore from Backup:**
   - Restore `.env` from backup
   - Restore database from backup
   - Restore files from backup

2. **Quick Fix:**
   ```bash
   # Clear all caches
   php artisan optimize:clear
   
   # Rebuild caches
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Check Logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

4. **Enable Debug Temporarily:**
   - In `.env`: `APP_DEBUG=true`
   - Check error message
   - Fix the issue
   - Set back to `APP_DEBUG=false`

---

## 📞 Support

If you encounter issues:
1. Check `storage/logs/laravel.log`
2. Check cPanel error logs
3. Verify all steps in this guide
4. Check file permissions
5. Verify database connection

---

## 🎉 Success!

Your RWAMP Laravel application should now be deployed and running on Hostinger!

**Remember:**
- Always backup before updates
- Test changes in a staging environment first
- Keep `APP_DEBUG=false` in production
- Monitor `storage/logs/laravel.log` regularly
- Keep dependencies updated

---

**Last Updated:** 2024
**Version:** Laravel 10+
**PHP Version:** 8.1+


# Hostinger Deployment - Quick Fix Guide

## Issues Found:
1. ❌ `bootstrap/cache` directory not writable
2. ❌ `npm` command not found (Node.js not installed)
3. ⚠️ Currently in `live.rwamp.net` directory (verify if this is correct)

---

## Step-by-Step Fix:

### 1. Fix Bootstrap Cache Directory

```bash
# Create bootstrap/cache directory if it doesn't exist
mkdir -p bootstrap/cache

# Set proper permissions
chmod -R 775 bootstrap/cache
chmod -R 775 storage
chmod -R 775 storage/framework
chmod -R 775 storage/logs

# Set ownership (adjust user/group as needed)
chown -R u945985759:u945985759 bootstrap/cache
chown -R u945985759:u945985759 storage
```

### 2. Verify You're in the Correct Directory

```bash
# Check current directory
pwd

# If you need to switch to dev.rwamp.net:
cd /home/u945985759/domains/rwamp.net/public_html/dev.rwamp.net

# Or if live.rwamp.net is correct, stay there
```

### 3. Handle NPM/Node.js (Two Options)

#### Option A: Build Assets Locally and Upload (Recommended)

Since Node.js is not installed on Hostinger, build assets on your local machine:

**On Local Machine:**
```bash
cd D:\RWAMP\rwamp-laravel
npm run build
```

Then upload the `public/build` directory to the server:
```bash
# Use SFTP or FileZilla to upload:
# - public/build/ directory
# - public/build/manifest.json
```

#### Option B: Install Node.js on Server (If you have access)

```bash
# Check if node is available via nvm or other method
which node
which npm

# If not available, you may need to request Node.js installation from Hostinger
# or use a Node.js version manager
```

### 4. Complete Deployment Steps

```bash
# 1. Fix permissions (run this first)
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache storage bootstrap/cache
chown -R u945985759:u945985759 bootstrap/cache storage

# 2. Pull latest code (if using git)
git pull origin main

# 3. Install Composer dependencies
composer install --optimize-autoloader --no-dev

# 4. Run migrations
php artisan migrate --force

# 5. Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Verify deployment
php artisan migrate:status
```

### 5. If Assets Need to be Built Locally

**On Your Local Machine (Windows):**
```powershell
cd D:\RWAMP\rwamp-laravel
npm run build
```

**Then upload via SFTP/FileZilla:**
- Upload entire `public/build` folder to server's `public/build`
- Ensure `public/build/manifest.json` is uploaded

---

## Quick Fix Commands (Run These Now)

```bash
# Fix permissions
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache storage
chown -R u945985759:u945985759 bootstrap/cache storage

# Try composer install again
composer install --optimize-autoloader --no-dev

# If successful, continue with migrations
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

---

## Verify Deployment

```bash
# Check migrations
php artisan migrate:status

# Check if system_settings table exists
php artisan tinker
>>> DB::table('system_settings')->count()
>>> exit

# Check logs for errors
tail -f storage/logs/laravel.log
```

---

## Important Notes

1. **Assets**: Since npm is not available, you MUST build assets locally and upload them
2. **Permissions**: Always ensure `bootstrap/cache` and `storage` are writable
3. **Directory**: Verify you're deploying to the correct directory (`dev.rwamp.net` or `live.rwamp.net`)


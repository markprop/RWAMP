# Hostinger Deployment Update Guide

This guide will help you update your already-deployed RWAMP Laravel project on Hostinger with the latest changes from GitHub.

## Prerequisites

1. **SSH Access to Hostinger**
   - Access your Hostinger hPanel
   - Go to **Advanced** → **SSH Access**
   - Enable SSH if not already enabled
   - Note your SSH credentials (host, username, port)

2. **Git Access**
   - Ensure your Hostinger server has Git installed
   - Your repository should be cloned on the server

## Step-by-Step Deployment Process

### Step 1: Connect to Hostinger via SSH

**Using Windows (PowerShell):**
```powershell
ssh username@your-hostinger-ip -p 65002
# Or if using domain:
ssh username@your-domain.com -p 65002
```

**Using PuTTY (Windows):**
- Host: `your-hostinger-ip` or `your-domain.com`
- Port: `65002` (default Hostinger SSH port)
- Username: Your Hostinger username
- Password: Your Hostinger password

### Step 2: Navigate to Your Project Directory

```bash
cd public_html
# Or if your project is in a subdirectory:
cd public_html/rwamp-laravel
# Or wherever your Laravel project is located
```

### Step 3: Check Current Status

```bash
# Check current branch
git branch

# Check for uncommitted changes
git status

# Check current commit
git log -1
```

### Step 4: Pull Latest Changes from GitHub

```bash
# Switch to main branch (if not already)
git checkout main

# Pull latest changes
git pull origin main
```

**If you get merge conflicts:**
```bash
# Stash local changes (if any)
git stash

# Pull again
git pull origin main

# Apply stashed changes (if needed)
git stash pop
```

### Step 5: Install/Update Dependencies

```bash
# Update Composer dependencies
composer install --no-dev --optimize-autoloader

# Update NPM dependencies (if needed)
npm install --production

# Build assets (if you have frontend build process)
npm run build
# Or if using Vite:
npm run build
```

### Step 6: Run Database Migrations

```bash
# Run new migrations
php artisan migrate --force

# Note: The --force flag is required in production
```

**Important:** Check which migrations are new:
```bash
# See pending migrations
php artisan migrate:status
```

### Step 7: Clear All Caches

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 8: Update Environment Variables

Edit your `.env` file to ensure all new variables are set:

```bash
nano .env
# Or use your preferred editor
```

**Required Environment Variables for New Features:**

```env
# Tawk.to Live Chat Widget Configuration
TAWK_ENABLED=true
TAWK_PROPERTY_ID=691ec32b545b891960a7807b
TAWK_WIDGET_ID=1jag2kp6s

# Broadcasting (if using Pusher for chat)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap2
```

After updating `.env`:
```bash
php artisan config:clear
php artisan config:cache
```

### Step 9: Set Proper Permissions

```bash
# Set storage and cache permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# If using different user, replace www-data with your user
```

### Step 10: Verify Deployment

```bash
# Check Laravel version
php artisan --version

# Check if there are any errors
php artisan about

# Test database connection
php artisan tinker
# Then in tinker: DB::connection()->getPdo();
# Exit with: exit
```

### Step 11: Test the Application

1. **Visit your website** and check:
   - Homepage loads correctly
   - Login/Signup works
   - Dashboard displays correctly
   - Portfolio values show correctly
   - Tawk.to chat widget appears (for logged-in users)

2. **Check browser console** for any errors

3. **Test key features:**
   - User login
   - Dashboard access
   - Portfolio calculations
   - Buy from reseller functionality
   - Tawk.to chat (if logged in)

## Troubleshooting

### Issue: Git pull fails with "permission denied"

**Solution:**
```bash
# Check file ownership
ls -la

# Fix ownership if needed
chown -R username:username .
```

### Issue: Composer install fails

**Solution:**
```bash
# Update Composer
composer self-update

# Clear Composer cache
composer clear-cache

# Try again
composer install --no-dev --optimize-autoloader
```

### Issue: Migration fails

**Solution:**
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration if needed
php artisan migrate:rollback --step=1

# Try migration again
php artisan migrate --force
```

### Issue: 500 Error after deployment

**Solution:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan optimize:clear

# Re-optimize
php artisan optimize
```

### Issue: Tawk.to widget not showing

**Solution:**
1. Verify `.env` has correct tawk.to credentials
2. Clear config cache: `php artisan config:clear && php artisan config:cache`
3. Check browser console for CSP errors
4. Verify user is logged in (widget only shows for authenticated users)

## Quick Deployment Script

You can create a deployment script for faster updates:

```bash
#!/bin/bash
# deploy.sh

echo "Starting deployment..."

# Navigate to project
cd /path/to/your/project

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan optimize:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deployment complete!"
```

**Make it executable:**
```bash
chmod +x deploy.sh
```

**Run it:**
```bash
./deploy.sh
```

## Post-Deployment Checklist

- [ ] All migrations ran successfully
- [ ] Application loads without errors
- [ ] User login works
- [ ] Dashboard displays correctly
- [ ] Portfolio values calculate correctly
- [ ] Buy from reseller works
- [ ] Tawk.to chat widget appears (for logged-in users)
- [ ] No console errors in browser
- [ ] All new features work as expected
- [ ] Database is updated correctly

## Rollback Plan (If Something Goes Wrong)

If you need to rollback:

```bash
# Check git log for previous commit
git log --oneline -10

# Rollback to previous commit
git reset --hard <previous-commit-hash>

# Clear caches
php artisan optimize:clear

# Re-optimize
php artisan optimize
```

## Important Notes

1. **Always backup your database** before running migrations
2. **Test in staging first** if you have a staging environment
3. **Monitor logs** after deployment: `tail -f storage/logs/laravel.log`
4. **Keep `.env` file secure** - never commit it to Git
5. **Check file permissions** after deployment

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Check browser console for frontend errors
3. Verify all environment variables are set correctly
4. Ensure all migrations ran successfully

---

**Last Updated:** 2025-11-20
**Version:** 1.0


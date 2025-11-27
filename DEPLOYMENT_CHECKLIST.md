# Deployment Checklist - RWAMP Project

**Date:** 2025-11-27  
**Status:** ✅ Ready for Deployment  
**Git Commits:** 4 commits pushed to `main` branch

---

## 📋 Pre-Deployment Summary

### Commits Pushed:
1. **feat: Add database persistence for official coin price**
   - System settings table for persistent price storage
   - PriceHelper updates for database fallback
   - Admin price controller improvements

2. **feat: Display reseller name when referral code is entered**
   - Fixed referral code API endpoint
   - Reseller name display in signup form
   - Improved user feedback

3. **feat: Calculate portfolio value using weighted average purchase price**
   - Weighted average calculation from all purchases
   - Portfolio Value vs Official Portfolio Value distinction
   - Support for all purchase types

4. **fix: Resolve route conflicts and improve authentication**
   - Fixed duplicate route names
   - Added missing routes (admin.sell-coins, dashboard.reseller, admin.users.details)
   - Fixed 419 Page Expired error
   - 2FA columns migration

---

## 🚀 Hostinger Deployment Steps

### 1. **SSH into Hostinger Server**

```bash
ssh your_username@dev.rwamp.net
# or use Hostinger's SSH access from cPanel
```

### 2. **Navigate to Project Directory**

```bash
cd /home/u945985759/domains/dev.rwamp.net/public_html
# Adjust path based on your Hostinger setup
```

### 3. **Backup Current Live Site** ⚠️ **IMPORTANT**

```bash
# Backup database
mysqldump -u u945985759_markprop -p rwamp_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files (optional but recommended)
tar -czf files_backup_$(date +%Y%m%d_%H%M%S).tar.gz .
```

### 4. **Pull Latest Changes from GitHub**

```bash
# Ensure you're on main branch
git checkout main

# Pull latest changes
git pull origin main
```

### 5. **Install/Update Dependencies**

```bash
# Install Composer dependencies (production mode)
composer install --optimize-autoloader --no-dev

# Install NPM dependencies and build assets
npm install
npm run build
```

### 6. **Run Database Migrations**

```bash
# Run new migrations
php artisan migrate --force

# Verify migrations
php artisan migrate:status
```

**Expected New Migrations:**
- `2025_11_27_111734_add_two_factor_columns_to_users_table_if_missing`
- `2025_11_27_124710_create_system_settings_table`

### 7. **Clear All Caches**

```bash
# Clear all Laravel caches
php artisan optimize:clear

# Rebuild optimized files
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. **Set Permissions** (if needed)

```bash
# Set storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. **Verify Environment Variables**

Ensure `.env` file has correct values:
- `APP_ENV=production`
- `APP_DEBUG=false`
- Database credentials
- Mail settings
- Cache driver (recommended: `file` or `redis`)

### 10. **Test Critical Functionality**

After deployment, test:
- ✅ Admin login and dashboard
- ✅ Price management (update coin price)
- ✅ User registration with referral code (reseller name display)
- ✅ Investor/Reseller dashboard (portfolio value calculation)
- ✅ Purchase flow
- ✅ All routes working

---

## 🔍 Post-Deployment Verification

### Check Logs

```bash
# Check Laravel logs for errors
tail -f storage/logs/laravel.log

# Check web server error logs
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

### Verify Database

```bash
# Check system_settings table exists
php artisan tinker
>>> DB::table('system_settings')->count()
```

### Test Price System

1. Login as admin
2. Go to `/dashboard/admin/prices`
3. Update RWAMP price
4. Verify price appears on homepage and purchase pages

### Test Referral Code

1. Go to `/register?ref=RSL48` (or any valid reseller code)
2. Verify reseller name displays when code is entered
3. Complete registration

### Test Portfolio Value

1. Login as investor/reseller
2. Check dashboard portfolio cards
3. Verify:
   - Portfolio Value = Token Balance × Average Purchase Price
   - Official Portfolio Value = Token Balance × Current Official Price

---

## 🐛 Troubleshooting

### If migrations fail:
```bash
# Check migration status
php artisan migrate:status

# Rollback if needed (be careful!)
php artisan migrate:rollback --step=1
```

### If routes don't work:
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache
```

### If prices don't update:
```bash
# Clear all caches
php artisan optimize:clear

# Check database
php artisan tinker
>>> \App\Helpers\PriceHelper::getRwampPkrPrice()
```

### If assets don't load:
```bash
# Rebuild assets
npm run build

# Clear view cache
php artisan view:clear
```

---

## 📝 Important Notes

1. **Database Backup**: Always backup before deploying
2. **Maintenance Mode**: Consider using `php artisan down` during deployment
3. **Queue Workers**: If using queues, restart workers after deployment
4. **Cache Driver**: Ensure cache driver is set correctly in `.env`
5. **File Permissions**: Verify storage and cache directories are writable

---

## ✅ Deployment Complete Checklist

- [ ] Code pulled from GitHub
- [ ] Dependencies installed
- [ ] Migrations run successfully
- [ ] Caches cleared and rebuilt
- [ ] Permissions set correctly
- [ ] Environment variables verified
- [ ] Admin dashboard accessible
- [ ] Price management working
- [ ] Referral code display working
- [ ] Portfolio value calculation correct
- [ ] No errors in logs
- [ ] All routes functional

---

## 📞 Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server logs
3. Verify database connection
4. Check file permissions
5. Review `.env` configuration

---

**Last Updated:** 2025-11-27  
**Deployment Status:** Ready ✅

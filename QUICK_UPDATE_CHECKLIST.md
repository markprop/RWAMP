# Quick Update Checklist - Manual File Update

Use this checklist when manually updating files on Hostinger.

## Pre-Update Steps

- [ ] **Backup current files** to `backup_2025_11_20/` folder
- [ ] **Download `.env` file** as backup
- [ ] **Note current file locations** on server

## Files to Update (In Order)

### 1. Configuration Files
- [ ] `config/services.php` - Add tawk.to config section
- [ ] `.env` - Add tawk.to variables:
  ```
  TAWK_ENABLED=true
  TAWK_PROPERTY_ID=691ec32b545b891960a7807b
  TAWK_WIDGET_ID=1jag2kp6s
  ```

### 2. Middleware
- [ ] `app/Http/Middleware/SecurityHeaders.php`

### 3. Controllers
- [ ] `app/Http/Controllers/CryptoPaymentController.php`
- [ ] `app/Http/Controllers/ResellerController.php`
- [ ] `app/Http/Controllers/ProfileController.php`

### 4. Models
- [ ] `app/Models/User.php`

### 5. View Components
- [ ] `resources/views/components/tawk-to.blade.php` (NEW FILE)
- [ ] `resources/views/components/buy-from-reseller-modal.blade.php`
- [ ] `resources/views/layouts/app.blade.php` (verify tawk.to include)

### 6. Dashboard Views
- [ ] `resources/views/dashboard/investor.blade.php`
- [ ] `resources/views/dashboard/reseller.blade.php`
- [ ] `resources/views/dashboard/reseller-sell.blade.php`
- [ ] `resources/views/dashboard/user-history.blade.php`

### 7. Auth Views
- [ ] `resources/views/auth/profile.blade.php`

## Post-Update Steps

- [ ] **Set file permissions**: Files = 644, Folders = 755
- [ ] **Set storage permissions**: `storage/` = 775, `bootstrap/cache/` = 775
- [ ] **Clear caches** (delete cache folders or use SSH)
- [ ] **Run migrations** (via SSH or contact support)
- [ ] **Test website** - Homepage loads
- [ ] **Test login** - Works correctly
- [ ] **Test dashboard** - Portfolio values show
- [ ] **Test tawk.to** - Widget appears when logged in
- [ ] **Check browser console** - No critical errors

## Quick Commands (If SSH Available)

```bash
# Navigate to project
cd public_html

# Clear caches
php artisan optimize:clear

# Run migrations
php artisan migrate --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 775 storage bootstrap/cache
```

## Emergency Rollback

If something breaks:
1. Restore files from `backup_2025_11_20/`
2. Restore `.env` from backup
3. Clear caches: `php artisan optimize:clear`

---

**Keep this checklist handy during update!**


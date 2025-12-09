# 📚 RWAMP Laravel - Complete Deployment Documentation

This document provides an overview of all deployment-related documentation for the RWAMP Laravel project.

## 📖 Documentation Index

### 1. **HOSTINGER_DEPLOYMENT_GUIDE.md** ⭐ START HERE
   - Complete step-by-step deployment guide
   - File structure setup
   - Database configuration
   - Environment setup
   - Security hardening
   - Troubleshooting common issues
   - **Use this for initial deployment**

### 2. **DEPLOYMENT_CHECKLIST.md**
   - Pre-deployment checklist
   - File upload checklist
   - Configuration checklist
   - Testing checklist
   - Post-deployment checklist
   - **Use this to ensure nothing is missed**

### 3. **POST_DEPLOYMENT_VERIFICATION.md**
   - Quick verification steps
   - Detailed verification procedures
   - Feature-specific checks
   - Performance checks
   - Security checks
   - **Use this after deployment to verify everything works**

### 4. **SAFE_UPDATE_GUIDE.md** ⭐ FOR UPDATES
   - How to safely update files without crashing
   - Pre-update checklist
   - Step-by-step update process
   - Common update scenarios
   - Emergency rollback procedures
   - **Use this every time you update files**

### 5. **DASHBOARD_FIX_GUIDE.md**
   - Dashboard 500 error fixes
   - Recovery codes error handling
   - 2FA setup issues
   - **Use this if dashboard crashes**

### 6. **FIX_CORRUPTED_RECOVERY_CODES.md**
   - How to fix corrupted 2FA recovery codes
   - Prevention tips
   - **Use this if 2FA causes issues**

---

## 🚀 Quick Start Guide

### First Time Deployment

1. **Read:** `HOSTINGER_DEPLOYMENT_GUIDE.md`
2. **Follow:** `DEPLOYMENT_CHECKLIST.md`
3. **Verify:** `POST_DEPLOYMENT_VERIFICATION.md`

### Updating Files

1. **Read:** `SAFE_UPDATE_GUIDE.md`
2. **Follow:** Pre-update checklist
3. **Execute:** Update process
4. **Verify:** Post-update checklist

### If Something Breaks

1. **Check:** `storage/logs/laravel.log`
2. **Review:** `SAFE_UPDATE_GUIDE.md` → Emergency Rollback
3. **Fix:** `DASHBOARD_FIX_GUIDE.md` (if dashboard issue)
4. **Recover:** Restore from backup

---

## 🔑 Key Points to Remember

### ⚠️ Critical Rules

1. **Always Backup Before Updates**
   - Database backup
   - `.env` file backup
   - Critical files backup

2. **Always Clear Caches After Updates**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Never Delete These Directories:**
   - `storage/` (contains user data)
   - `vendor/` (contains dependencies)
   - `public/build/` (contains assets)

4. **Never Change APP_KEY After 2FA Enabled**
   - Will corrupt recovery codes
   - Dashboard will crash

5. **Always Set Correct Permissions:**
   - Directories: 755
   - Files: 644
   - Storage: 775
   - `.env`: 600

---

## 📋 Common Commands Reference

### Deployment Commands

```bash
# Build assets
npm run build

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link

# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate APP_KEY
php artisan key:generate

# Check migration status
php artisan migrate:status
```

### Update Commands

```bash
# Clear caches (do this after EVERY update)
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check logs
tail -50 storage/logs/laravel.log

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

### Emergency Commands

```bash
# Put site in maintenance mode
php artisan down

# Bring site back online
php artisan up

# Clear all caches (emergency fix)
php artisan optimize:clear

# Check database connection
php artisan tinker
DB::connection()->getPdo();
```

---

## 🗂️ File Structure on Hostinger

### Standard Structure (Recommended)

```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/              ← Document root points here
│   ├── index.php
│   ├── .htaccess
│   ├── build/          ← Vite assets
│   └── storage/        ← Symlink to storage/app/public
├── resources/
├── routes/
├── storage/            ← Must be writable (775)
│   ├── app/
│   ├── framework/
│   └── logs/
├── vendor/
├── .env                ← Must be secure (600)
├── artisan
├── composer.json
└── composer.lock
```

---

## 🔍 Troubleshooting Quick Reference

### Issue: 500 Internal Server Error

**Quick Fix:**
```bash
php artisan optimize:clear
chmod -R 775 storage bootstrap/cache
tail -50 storage/logs/laravel.log
```

### Issue: Dashboard Not Loading

**Quick Fix:**
1. Check if 2FA is enabled: Visit `/admin/2fa/setup`
2. Check user role: `php artisan tinker` → `User::find(1)->role;`
3. Check logs: `tail -50 storage/logs/laravel.log`

### Issue: Assets Not Loading

**Quick Fix:**
1. Verify `public/build/` exists
2. Rebuild locally: `npm run build`
3. Upload `public/build/` directory
4. Clear browser cache

### Issue: Storage Files Not Accessible

**Quick Fix:**
```bash
php artisan storage:link
chmod -R 775 storage
```

### Issue: Database Connection Error

**Quick Fix:**
1. Check `.env` database credentials
2. Test connection: `php artisan tinker` → `DB::connection()->getPdo();`
3. Verify database exists in cPanel

---

## 📞 Support Resources

### Documentation Files

- `HOSTINGER_DEPLOYMENT_GUIDE.md` - Full deployment guide
- `DEPLOYMENT_CHECKLIST.md` - Deployment checklist
- `POST_DEPLOYMENT_VERIFICATION.md` - Verification steps
- `SAFE_UPDATE_GUIDE.md` - Safe update procedures
- `DASHBOARD_FIX_GUIDE.md` - Dashboard troubleshooting
- `FIX_CORRUPTED_RECOVERY_CODES.md` - 2FA recovery codes fix

### Log Files

- `storage/logs/laravel.log` - Laravel application logs
- cPanel Error Logs - Server-level errors

### Scripts

- `deploy-to-hostinger.sh` - Deployment automation script

---

## ✅ Deployment Success Criteria

Your deployment is successful when:

- ✅ Homepage loads without errors
- ✅ Login/Registration works
- ✅ Dashboard loads (after 2FA setup)
- ✅ File uploads work
- ✅ Forms submit correctly
- ✅ No errors in `storage/logs/laravel.log`
- ✅ Assets (CSS/JS) load correctly
- ✅ Database queries work
- ✅ Email sending works
- ✅ All features tested and working

---

## 🎯 Best Practices Summary

1. **Always backup before updates**
2. **Test locally before deploying**
3. **Clear caches after every update**
4. **Set correct file permissions**
5. **Monitor logs after deployment**
6. **Update during low-traffic periods**
7. **Keep update logs**
8. **Have rollback plan ready**

---

## 📝 Version History

- **v1.0** (2024) - Initial deployment documentation
  - Complete Hostinger deployment guide
  - Safe update procedures
  - Troubleshooting guides
  - Verification checklists

---

## 🆘 Emergency Contacts

- **Hostinger Support:** [Your support link]
- **Database Admin:** [Contact]
- **Server Admin:** [Contact]
- **Developer:** [Contact]

---

**Remember:** When in doubt, refer to the detailed guides. Each guide covers specific scenarios in depth.

**Last Updated:** 2024


# ✅ RWAMP Laravel Deployment Checklist

Use this checklist to ensure a successful deployment to Hostinger.

## 📦 Pre-Deployment

- [ ] **Local Build Complete**
  - [ ] `npm run build` executed successfully
  - [ ] `public/build/` directory contains assets
  - [ ] `public/build/manifest.json` exists

- [ ] **Environment Prepared**
  - [ ] Production `.env` file prepared
  - [ ] `APP_ENV=production` set
  - [ ] `APP_DEBUG=false` set
  - [ ] `APP_KEY` generated
  - [ ] Database credentials ready
  - [ ] Mail settings configured
  - [ ] Crypto settings configured

- [ ] **Dependencies Ready**
  - [ ] `composer.json` and `composer.lock` ready
  - [ ] `package.json` and `package-lock.json` ready
  - [ ] All vendor dependencies tested locally

- [ ] **Database Ready**
  - [ ] Database schema exported or migrations ready
  - [ ] Seed data prepared (if needed)

---

## 📤 File Upload

- [ ] **Files Uploaded**
  - [ ] All project files uploaded to server
  - [ ] `node_modules/` excluded (not uploaded)
  - [ ] `.git/` excluded (not uploaded)
  - [ ] Local `.env` excluded (create new on server)
  - [ ] `storage/logs/*.log` excluded

- [ ] **Directory Structure**
  - [ ] Document root points to `public/` directory
  - [ ] OR files restructured for `public_html/` root
  - [ ] All directories in correct locations

---

## 🔐 Configuration

- [ ] **Environment File**
  - [ ] `.env` file created on server
  - [ ] All required variables set
  - [ ] `APP_KEY` set correctly
  - [ ] `APP_URL` matches domain (with https://)
  - [ ] Database credentials correct
  - [ ] Mail settings configured

- [ ] **File Permissions**
  - [ ] Directories: 755
  - [ ] Files: 644
  - [ ] `storage/`: 775 (writable)
  - [ ] `bootstrap/cache/`: 775 (writable)
  - [ ] `.env`: 600 (secure)

---

## 🗄️ Database

- [ ] **Database Created**
  - [ ] Database created in cPanel
  - [ ] Database user created
  - [ ] User granted ALL PRIVILEGES
  - [ ] Credentials noted

- [ ] **Database Setup**
  - [ ] Migrations run: `php artisan migrate --force`
  - [ ] OR database imported via phpMyAdmin
  - [ ] Database connection tested
  - [ ] Tables exist and are accessible

---

## 📦 Dependencies

- [ ] **Composer**
  - [ ] `composer install --no-dev --optimize-autoloader` executed
  - [ ] OR `vendor/` directory uploaded
  - [ ] Autoloader optimized

- [ ] **Assets**
  - [ ] `public/build/` directory uploaded
  - [ ] `public/build/manifest.json` exists
  - [ ] CSS and JS files present

---

## 🔗 Storage & Symlinks

- [ ] **Storage Symlink**
  - [ ] `php artisan storage:link` executed
  - [ ] OR symlink created manually
  - [ ] `public/storage` → `storage/app/public` verified

- [ ] **Storage Directories**
  - [ ] `storage/app/` exists and writable
  - [ ] `storage/framework/` exists and writable
  - [ ] `storage/logs/` exists and writable

---

## 🧹 Optimization

- [ ] **Caches Cleared**
  - [ ] `php artisan optimize:clear` executed

- [ ] **Caches Built**
  - [ ] `php artisan config:cache` executed
  - [ ] `php artisan route:cache` executed
  - [ ] `php artisan view:cache` executed

- [ ] **Autoloader**
  - [ ] `composer dump-autoload --optimize` executed

---

## ✅ Testing

- [ ] **Basic Functionality**
  - [ ] Homepage loads: `https://yourdomain.com`
  - [ ] No 500 errors
  - [ ] CSS/JS assets load correctly
  - [ ] Images load correctly

- [ ] **Authentication**
  - [ ] Login page loads: `/login`
  - [ ] Registration works
  - [ ] Login works
  - [ ] Logout works

- [ ] **Dashboard**
  - [ ] Admin can access `/admin/2fa/setup`
  - [ ] 2FA can be enabled
  - [ ] Admin dashboard loads: `/dashboard/admin`
  - [ ] Investor dashboard loads: `/dashboard/investor`
  - [ ] Reseller dashboard loads: `/dashboard/reseller`

- [ ] **Features**
  - [ ] File uploads work (KYC, payment screenshots)
  - [ ] Forms submit correctly
  - [ ] Database queries work
  - [ ] Email sending works (test password reset)

- [ ] **Error Handling**
  - [ ] No errors in `storage/logs/laravel.log`
  - [ ] No PHP errors in cPanel error logs
  - [ ] 404 pages work correctly
  - [ ] Error pages display correctly

---

## 🔒 Security

- [ ] **Environment**
  - [ ] `APP_DEBUG=false` in production
  - [ ] `.env` file permissions: 600
  - [ ] `.env` not accessible via web

- [ ] **Files**
  - [ ] `.htaccess` files in place
  - [ ] Directory listing disabled
  - [ ] Sensitive files protected

- [ ] **SSL**
  - [ ] SSL certificate installed
  - [ ] HTTPS redirects configured
  - [ ] `APP_URL` uses https://

---

## 📊 Monitoring

- [ ] **Logs**
  - [ ] `storage/logs/laravel.log` accessible
  - [ ] Log rotation configured (optional)
  - [ ] Error monitoring set up (optional)

- [ ] **Backups**
  - [ ] Database backup configured
  - [ ] File backup configured
  - [ ] Backup schedule set

---

## 🎯 Post-Deployment

- [ ] **Documentation**
  - [ ] Deployment steps documented
  - [ ] Credentials stored securely
  - [ ] Team notified of deployment

- [ ] **Monitoring**
  - [ ] Site monitored for 24-48 hours
  - [ ] Error logs checked regularly
  - [ ] Performance monitored

---

## 🚨 Emergency Contacts

- [ ] Hostinger Support: [support link]
- [ ] Database Admin: [contact]
- [ ] Server Admin: [contact]
- [ ] Developer: [contact]

---

## 📝 Notes

**Deployment Date:** _______________

**Deployed By:** _______________

**Domain:** _______________

**Database Name:** _______________

**Issues Encountered:**
- 
- 
- 

**Resolutions:**
- 
- 
- 

---

**Checklist Version:** 1.0
**Last Updated:** 2024


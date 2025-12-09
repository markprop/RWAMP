# Deployment Guide - RWAMP Laravel Project

**Last Updated:** 2024-11-27  
**Project:** RWAMP Laravel Application  
**Target:** Hostinger/Production Server

---

## Quick Deployment Checklist

### ✅ Pre-Deployment (Local)

- [x] All tests pass: `php artisan test` (23/23 passing)
- [x] Code refactored and organized
- [x] Backward compatibility maintained
- [x] Routes verified
- [x] Controllers exist and functional

### 🚀 Deployment Steps

#### 1. **Backup Current Live Site**

```bash
# SSH into server
ssh user@dev.rwamp.net

# Backup database
mysqldump -u username -p database_name > /backup/db_backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf /backup/files_backup_$(date +%Y%m%d_%H%M%S).tar.gz /path/to/live/project
```

#### 2. **Deploy via Git (Recommended)**

```bash
# On server, navigate to project directory
cd /path/to/live/project

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations (if any new ones)
php artisan migrate --force

# Clear all caches
php artisan optimize:clear

# Rebuild caches for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 3. **Deploy via FTP/SFTP (Alternative)**

**Files to Upload:**
- All files EXCEPT:
  - `.env` (keep existing on server)
  - `storage/` (keep existing on server)
  - `vendor/` (run `composer install` on server)
  - `node_modules/` (not needed on server)
  - `.git/` (optional)

**After Upload:**
```bash
# SSH into server
ssh user@dev.rwamp.net
cd /path/to/live/project

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

---

## Server Configuration

### Required PHP Extensions

```bash
php -m | grep -E "pdo_mysql|mbstring|xml|openssl|json|curl|zip|gd"
```

Required extensions:
- ✅ `pdo_mysql`
- ✅ `mbstring`
- ✅ `xml`
- ✅ `openssl`
- ✅ `json`
- ✅ `curl`
- ✅ `zip`
- ✅ `gd` (for image processing)

### Apache Configuration

Ensure `mod_rewrite` is enabled:

```bash
# Check if enabled
apache2ctl -M | grep rewrite

# If not enabled, enable it
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Document Root

Ensure Apache/Nginx points to `public/` directory:

**Apache Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName dev.rwamp.net
    DocumentRoot /path/to/project/public
    
    <Directory /path/to/project/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name dev.rwamp.net;
    root /path/to/project/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## Environment Configuration

### Required `.env` Variables

```env
# Application
APP_NAME=RWAMP
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://dev.rwamp.net

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@dev.rwamp.net
MAIL_FROM_NAME="${APP_NAME}"

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Pusher (if chat enabled)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=ap2
PUSHER_APP_USE_TLS=true

# Google reCAPTCHA
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key

# Analytics
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
META_PIXEL_ID=XXXXXXXXXX

# Admin
ADMIN_EMAIL=admin@rwamp.net
```

### Generate App Key (if needed)

```bash
php artisan key:generate
```

---

## Post-Deployment Verification

### 1. **Check Application Status**

```bash
# Check if application is accessible
curl -I https://dev.rwamp.net

# Should return: HTTP/1.1 200 OK
```

### 2. **Test Key Functionalities**

- [ ] Homepage loads: `https://dev.rwamp.net`
- [ ] Login page: `https://dev.rwamp.net/login`
- [ ] Register page: `https://dev.rwamp.net/register`
- [ ] Admin dashboard: `https://dev.rwamp.net/dashboard/admin` (after login as admin)
- [ ] Reseller dashboard: `https://dev.rwamp.net/dashboard/reseller` (after login as reseller)
- [ ] Investor dashboard: `https://dev.rwamp.net/dashboard/investor` (after login as investor)

### 3. **Check Logs**

```bash
# View latest errors
tail -f storage/logs/laravel.log

# Check for any errors
grep -i error storage/logs/laravel.log | tail -20
```

### 4. **Test API Endpoints**

```bash
# Test API health
curl https://dev.rwamp.net/api/check-email?email=test@example.com
```

---

## Troubleshooting

### Issue: "500 Internal Server Error"

**Check:**
1. File permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

2. `.env` file exists and has correct values:
   ```bash
   ls -la .env
   cat .env | grep APP_KEY
   ```

3. Laravel logs:
   ```bash
   tail -50 storage/logs/laravel.log
   ```

### Issue: "404 Not Found"

**Check:**
1. `.htaccess` exists in `public/` directory
2. `mod_rewrite` is enabled
3. Document root points to `public/` directory
4. Route cache cleared:
   ```bash
   php artisan route:clear
   ```

### Issue: "Class Not Found"

**Solution:**
```bash
composer dump-autoload
php artisan optimize:clear
```

### Issue: "Database Connection Error"

**Check:**
1. Database credentials in `.env`
2. Database server is running
3. Database user has proper permissions
4. Firewall allows connection

---

## Automated Deployment (GitHub Actions)

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /path/to/project
          git pull origin main
          composer install --optimize-autoloader --no-dev
          php artisan migrate --force
          php artisan optimize:clear
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
```

---

## Rollback Procedure

If deployment fails:

```bash
# 1. Restore files from backup
cd /path/to/project
rm -rf *
tar -xzf /backup/files_backup_TIMESTAMP.tar.gz

# 2. Restore database (if migrations were run)
mysql -u username -p database_name < /backup/db_backup_TIMESTAMP.sql

# 3. Clear caches
php artisan optimize:clear

# 4. Rebuild caches
php artisan config:cache
php artisan route:cache
```

---

## Maintenance Commands

### Daily

```bash
# Clear old logs (optional)
find storage/logs -name "*.log" -mtime +30 -delete
```

### Weekly

```bash
# Optimize database
php artisan optimize

# Clear old cache
php artisan cache:clear
```

### Monthly

```bash
# Update dependencies (test first!)
composer update --no-dev
php artisan migrate
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secure
- [ ] File permissions correct (755 for directories, 644 for files)
- [ ] `.env` file not accessible via web
- [ ] HTTPS enabled
- [ ] Firewall configured
- [ ] Regular backups scheduled

---

## Support

For issues or questions:
1. Check `storage/logs/laravel.log`
2. Review `MERGE_ANALYSIS.md` for known issues
3. Check server error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`

---

**Status:** ✅ Ready for deployment


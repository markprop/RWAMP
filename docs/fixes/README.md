# Fixes & Troubleshooting Documentation

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=FF6B6B&center=true&vCenter=true&width=600&lines=Troubleshooting+%26+Issue+Resolution" alt="Fixes Header" />
</p>

This directory contains troubleshooting guides and fix instructions for common issues in the RWAMP platform. All fixes are tested and verified.

## 📄 Documents

### Registration & Authentication
- **FIX_REGISTRATION_500_ERROR.md** - Fixing registration 500 errors and validation issues

### Email Issues
- **EMAIL_FIX_INSTRUCTIONS.md** - Email configuration fixes and SMTP troubleshooting

### Chat System Fixes
- **CHAT_ERRORS_FIXED.md** - Chat system error fixes and solutions
- **CSP_AND_CHAT_BUTTONS_FIX.md** - Content Security Policy and chat button fixes

### Dashboard Issues
- **DASHBOARD_FIX_GUIDE.md** - Dashboard troubleshooting guide for all roles

### Security & Recovery
- **FIX_CORRUPTED_RECOVERY_CODES.md** - Fixing corrupted 2FA recovery codes

### Server Issues
- **LIVE_SERVER_FIX_INSTRUCTIONS.md** - Live server troubleshooting and fixes

## 🔧 Common Issues & Solutions

### Registration Errors
**Symptoms**: 500 error on registration, validation failures

**Solutions**:
1. Check **FIX_REGISTRATION_500_ERROR.md** for detailed fixes
2. Verify environment configuration in `.env`
3. Check database connectivity
4. Verify reCAPTCHA configuration
5. Check file permissions on `storage/` directory

### Email Problems
**Symptoms**: Emails not sending, SMTP errors

**Solutions**:
1. Follow **EMAIL_FIX_INSTRUCTIONS.md** step-by-step
2. Verify SMTP settings in `.env`:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.hostinger.com
   MAIL_PORT=465
   MAIL_USERNAME=your_email@domain.com
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=ssl
   ```
3. Test email configuration: `php artisan tinker` → `Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });`
4. Check mail server logs

### Chat System Issues
**Symptoms**: Chat not loading, buttons not working, CSP errors

**Solutions**:
1. Review **CHAT_ERRORS_FIXED.md** for chat-specific issues
2. Check **CSP_AND_CHAT_BUTTONS_FIX.md** for Content Security Policy problems
3. Verify Pusher configuration in `.env`
4. Check browser console for errors
5. Verify routes are enabled in `routes/web.php`

### Dashboard Problems
**Symptoms**: Dashboard not loading, permission errors, missing data

**Solutions**:
1. Use **DASHBOARD_FIX_GUIDE.md** for comprehensive troubleshooting
2. Check user permissions and role assignments
3. Verify middleware is properly configured
4. Check database for user data
5. Clear caches: `php artisan cache:clear && php artisan config:clear`

### 2FA Issues
**Symptoms**: Recovery codes not working, 2FA setup failing

**Solutions**:
1. Follow **FIX_CORRUPTED_RECOVERY_CODES.md** for recovery code problems
2. Regenerate recovery codes via admin dashboard
3. Verify Fortify configuration
4. Check database for 2FA fields

### Server Issues
**Symptoms**: 500 errors, slow performance, connection issues

**Solutions**:
1. Review **LIVE_SERVER_FIX_INSTRUCTIONS.md** for server-specific fixes
2. Check error logs: `storage/logs/laravel.log`
3. Verify PHP version (8.2+)
4. Check file permissions
5. Verify `.env` configuration

## 🚨 Quick Troubleshooting Steps

### Step 1: Check Error Logs
```bash
tail -f storage/logs/laravel.log
```

### Step 2: Verify Environment
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 3: Check Database
```bash
php artisan migrate:status
php artisan db:show
```

### Step 4: Verify Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 5: Test Configuration
```bash
php artisan tinker
# Test database connection
DB::connection()->getPdo();
# Test mail configuration
Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

## 📋 Common Error Codes

| Error Code | Issue | Solution Document |
|------------|-------|-------------------|
| 500 | Server Error | Check error logs, verify `.env` |
| 419 | CSRF Token | Clear cache, check session |
| 403 | Permission Denied | Check user role, verify middleware |
| 404 | Route Not Found | Check `routes/web.php`, clear route cache |
| 422 | Validation Error | Check form validation rules |

## 🔍 Diagnostic Commands

```bash
# Check Laravel version
php artisan --version

# Check PHP version
php -v

# Check database connection
php artisan db:show

# List all routes
php artisan route:list

# Check queue status
php artisan queue:work --once

# Check scheduled tasks
php artisan schedule:list
```

## 📚 Related Documentation

- **Main README**: [`../../README.md`](../../README.md)
- **Deployment**: [`../deployment/`](../deployment/) - Deployment troubleshooting
- **Database**: [`../database/`](../database/) - Database issues
- **Environment**: [`../environment/`](../environment/) - Environment configuration

## 🔗 Support

- **Website**: [rwamp.io](https://rwamp.io)
- **Email**: info@rwamp.io
- **Phone**: +92 370 1346038

---

**Last Updated:** January 27, 2025

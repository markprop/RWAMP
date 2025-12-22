# RWAMP Platform Fixes - Deployment Guide

## Overview
This document outlines all fixes applied to resolve user-reported issues with the RWAMP platform, including CSRF token expiration, 2FA decryption errors, session management, and performance optimizations.

## Issues Fixed

### 1. 2FA Decryption Errors
**Problem:** "Failed to decrypt recovery codes for user X: The MAC is invalid"
**Solution:**
- Added graceful error handling in `User` model
- Created `Reencrypt2FACodes` artisan command
- Improved `Admin2FAController` error handling

### 2. CSRF Token Expiration (419 Errors)
**Problem:** "The page has expired due to inactivity" errors
**Solution:**
- Enhanced `HandleExpiredCsrf` middleware with better error handling
- Improved CSRF token refresh in JavaScript (reduced interval to 10 minutes)
- Added Axios interceptor support for CSRF token updates
- Better redirect handling with flash messages

### 3. Session Management Issues
**Problem:** Multi-tab conflicts, logout issues, session expiration
**Solution:**
- Updated logout to perform full session cleanup
- Fixed tab session cookie clearing on logout
- Improved session invalidation

### 4. Rate Fetching Optimization
**Problem:** Repetitive API calls to exchangerate-api.com causing log spam
**Solution:**
- Added cache locks to prevent concurrent API calls
- Improved caching with `Cache::remember()` for atomic operations
- Reduced API call frequency with proper cache management

### 5. Asset Loading Issues
**Problem:** JS/CSS not loading properly, cache busting issues
**Solution:**
- Updated `vite.config.js` with proper manifest and chunk naming
- Ensured cache busting with hash-based filenames

## Deployment Steps

### Step 1: Database Migration
```bash
# Create sessions table for database session driver
php artisan migrate
```

### Step 2: Environment Configuration
Update your `.env` file with these recommended production settings:

```env
# Session Configuration (IMPORTANT: Switch to database for production)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://rwamp.io

# Cache Configuration (Redis recommended for production)
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Ensure APP_KEY is consistent across all environments
APP_KEY=base64:your-key-here
```

### Step 3: Re-encrypt 2FA Recovery Codes
If you've changed APP_KEY or have users with corrupted recovery codes:

```bash
# Re-encrypt all 2FA recovery codes
php artisan 2fa:reencrypt --all --regenerate

# Or for a specific user
php artisan 2fa:reencrypt --user-id=5 --regenerate
```

### Step 4: Build Assets
```bash
# Install dependencies (if needed)
npm install

# Build production assets with cache busting
npm run build
```

### Step 5: Clear All Caches
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

### Step 6: Verify Session Table
Ensure the sessions table exists:
```bash
php artisan migrate:status
```

If the sessions table migration hasn't run:
```bash
php artisan migrate
```

## Testing Checklist

### Multi-Browser/Device Testing
1. **Login/Logout Flow:**
   - [ ] Login in Chrome
   - [ ] Open new tab, verify session persists
   - [ ] Logout, verify all tabs are logged out
   - [ ] Login in Firefox (different browser)
   - [ ] Verify no session conflicts

2. **CSRF Token Handling:**
   - [ ] Leave page open for 15+ minutes
   - [ ] Submit a form, verify no 419 error
   - [ ] Check browser console for CSRF refresh logs
   - [ ] Verify token auto-refreshes every 10 minutes

3. **2FA Testing:**
   - [ ] Setup 2FA for admin user
   - [ ] Verify recovery codes can be viewed
   - [ ] Test recovery code regeneration
   - [ ] Verify no decryption errors in logs

4. **Rate Fetching:**
   - [ ] Monitor logs during page loads
   - [ ] Verify no repetitive API calls
   - [ ] Check cache is working (rates cached for 1 hour)

5. **Asset Loading:**
   - [ ] Clear browser cache
   - [ ] Load dashboard, verify all JS/CSS loads
   - [ ] Check network tab for proper cache headers
   - [ ] Verify assets have hash-based filenames

## Files Modified

### Core Files
- `app/Models/User.php` - 2FA decryption error handling
- `app/Http/Controllers/AuthController.php` - Logout improvements
- `app/Http/Middleware/HandleExpiredCsrf.php` - Better CSRF error handling
- `app/Helpers/PriceHelper.php` - Rate fetching optimization
- `app/Exceptions/Handler.php` - Improved error handling
- `resources/js/app.js` - CSRF token refresh improvements
- `vite.config.js` - Asset build configuration

### New Files
- `app/Console/Commands/Reencrypt2FACodes.php` - 2FA re-encryption command
- `database/migrations/2025_12_19_000000_create_sessions_table.php` - Sessions table

## Monitoring

### Log Monitoring
Watch for these log entries after deployment:

**Good Signs:**
- `USD to PKR rate fetched from exchangerate-api.com` (should appear once per hour max)
- `CSRF token mismatch` (should be rare, only on actual expiration)
- `Successfully re-encrypted recovery codes` (if running 2FA command)

**Warning Signs:**
- Multiple `USD to PKR rate fetched` entries in quick succession (indicates cache not working)
- Frequent `CSRF token mismatch` errors (indicates session issues)
- `Failed to decrypt recovery codes` errors (run re-encryption command)

### Performance Monitoring
- Check response times for dashboard pages
- Monitor API call frequency to exchangerate-api.com
- Verify session table size (should not grow unbounded)

## Rollback Plan

If issues occur after deployment:

1. **Revert Session Driver:**
   ```env
   SESSION_DRIVER=file
   ```
   Then clear config cache: `php artisan config:clear`

2. **Revert Asset Build:**
   ```bash
   npm run build -- --mode development
   ```

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Additional Recommendations

1. **Use Redis for Sessions:** For better performance and scalability
   ```env
   SESSION_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

2. **Session Cleanup:** Add a scheduled task to clean old sessions
   ```bash
   php artisan session:gc
   ```
   Add to `app/Console/Kernel.php`:
   ```php
   $schedule->command('session:gc')->hourly();
   ```

3. **Monitor 2FA Issues:** Set up alerts for decryption errors
   - Watch for `Failed to decrypt recovery codes` in logs
   - Automatically run `2fa:reencrypt --all --regenerate` if needed

4. **Rate Limiting:** Consider adding rate limiting for CSRF token endpoint
   ```php
   Route::get('/csrf-token', ...)->middleware('throttle:60,1');
   ```

## Support

If issues persist after deployment:
1. Check `storage/logs/laravel.log` for errors
2. Verify `.env` configuration matches recommendations
3. Ensure all migrations have run
4. Clear all caches and rebuild assets
5. Test in incognito/private browsing mode

---

**Last Updated:** 2025-12-19
**Version:** 1.0.0

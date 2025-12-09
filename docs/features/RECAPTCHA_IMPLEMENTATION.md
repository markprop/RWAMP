# 🔒 Google reCAPTCHA v2 Implementation Guide

This document describes the Google reCAPTCHA v2 "I'm not a robot" checkbox implementation for the RWAMP Laravel application.

## ✅ Implementation Complete

reCAPTCHA v2 has been successfully added to:
- ✅ `/login` page
- ✅ `/register` page (both Investor and Reseller forms)

---

## 📋 Files Modified

### 1. **Backend Files**

#### `app/Rules/Recaptcha.php` (NEW)
- Custom validation rule for reCAPTCHA verification
- Validates the reCAPTCHA response with Google's API
- Handles network errors gracefully
- Skips validation if no secret key is configured (for development)

#### `app/Providers/AppServiceProvider.php` (UPDATED)
- Registered custom `recaptcha` validation rule
- Makes it available as `'recaptcha'` in validation arrays

#### `app/Http/Controllers/AuthController.php` (UPDATED)
- Added reCAPTCHA validation to `login()` method
- Added reCAPTCHA validation to `register()` method
- Added reCAPTCHA validation to `registerResellerApplication()` method
- Conditional validation (only required if secret key is configured)

#### `app/Http/Middleware/SecurityHeaders.php` (UPDATED)
- Updated CSP (Content Security Policy) to allow:
  - `https://www.google.com` (scripts and API calls)
  - `https://www.gstatic.com` (static resources)
  - `frame-src` for reCAPTCHA iframe

### 2. **Frontend Files**

#### `resources/views/auth/login.blade.php` (UPDATED)
- Added reCAPTCHA widget before submit button
- Added reCAPTCHA script in `@push('scripts')` section
- Added error message display for reCAPTCHA validation

#### `resources/views/auth/signup.blade.php` (UPDATED)
- Added reCAPTCHA widget to Investor form (before submit button)
- Added reCAPTCHA widget to Reseller form (before submit button)
- Added reCAPTCHA script in `@push('scripts')` section
- Added error message display for reCAPTCHA validation

---

## ⚙️ Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Google reCAPTCHA v2
RECAPTCHA_SITE_KEY=your_site_key_here
RECAPTCHA_SECRET_KEY=your_secret_key_here
RECAPTCHA_MIN_SCORE=0.5
```

**Note:** `RECAPTCHA_MIN_SCORE` is for v3, but kept for compatibility. For v2, it's not used.

### Getting reCAPTCHA Keys

1. Go to [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Click "Create" to create a new site
3. Select **reCAPTCHA v2** → **"I'm not a robot" Checkbox**
4. Add your domain(s):
   - `localhost` (for development)
   - `dev.rwamp.net` (for staging)
   - `rwamp.net` (for production)
5. Accept the terms and submit
6. Copy the **Site Key** and **Secret Key**
7. Add them to your `.env` file

---

## 🔧 How It Works

### Frontend Flow

1. User visits `/login` or `/register`
2. If `RECAPTCHA_SITE_KEY` is configured, reCAPTCHA widget loads
3. User fills out the form
4. User checks the "I'm not a robot" checkbox
5. reCAPTCHA generates a response token
6. Form submits with `g-recaptcha-response` field

### Backend Flow

1. Controller receives form submission
2. Validation checks for `g-recaptcha-response` field
3. If secret key is configured, `Recaptcha` rule validates:
   - Sends token to Google's verification API
   - Checks if verification is successful
   - Returns validation result
4. If validation fails, user sees error message
5. If validation passes, login/registration proceeds

---

## 🧪 Testing

### Development Testing (Without Keys)

If `RECAPTCHA_SECRET_KEY` is empty or not set:
- reCAPTCHA widget **will not display**
- Validation **will be skipped**
- Forms work normally (for development)

### Production Testing (With Keys)

1. **Set up keys in `.env`:**
   ```env
   RECAPTCHA_SITE_KEY=your_site_key
   RECAPTCHA_SECRET_KEY=your_secret_key
   ```

2. **Clear config cache:**
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

3. **Test login:**
   - Visit `/login`
   - reCAPTCHA widget should appear
   - Fill form and check reCAPTCHA
   - Submit form
   - Should work if reCAPTCHA is valid

4. **Test registration:**
   - Visit `/register`
   - Try both Investor and Reseller tabs
   - reCAPTCHA widget should appear on both
   - Fill form and check reCAPTCHA
   - Submit form
   - Should work if reCAPTCHA is valid

5. **Test validation:**
   - Submit form without checking reCAPTCHA
   - Should show error: "Please complete the reCAPTCHA verification."
   - Submit with invalid/expired token
   - Should show error: "reCAPTCHA verification failed. Please try again."

---

## 🔍 Troubleshooting

### Issue: reCAPTCHA Widget Not Showing

**Possible Causes:**
1. `RECAPTCHA_SITE_KEY` not set in `.env`
2. Config cache not cleared
3. CSP blocking Google scripts

**Solutions:**
```bash
# Check .env file
cat .env | grep RECAPTCHA

# Clear and rebuild config cache
php artisan config:clear
php artisan config:cache

# Check browser console for CSP errors
# If CSP errors, verify SecurityHeaders middleware is updated
```

### Issue: "reCAPTCHA verification failed"

**Possible Causes:**
1. Invalid secret key
2. Domain mismatch (key registered for different domain)
3. Network issues reaching Google API
4. Token expired (user took too long)

**Solutions:**
1. Verify secret key in `.env` matches Google reCAPTCHA console
2. Check domain in reCAPTCHA console matches your domain
3. Check logs: `storage/logs/laravel.log`
4. User should refresh page and try again

### Issue: CSP Errors in Browser Console

**Solution:**
- Verify `SecurityHeaders` middleware includes:
  - `https://www.google.com` in `script-src`
  - `https://www.gstatic.com` in `script-src`
  - `https://www.google.com` in `frame-src`
  - `https://www.google.com` in `connect-src`

### Issue: Validation Always Fails

**Possible Causes:**
1. Secret key incorrect
2. Network timeout
3. Google API rate limiting

**Solutions:**
1. Double-check secret key
2. Check network connectivity
3. Review logs for specific error messages
4. Test with Google's reCAPTCHA test keys:
   - Site Key: `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI`
   - Secret Key: `6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe`
   - (These always pass for testing)

---

## 🔒 Security Features

### Implemented Security

1. **Server-Side Validation**
   - All reCAPTCHA tokens verified on server
   - Cannot be bypassed by disabling JavaScript

2. **Conditional Validation**
   - Only validates if secret key is configured
   - Allows development without keys

3. **Error Handling**
   - Network errors logged but don't crash
   - User-friendly error messages
   - No sensitive information exposed

4. **CSP Integration**
   - Security headers updated to allow reCAPTCHA
   - Maintains security while allowing functionality

### Best Practices

1. **Rate Limiting**
   - Login: Already has `throttle:5,1` middleware
   - Registration: Can add rate limiting if needed

2. **Key Security**
   - Never commit keys to version control
   - Use different keys for dev/staging/production
   - Rotate keys if compromised

3. **Monitoring**
   - Monitor reCAPTCHA failure rates
   - Check logs for verification errors
   - Track suspicious activity patterns

---

## 📝 Code Examples

### Using reCAPTCHA in Other Forms

If you need to add reCAPTCHA to other forms:

**1. Add widget to view:**
```blade
@if(config('services.recaptcha.site_key'))
    <div class="mt-4">
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-theme="light"></div>
        @error('g-recaptcha-response')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
@endif
```

**2. Add script:**
```blade
@push('scripts')
@if(config('services.recaptcha.site_key'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endpush
```

**3. Add validation in controller:**
```php
$request->validate([
    // ... other fields
    'g-recaptcha-response' => config('services.recaptcha.secret_key') 
        ? ['required', 'recaptcha'] 
        : ['nullable'],
], [
    'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
    'g-recaptcha-response.recaptcha' => 'reCAPTCHA verification failed. Please try again.',
]);
```

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] reCAPTCHA widget appears on `/login` page
- [ ] reCAPTCHA widget appears on `/register` page (Investor tab)
- [ ] reCAPTCHA widget appears on `/register` page (Reseller tab)
- [ ] Widget is visible and clickable on mobile devices
- [ ] Form submission works with valid reCAPTCHA
- [ ] Error message shows if reCAPTCHA not completed
- [ ] Error message shows if reCAPTCHA verification fails
- [ ] No CSP errors in browser console
- [ ] No JavaScript errors in browser console
- [ ] Works on different browsers (Chrome, Firefox, Safari, Edge)
- [ ] Works on mobile devices
- [ ] Logs show successful verifications
- [ ] Logs show failures (if any) with proper error messages

---

## 🚀 Deployment Notes

### Before Deploying

1. **Get Production Keys:**
   - Create reCAPTCHA site in Google Console
   - Add production domain
   - Copy site key and secret key

2. **Update `.env` on Server:**
   ```env
   RECAPTCHA_SITE_KEY=your_production_site_key
   RECAPTCHA_SECRET_KEY=your_production_secret_key
   ```

3. **Clear Caches:**
   ```bash
   php artisan config:clear
   php artisan config:cache
   php artisan view:clear
   ```

4. **Test:**
   - Test login with reCAPTCHA
   - Test registration with reCAPTCHA
   - Verify error handling

### After Deploying

1. **Monitor Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i recaptcha
   ```

2. **Check Browser Console:**
   - No CSP errors
   - No JavaScript errors
   - reCAPTCHA loads correctly

3. **Test User Experience:**
   - Widget loads quickly
   - Easy to click on mobile
   - Error messages are clear

---

## 📚 Additional Resources

- [Google reCAPTCHA Documentation](https://developers.google.com/recaptcha/docs/display)
- [reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
- [Laravel Validation Documentation](https://laravel.com/docs/10.x/validation)

---

## 🎯 Summary

✅ **Implementation Status:** Complete
✅ **Security:** Server-side validation implemented
✅ **UX:** User-friendly error messages
✅ **Compatibility:** Works with existing authentication flow
✅ **Mobile:** Responsive and touch-friendly
✅ **CSP:** Security headers updated
✅ **Development:** Works without keys (for local dev)

---

**Implementation Date:** 2024
**Version:** reCAPTCHA v2 "I'm not a robot" Checkbox
**Laravel Version:** 10+


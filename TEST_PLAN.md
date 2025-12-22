# RWAMP Platform Fixes - Test Plan

## Pre-Deployment Testing

### 1. 2FA Decryption Fixes

**Test 1.1: Re-encrypt Command**
```bash
# Test for specific user
php artisan 2fa:reencrypt --user-id=5 --regenerate

# Test for all users
php artisan 2fa:reencrypt --all --regenerate
```

**Expected Results:**
- Command runs without errors
- Corrupted recovery codes are regenerated
- Valid recovery codes are re-encrypted
- Logs show success messages

**Test 1.2: Admin 2FA Setup**
1. Login as admin user
2. Navigate to `/admin/2fa/setup`
3. Verify no decryption errors appear
4. Setup 2FA if not already enabled
5. Verify recovery codes can be viewed
6. Test recovery code regeneration

**Expected Results:**
- No "MAC is invalid" errors
- 2FA setup completes successfully
- Recovery codes display correctly
- Regeneration works without errors

### 2. CSRF Token Management

**Test 2.1: Token Auto-Refresh**
1. Open dashboard in browser
2. Open browser console
3. Wait 30 seconds
4. Check console for CSRF refresh logs
5. Wait 10 minutes
6. Verify token refreshes automatically

**Expected Results:**
- Token refreshes after 30 seconds
- Token refreshes every 10 minutes
- No 419 errors during normal usage
- Meta tag and form inputs update

**Test 2.2: Form Submission After Expiration**
1. Open a form page (e.g., purchase, contact)
2. Leave page open for 15+ minutes
3. Fill out form
4. Submit form
5. Verify submission succeeds

**Expected Results:**
- Form submits successfully
- Token auto-refreshes before submission
- No 419 errors
- Success message appears

**Test 2.3: AJAX Requests**
1. Open dashboard
2. Perform AJAX actions (e.g., check payment status)
3. Leave page open for 15+ minutes
4. Perform another AJAX action
5. Verify request succeeds

**Expected Results:**
- AJAX requests succeed
- Axios interceptor refreshes token
- No 419 errors in console
- Responses return correctly

### 3. Session Management

**Test 3.1: Multi-Tab Login/Logout**
1. Open browser (Chrome)
2. Login to account A
3. Open new tab, verify still logged in
4. Open new tab, login to account B (different account)
5. Logout from account B
6. Verify account A tabs are still logged in
7. Logout from account A
8. Verify all tabs are logged out

**Expected Results:**
- Each tab maintains independent session
- Logout clears only that tab's session
- No session conflicts between tabs
- Cookies cleared properly on logout

**Test 3.2: Cross-Browser Sessions**
1. Login in Chrome
2. Open Firefox (different browser)
3. Login with different account
4. Verify no conflicts
5. Logout from Chrome
6. Verify Firefox session still active

**Expected Results:**
- Sessions independent across browsers
- No cross-browser conflicts
- Logout works correctly in each browser

**Test 3.3: Session Expiration**
1. Login to dashboard
2. Leave page idle for 2+ hours
3. Try to perform an action
4. Verify graceful handling

**Expected Results:**
- Session expires gracefully
- User redirected to login with message
- No hard errors
- Data preserved if possible

### 4. Rate Fetching Optimization

**Test 4.1: API Call Frequency**
1. Monitor `storage/logs/laravel.log`
2. Load dashboard page
3. Refresh page 10 times quickly
4. Check logs for API calls

**Expected Results:**
- Only 1 API call per hour maximum
- Cache locks prevent concurrent calls
- Logs show "rate fetched" once per hour
- No repetitive API calls in logs

**Test 4.2: Cache Effectiveness**
1. Clear application cache: `php artisan cache:clear`
2. Load dashboard (should fetch from API)
3. Load dashboard again immediately
4. Verify uses cached value

**Expected Results:**
- First load fetches from API
- Subsequent loads use cache
- Cache persists for 1 hour
- No unnecessary API calls

### 5. Asset Loading

**Test 5.1: Production Build**
```bash
npm run build
```

**Expected Results:**
- Build completes without errors
- Assets generated with hash-based filenames
- Manifest file created
- All JS/CSS files present

**Test 5.2: Browser Cache Busting**
1. Clear browser cache completely
2. Load dashboard
3. Check Network tab in DevTools
4. Verify assets load with hash filenames
5. Hard refresh (Ctrl+F5)
6. Verify new assets load

**Expected Results:**
- Assets load with hash filenames
- Cache headers set correctly
- Hard refresh loads new assets
- No 404 errors for assets

**Test 5.3: Asset Integrity**
1. Load dashboard
2. Check browser console for errors
3. Verify Alpine.js initializes
4. Verify Chart.js works
5. Verify all interactive elements function

**Expected Results:**
- No JavaScript errors
- Alpine.js components work
- Charts render correctly
- All features functional

### 6. Error Handling

**Test 6.1: CSRF Error Handling**
1. Manually expire CSRF token (clear session)
2. Submit a form
3. Verify error handling

**Expected Results:**
- Graceful error message
- Redirect with flash message
- No hard 419 error page
- User can retry after refresh

**Test 6.2: 2FA Error Handling**
1. Login as admin with corrupted 2FA codes
2. Navigate to 2FA setup
3. Verify error handling

**Expected Results:**
- No fatal errors
- User notified of corruption
- Option to regenerate codes
- System continues to function

## Post-Deployment Testing

### Production Smoke Tests

**Test P1: Login Flow**
1. Visit homepage
2. Click login
3. Enter credentials
4. Verify successful login
5. Verify redirect to correct dashboard

**Test P2: Purchase Flow**
1. Login as investor
2. Navigate to purchase page
3. Fill purchase form
4. Submit form
5. Verify no CSRF errors

**Test P3: Dashboard Loading**
1. Login to dashboard
2. Verify all widgets load
3. Check for JavaScript errors
4. Verify prices display correctly
5. Verify no repetitive API calls

**Test P4: Multi-User Scenarios**
1. Login as User A in Chrome
2. Login as User B in Firefox
3. Perform actions in both
4. Verify no conflicts
5. Logout from both
6. Verify clean logout

## Monitoring Checklist

After deployment, monitor:

- [ ] `storage/logs/laravel.log` for errors
- [ ] CSRF token mismatch frequency (should be rare)
- [ ] API call frequency to exchangerate-api.com (max 1/hour)
- [ ] Session table size (should not grow unbounded)
- [ ] 2FA decryption errors (should be zero)
- [ ] Asset loading errors in browser console
- [ ] User-reported issues

## Rollback Criteria

Rollback if:
- Multiple users report login issues
- CSRF errors increase significantly
- 2FA completely broken
- Critical functionality fails
- Database errors occur

## Success Criteria

Deployment is successful if:
- ✅ No CSRF token expiration errors
- ✅ No 2FA decryption errors
- ✅ Multi-tab sessions work correctly
- ✅ Rate fetching optimized (1 call/hour max)
- ✅ Assets load correctly
- ✅ All features functional
- ✅ No increase in error logs

---

**Test Date:** _______________
**Tested By:** _______________
**Status:** ☐ Passed  ☐ Failed  ☐ Needs Review

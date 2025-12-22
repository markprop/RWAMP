# Extended Mock Test Results for PriceHelper Caching

## Test Scenarios

### Scenario 1: Valid Rate Within Bounds
**Setup:**
- API returns: `PKR = 300`
- Config: `pkr_min = 100`, `pkr_max = 10000`
- Cache: Empty

**Execution:**
1. `fetchUsdToPkrRate()` called
2. Lock acquired → API fetch succeeds
3. Rate `300` validated: `300 >= 100 && 300 <= 10000` ✅
4. `Cache::put('exchange_rate_usd_pkr', 300, 3600)` succeeds
5. Log: `info` - "USD to PKR rate fetched from exchangerate-api.com" with `['rate' => 300]`
6. Returns: `300.0`

**Result:** ✅ **PASS**
- Rate cached for 3600 seconds
- Subsequent calls use cache (no API calls)
- No errors logged

---

### Scenario 2: Invalid Rate - Too High (15000)
**Setup:**
- API returns: `PKR = 15000`
- Config: `pkr_min = 100`, `pkr_max = 10000`
- Cache: Empty

**Execution:**
1. `fetchUsdToPkrRate()` called
2. Lock acquired → API fetch succeeds
3. Rate `15000` validated: `15000 >= 100 && 15000 <= 10000` ❌ (fails max check)
4. Validation fails → **No cache write**
5. Log: `warning` - "Invalid USD to PKR rate from exchangerate-api.com: 15000 (must be >= 100 and <= 10000)" with context:
   ```php
   [
       'rate' => 15000,
       'type' => 'double',
       'min'  => 100,
       'max'  => 10000,
   ]
   ```
6. Flow continues to alternative API / default fallback
7. Default rate `278` cached for 300 seconds
8. Returns: `278.0`

**Result:** ✅ **PASS**
- Invalid rate rejected (not cached)
- Clear warning logged with bounds
- Graceful fallback to default
- No exceptions thrown

---

### Scenario 3: Invalid Rate - Too Low (50)
**Setup:**
- API returns: `PKR = 50`
- Config: `pkr_min = 100`, `pkr_max = 10000`
- Cache: Empty

**Execution:**
1. `fetchUsdToPkrRate()` called
2. Lock acquired → API fetch succeeds
3. Rate `50` validated: `50 >= 100 && 50 <= 10000` ❌ (fails min check)
4. Validation fails → **No cache write**
5. Log: `warning` - "Invalid USD to PKR rate from exchangerate-api.com: 50 (must be >= 100 and <= 10000)" with context:
   ```php
   [
       'rate' => 50,
       'type' => 'double',
       'min'  => 100,
       'max'  => 10000,
   ]
   ```
6. Flow continues to alternative API / default fallback
7. Default rate `278` cached for 300 seconds
8. Returns: `278.0`

**Result:** ✅ **PASS**
- Invalid rate rejected (not cached)
- Clear warning logged with bounds
- Graceful fallback to default

---

### Scenario 4: Cache Exception on Put (Success Path)
**Setup:**
- API returns: `PKR = 300` (valid)
- Config: `pkr_min = 100`, `pkr_max = 10000`
- Cache: `Cache::put()` throws `\Exception` ("Redis connection failed")

**Execution:**
1. `fetchUsdToPkrRate()` called
2. Lock acquired → API fetch succeeds
3. Rate `300` validated: ✅
4. `Cache::put('exchange_rate_usd_pkr', 300, 3600)` throws exception
5. Exception caught → Log: `error` - "Cache put failed for USD to PKR rate (exchangerate-api.com)" with context:
   ```php
   [
       'rate'  => 300,
       'ttl'   => 3600,
       'error' => 'Redis connection failed',
   ]
   ```
6. **Method continues** → Returns `300.0` (rate still valid, just not cached)
7. Next call will re-fetch (cache miss)

**Result:** ✅ **PASS**
- Cache failure logged but doesn't break flow
- Valid rate still returned
- System remains functional

---

### Scenario 5: Cache Exception on Get (Concurrent Path)
**Setup:**
- Another process is fetching (lock not acquired)
- Cache: `Cache::get()` throws `\Exception` ("Redis connection failed")

**Execution:**
1. `fetchUsdToPkrRate()` called
2. Lock not acquired → Enters `else` branch
3. `sleep(1)` → Wait for other process
4. `Cache::get('exchange_rate_usd_pkr')` throws exception
5. Exception caught → `$cached = null`
6. Log: `error` - "Cache get failed for USD to PKR rate" with context:
   ```php
   [
       'error' => 'Redis connection failed',
   ]
   ```
7. Cache check: `$cached === null` → Falls through
8. Default rate `278` retrieved from config
9. `Cache::put('exchange_rate_usd_pkr', 278, 300)` attempted (wrapped in try-catch)
10. Returns: `278.0`

**Result:** ✅ **PASS**
- Cache failure handled gracefully
- Default rate returned
- System remains functional

---

### Scenario 6: Config Bounds Override (pkr_max = 500, Rate = 600)
**Setup:**
- `.env`: `USD_PKR_MAX=500`
- Config loaded: `pkr_max = 500`
- API returns: `PKR = 600`
- Cache: Empty

**Execution:**
1. `fetchUsdToPkrRate()` called
2. `$pkrMax = config('crypto.rates.pkr_max', 10000)` → Returns `500`
3. Lock acquired → API fetch succeeds
4. Rate `600` validated: `600 >= 100 && 600 <= 500` ❌ (fails max check)
5. Validation fails → **No cache write**
6. Log: `warning` - "Invalid USD to PKR rate from exchangerate-api.com: 600 (must be >= 100 and <= 500)" with context:
   ```php
   [
       'rate' => 600,
       'type' => 'double',
       'min'  => 100,
       'max'  => 500,
   ]
   ```
7. Flow continues to default fallback
8. Default rate `278` cached for 300 seconds
9. Returns: `278.0`

**Result:** ✅ **PASS**
- Config override respected (`pkr_max = 500`)
- Rate `600` correctly rejected
- Warning message shows correct bounds (`<= 500`)

---

### Scenario 7: Local Environment Caching
**Setup:**
- Environment: `local`
- Config: `usd_pkr = 278`
- Cache: Empty

**Execution:**
1. `fetchUsdToPkrRate()` called
2. `app()->environment('local')` → `true`
3. `$defaultRate = config('crypto.rates.usd_pkr', 278)` → `278`
4. `Cache::put('exchange_rate_usd_pkr', 278, 300)` attempted
5. Cache succeeds (or fails gracefully with try-catch)
6. Returns: `278.0`

**Result:** ✅ **PASS**
- Local env returns default without API call
- Default cached for 300 seconds (consistency with production fallback)
- Cache failure handled gracefully

---

### Scenario 8: AED Rate - Invalid (Too High: 15)
**Setup:**
- API returns: `AED = 15`
- Config: `aed_min = 1`, `aed_max = 10`
- Cache: Empty

**Execution:**
1. `fetchUsdToAedRate()` called
2. Lock acquired → API fetch succeeds
3. Rate `15` validated: `15 >= 1 && 15 <= 10` ❌ (fails max check)
4. Validation fails → **No cache write**
5. Log: `warning` - "Invalid USD to AED rate from exchangerate-api.com: 15 (must be >= 1 and <= 10)" with context:
   ```php
   [
       'rate' => 15,
       'type' => 'double',
       'min'  => 1,
       'max'  => 10,
   ]
   ```
6. Flow continues to default fallback
7. Default rate `3.67` cached for 300 seconds
8. Returns: `3.67`

**Result:** ✅ **PASS**
- Invalid AED rate rejected
- Clear warning logged
- Graceful fallback

---

## Summary

| Scenario | Rate | Bounds | Cache Put | Cache Get | Result |
|----------|------|--------|-----------|-----------|--------|
| 1. Valid Rate | 300 | 100-10000 | ✅ Success | ✅ Success | ✅ PASS |
| 2. Too High (15000) | 15000 | 100-10000 | ❌ Rejected | N/A | ✅ PASS |
| 3. Too Low (50) | 50 | 100-10000 | ❌ Rejected | N/A | ✅ PASS |
| 4. Cache Put Exception | 300 | 100-10000 | ❌ Exception | N/A | ✅ PASS |
| 5. Cache Get Exception | N/A | 100-10000 | ✅ Default | ❌ Exception | ✅ PASS |
| 6. Config Override (600 > 500) | 600 | 100-500 | ❌ Rejected | N/A | ✅ PASS |
| 7. Local Env | 278 | N/A | ✅ Default | N/A | ✅ PASS |
| 8. AED Too High (15) | 15 | 1-10 | ❌ Rejected | N/A | ✅ PASS |

## Key Observations

1. **Validation Works:** All invalid rates (too high/low) are rejected with clear warnings
2. **Cache Resilience:** Cache exceptions are caught and logged, but don't break the flow
3. **Config Override:** Custom bounds from `.env` are respected
4. **Local Env:** Defaults are cached for consistency
5. **Graceful Degradation:** System always returns a valid rate, even when APIs/cache fail
6. **Logging:** All errors and warnings include context (rate, bounds, error messages)

## Reminder

After updating `.env` or `config/crypto.php`, run:
```bash
php artisan config:cache
```

This ensures Laravel picks up the new configuration values.

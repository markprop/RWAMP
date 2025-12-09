# Fix Corrupted Recovery Codes

## Problem
If you're getting "The payload is invalid" errors when accessing the dashboard, it means your 2FA recovery codes in the database are corrupted (likely due to an APP_KEY change or data corruption).

## Solution

### Option 1: Regenerate Recovery Codes (Recommended)
1. Log in to your admin account
2. Go to: `https://dev.rwamp.net/admin/2fa/setup`
3. Click "Regenerate Recovery Codes"
4. Save the new codes securely

### Option 2: Clear Corrupted Recovery Codes via Database
If you can't access the dashboard, you can clear the corrupted codes directly:

**Using Laravel Tinker:**
```bash
php artisan tinker
```

Then run:
```php
$user = App\Models\User::find(5); // Replace 5 with your user ID
$user->two_factor_recovery_codes = null;
$user->save();
exit
```

**Or using SQL directly:**
```sql
UPDATE users SET two_factor_recovery_codes = NULL WHERE id = 5;
```

### Option 3: Disable and Re-enable 2FA
1. If you can access the dashboard, disable 2FA
2. Then re-enable it to generate fresh recovery codes

## Prevention
- **Never change APP_KEY** after 2FA is enabled unless you're prepared to regenerate all recovery codes
- Always save recovery codes in a secure location when first enabling 2FA
- If you must change APP_KEY, regenerate all 2FA recovery codes for all users

## Current Fix
The dashboard view has been updated to handle corrupted recovery codes gracefully. It will:
- Catch the DecryptException
- Log a warning
- Display a message asking you to regenerate recovery codes
- Not crash the dashboard


# WalletConnect Button Disabled - Troubleshooting Guide

## Issue: "Connect Wallet" Button is Disabled

If the "Connect Wallet" button is disabled, here's how to diagnose and fix the issue.

## Quick Diagnosis

The button will be **enabled** when ALL of these conditions are met:
1. ✅ Token quantity ≥ 100
2. ✅ A payment network is selected (ERC20, BEP20, or TRC20)
3. ✅ WalletConnect is enabled in configuration

## Step-by-Step Troubleshooting

### 1. Check Token Quantity
- **Requirement:** Minimum 100 tokens
- **Fix:** Enter at least 100 in the token quantity field
- **Visual Check:** The debug info box (in local environment) will show your current token quantity

### 2. Check Network Selection
- **Requirement:** A network must be selected
- **Fix:** Click on one of the network options:
  - ERC20 (Ethereum) - Blue icon
  - BEP20 (BNB Chain) - Yellow icon (Recommended)
  - TRC20 (Tron) - Green icon
- **Visual Check:** Selected network will have a red border

### 3. Check WalletConnect Configuration

#### Check `.env` File
Open your `.env` file and verify these settings:

```env
# Enable WalletConnect (should be true)
WALLETCONNECT_ENABLED=true

# WalletConnect Project ID (required)
WALLETCONNECT_PROJECT_ID=your_project_id_here

# Enable payments (optional for wallet connection, required for payments)
CRYPTO_PAYMENTS_ENABLED=true
```

#### Get WalletConnect Project ID
1. Go to [WalletConnect Cloud](https://cloud.walletconnect.com/)
2. Sign up or log in
3. Create a new project
4. Copy your Project ID
5. Add it to your `.env` file

### 4. Clear Configuration Cache

After updating `.env`, clear Laravel's config cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Check Browser Console

Open your browser's developer console (F12) and look for:
- Any JavaScript errors
- Debug messages showing why the button is disabled
- WalletConnect initialization errors

### 6. Verify WalletConnect Script Loading

Check if WalletConnect is loading properly:
1. Open browser console (F12)
2. Type: `window.walletConnectLoaded`
3. Should return `true` if loaded correctly
4. Type: `window.walletConnectModal`
5. Should return an object if initialized

## Common Issues and Solutions

### Issue: "WalletConnect is currently disabled"
**Solution:**
1. Check `.env` file has `WALLETCONNECT_ENABLED=true`
2. Run `php artisan config:clear`
3. Refresh the page

### Issue: "Payments are currently disabled"
**Note:** This won't prevent wallet connection anymore, but will prevent payments.
**Solution:**
1. Set `CRYPTO_PAYMENTS_ENABLED=true` in `.env`
2. Run `php artisan config:clear`
3. Refresh the page

### Issue: Button still disabled after all checks
**Solution:**
1. Check the debug info box (visible in local environment)
2. Look at browser console for errors
3. Verify all three requirements are met:
   - Token quantity ≥ 100
   - Network selected
   - WalletConnect enabled

### Issue: WalletConnect modal doesn't open
**Possible causes:**
1. WalletConnect Project ID is missing or invalid
2. CDN is blocked (check browser console)
3. Network connectivity issues

**Solution:**
1. Verify `WALLETCONNECT_PROJECT_ID` is set correctly
2. Check browser console for CDN loading errors
3. Try a different network or disable ad blockers

## Debug Mode

In local development, a debug info box will appear showing:
- Current token quantity
- Selected network
- WalletConnect enabled status
- Payments disabled status
- Can connect status

Use this to quickly identify what's preventing the button from being enabled.

## Testing Checklist

Before reporting an issue, verify:

- [ ] Token quantity is 100 or more
- [ ] A network is selected (ERC20, BEP20, or TRC20)
- [ ] `.env` has `WALLETCONNECT_ENABLED=true`
- [ ] `.env` has `WALLETCONNECT_PROJECT_ID` set
- [ ] Config cache is cleared (`php artisan config:clear`)
- [ ] Browser console shows no errors
- [ ] WalletConnect script is loaded (`window.walletConnectLoaded === true`)

## Still Having Issues?

If the button is still disabled after following all steps:

1. **Check the debug info box** (in local environment) - it shows exactly why the button is disabled
2. **Check browser console** - look for JavaScript errors or debug messages
3. **Verify configuration** - double-check your `.env` file
4. **Clear all caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

## Configuration Reference

### Required for Wallet Connection:
```env
WALLETCONNECT_ENABLED=true
WALLETCONNECT_PROJECT_ID=your_project_id_here
```

### Required for Payments:
```env
CRYPTO_PAYMENTS_ENABLED=true
WALLETCONNECT_ENABLED=true
WALLETCONNECT_PROJECT_ID=your_project_id_here
```

### Optional (for static payment method):
```env
STATIC_PAYMENT_DISABLED=true  # Set to true to disable old static address method
```

## Support

If you continue to experience issues:
1. Check the browser console for specific error messages
2. Verify your WalletConnect Project ID is valid
3. Ensure your Laravel application is up to date
4. Check that all required dependencies are installed


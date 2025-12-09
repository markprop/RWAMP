# Fix .env File - Missing Configuration

## Issues Found

Your `.env` file is missing these critical settings:

1. ❌ `WALLETCONNECT_ENABLED=true` - **MISSING**
2. ❌ `CRYPTO_PAYMENTS_ENABLED=true` - **MISSING**
3. ❌ `STATIC_PAYMENT_DISABLED=true` - **MISSING** (optional but recommended)
4. ❌ `CRYPTO_WALLET_BEP20` - **MISSING** (optional, will use ERC20 as fallback)

## Quick Fix - Option 1: Use PowerShell Script

1. Open PowerShell in the `rwamp-laravel` directory
2. Run: `.\fix-env.ps1`
3. The script will:
   - Create a backup of your .env file
   - Add the missing configuration
   - Show you what was added

## Quick Fix - Option 2: Manual Edit

Open your `.env` file and add these lines:

### Step 1: Add WALLETCONNECT_ENABLED

Find this line (around line 60):
```
WALLETCONNECT_PROJECT_ID=2dca6a12384e7e6ba4380c665ee820d9
```

Add this line **right after it**:
```
WALLETCONNECT_ENABLED=true
```

### Step 2: Add Payment Configuration

Find this line (around line 68):
```
TRONGRID_API_KEY=c2bb8d81-ef43-4237-922e-a195029b7b87
```

Add these lines **right after it**:
```
# Crypto Payments Configuration
CRYPTO_PAYMENTS_ENABLED=true
STATIC_PAYMENT_DISABLED=true
CRYPTO_WALLET_BEP20=0x3fB2e0f8C575eee0a3dE43cC3B69288644cD7B03
```

## Complete Section Should Look Like:

```env
# WalletConnect (optional – if missing, MetaMask/manual only)
WALLETCONNECT_PROJECT_ID=2dca6a12384e7e6ba4380c665ee820d9
WALLETCONNECT_ENABLED=true

# Crypto Wallets – replace before production
# ⚠️ NEVER COMMIT REAL ADDRESSES
CRYPTO_WALLET_TRC20=0x3fB2e0f8C575eee0a3dE43cC3B69288644cD7B03
CRYPTO_WALLET_ERC20=0x3fB2e0f8C575eee0a3dE43cC3B69288644cD7B03
CRYPTO_WALLET_BTC=0x3fB2e0f8C575eee0a3dE43cC3B69288644cD7B03
ETHERSCAN_API_KEY=GWUV2XQPC7BMI93M874B8KCUBCFBT29MQ6
TRONGRID_API_KEY=c2bb8d81-ef43-4237-922e-a195029b7b87

# Crypto Payments Configuration
CRYPTO_PAYMENTS_ENABLED=true
STATIC_PAYMENT_DISABLED=true
CRYPTO_WALLET_BEP20=0x3fB2e0f8C575eee0a3dE43cC3B69288644cD7B03

# Rates
RWAMP_USD_RATE=0.011
USD_PKR_RATE=278
```

## After Making Changes

1. **Clear Laravel config cache:**
   ```bash
   php artisan config:clear
   ```

2. **Clear application cache:**
   ```bash
   php artisan cache:clear
   ```

3. **Clear view cache:**
   ```bash
   php artisan view:clear
   ```

4. **Refresh your browser** (hard refresh: Ctrl+F5 or Cmd+Shift+R)

## Verify It's Working

1. Go to the purchase page
2. Enter 100+ tokens
3. Select a network (BEP20, ERC20, or TRC20)
4. Check the debug info box (in local environment)
5. The "Connect Wallet" button should now be **enabled**

## What Each Setting Does

- **WALLETCONNECT_ENABLED=true**: Enables WalletConnect integration
- **CRYPTO_PAYMENTS_ENABLED=true**: Enables crypto payment functionality
- **STATIC_PAYMENT_DISABLED=true**: Disables old static address method (use WalletConnect instead)
- **CRYPTO_WALLET_BEP20**: BNB Chain wallet address (optional, uses ERC20 as fallback if not set)

## Still Having Issues?

If the button is still disabled after these changes:

1. Check the debug info box on the purchase page
2. Open browser console (F12) and check for errors
3. Verify all settings are saved correctly in .env
4. Make sure you cleared all caches
5. Restart your Laravel development server


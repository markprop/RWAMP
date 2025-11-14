# Crypto Purchase Setup Guide

## Required Environment Variables

To enable the crypto purchase functionality, you need to configure the following environment variables in your `.env` file:

### Token Pricing
```env
RWAMP_USD_RATE=0.011
USD_PKR_RATE=278
BTC_USD_RATE=60000
```

### Crypto Wallet Addresses
```env
# Replace these with your actual wallet addresses
CRYPTO_WALLET_TRC20=Your_TRC20_USDT_Wallet_Address_Here
CRYPTO_WALLET_ERC20=Your_ERC20_USDT_Wallet_Address_Here
CRYPTO_WALLET_BTC=Your_BTC_Wallet_Address_Here
```

## Setup Instructions

1. **Copy the example configuration:**
   ```bash
   cp .env.example .env
   ```

2. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

3. **Update wallet addresses:**
   - Replace the placeholder wallet addresses with your actual crypto wallet addresses
   - Ensure addresses are valid for the respective networks (TRC20, ERC20, BTC)

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

## Features Implemented

### 1. Enhanced Purchase Flow
- **Step 1:** Amount calculator with bonus tiers
- **Step 2:** Payment method selection with QR codes
- **Step 3:** Payment proof submission

### 2. QR Code Generation
- Dynamic QR code generation for wallet addresses
- Cached QR codes for performance
- Support for all three networks (TRC20, ERC20, BTC)

### 3. Bonus System
- Automatic bonus calculation based on purchase amount
- Visual bonus tier indicators
- Configurable bonus percentages

### 4. Security Features
- Manual admin approval for all payments
- Network-specific transaction hash validation
- Secure wallet address handling

### 5. User Experience
- Modern 3-step purchase flow
- Interactive step progress indicator
- Mobile-responsive design
- Copy-to-clipboard functionality

## Testing

1. **Development Mode:**
   - The system shows a warning when placeholder wallet addresses are detected
   - Replace placeholders with real addresses for testing

2. **Production Mode:**
   - Ensure all wallet addresses are properly configured
   - Test QR code generation for all networks
   - Verify payment submission flow

## Admin Approval

All crypto payments require manual admin approval:
- Payments are stored with `status = 'pending'`
- Admins can approve/reject payments via the admin dashboard
- Tokens are only credited upon admin approval

## Support

For technical support or questions about the crypto purchase system, please refer to the main documentation or contact the development team.

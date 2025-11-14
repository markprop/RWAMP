# Auto-Credit Crypto Payment System

## Overview

The RWAMP Laravel project has been upgraded from manual crypto approval to a real-time, WalletConnect-powered auto-credit system. This system automatically detects incoming crypto payments and credits RWAMP tokens to user accounts without manual intervention.

## Key Features

### 1. **WalletConnect v2 Integration**
- Modern wallet connection modal
- Support for MetaMask, Trust Wallet, Coinbase Wallet, and more
- Automatic wallet address detection and storage
- Real-time connection status

### 2. **Real-Time Payment Monitoring**
- Background job monitors admin wallets every 2 minutes
- Supports Ethereum (ERC20 USDT), Tron (TRC20 USDT), and Bitcoin
- Automatic transaction verification and confirmation
- Duplicate payment prevention using transaction hashes

### 3. **Auto-Credit System**
- Automatic token calculation: `amount_usd / 0.011`
- Instant token crediting upon payment confirmation
- Email notifications for successful payments
- Transaction logging for audit trails

## Technical Implementation

### Dependencies
```bash
composer require guzzlehttp/guzzle
```

### Environment Configuration
```env
# Pricing
RWAMP_USD_RATE=0.011
USD_PKR_RATE=278

# Admin Wallets (hot wallets for receiving)
CRYPTO_WALLET_TRC20=TYourRealTronAddress
CRYPTO_WALLET_ERC20=0xYourRealEthereumAddress
CRYPTO_WALLET_BTC=bc1YourRealBitcoinAddress

# API Keys
ALCHEMY_API_KEY=your_alchemy_key
TRONGRID_API_KEY=your_trongrid_key
WALLETCONNECT_PROJECT_ID=your_walletconnect_project_id
```

### Core Services

#### 1. CryptoPaymentVerifier
- **Location**: `app/Services/CryptoPaymentVerifier.php`
- **Purpose**: Monitors admin wallets and processes incoming payments
- **Methods**:
  - `monitorWallets()`: Main monitoring function
  - `monitorEthereumWallet()`: Ethereum USDT monitoring
  - `monitorTronWallet()`: Tron USDT monitoring
  - `monitorBitcoinWallet()`: Bitcoin monitoring

#### 2. MonitorCryptoPayments Command
- **Location**: `app/Console/Commands/MonitorCryptoPayments.php`
- **Purpose**: Scheduled command for payment monitoring
- **Schedule**: Every 2 minutes
- **Usage**: `php artisan crypto:monitor`

### Database Changes

#### Removed Tables
- `crypto_payments` - No longer needed for manual approval

#### Updated Tables
- `users` - Added `wallet_address` field for WalletConnect integration
- `transactions` - Enhanced for crypto payment logging

### API Endpoints

#### New Endpoints
- `POST /api/save-wallet-address` - Save connected wallet address
- `POST /api/check-payment-status` - Check payment confirmation status

#### Removed Endpoints
- `POST /purchase` - Manual proof submission (replaced by WalletConnect)

## User Experience Flow

### 1. **Purchase Process**
1. User enters token quantity (with number formatting)
2. User clicks "Connect Wallet" button
3. WalletConnect modal opens for wallet selection
4. User selects network (TRC20, ERC20, BTC)
5. System shows payment address and QR code
6. User sends payment from their wallet
7. System automatically detects and credits tokens

### 2. **Payment Detection**
1. Background job monitors admin wallets every 2 minutes
2. Detects incoming USDT/BTC transactions
3. Validates transaction details and confirmations
4. Calculates RWAMP tokens: `usd_amount / 0.011`
5. Credits user account automatically
6. Sends email notification
7. Logs transaction for audit

## Security Features

### 1. **Transaction Validation**
- Network-specific hash pattern validation
- Minimum confirmation requirements:
  - Ethereum: 1 confirmation
  - Tron: 1 confirmation
  - Bitcoin: 3 confirmations
- Duplicate payment prevention using transaction hashes

### 2. **Rate Limiting**
- Wallet connection attempts are rate-limited
- API endpoints have appropriate throttling

### 3. **Contract Validation**
- Validates USDT contract addresses:
  - Ethereum: `0xdAC17F958D2ee523a2206206994597C13D831ec7`
  - Tron: `TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t`

## Monitoring and Maintenance

### 1. **Scheduled Tasks**
```bash
# Add to crontab for production
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. **Logging**
- All payment processing is logged
- Error handling with detailed logging
- Transaction audit trails

### 3. **Email Notifications**
- Payment confirmation emails
- Error notifications for failed processing

## Testing

### 1. **Testnet Setup**
Before production deployment:
1. Set up testnet wallets
2. Configure testnet API keys
3. Test with small amounts
4. Verify auto-credit functionality

### 2. **Production Checklist**
- [ ] API keys configured
- [ ] Admin wallets set up
- [ ] WalletConnect project ID configured
- [ ] Scheduled tasks running
- [ ] Email notifications working
- [ ] Monitoring and logging active

## Troubleshooting

### Common Issues

1. **WalletConnect Not Loading**
   - Check WalletConnect project ID
   - Verify CDN connection
   - Check browser console for errors

2. **Payments Not Detected**
   - Verify API keys are correct
   - Check admin wallet addresses
   - Review background job logs
   - Ensure scheduled tasks are running

3. **Tokens Not Credited**
   - Check transaction confirmation requirements
   - Verify user wallet address is correct
   - Review payment verification logs

### Debug Commands
```bash
# Test payment monitoring
php artisan crypto:monitor

# Check scheduled tasks
php artisan schedule:list

# View logs
tail -f storage/logs/laravel.log
```

## Migration from Manual System

### 1. **Data Migration**
- Existing pending payments should be processed manually
- User wallet addresses need to be collected
- Transaction history should be preserved

### 2. **Deployment Steps**
1. Deploy code changes
2. Run database migrations
3. Configure environment variables
4. Set up scheduled tasks
5. Test with small amounts
6. Monitor for 24 hours
7. Full production deployment

## Support

For technical support or questions about the auto-credit system:
- Check logs in `storage/logs/laravel.log`
- Review transaction records in database
- Contact development team for complex issues

## Future Enhancements

- Support for additional cryptocurrencies
- Real-time WebSocket notifications
- Advanced fraud detection
- Multi-signature wallet support
- Enhanced analytics and reporting

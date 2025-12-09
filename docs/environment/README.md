# Environment Configuration Documentation

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=06D6A0&center=true&vCenter=true&width=600&lines=Environment+Setup+%26+Configuration" alt="Environment Header" />
</p>

This directory contains environment configuration and setup instructions for the RWAMP platform.

## 📄 Documents

- **ENV-FIX-INSTRUCTIONS.md** - Environment variable fix instructions and troubleshooting (3.3KB)

## ⚙️ Environment Setup

### Configuration Files
- **`.env`** - Main environment file (not in repository)
- **`.env.example`** - Example environment file with all variables

### Key Environment Variables

#### Application
```env
APP_NAME=RWAMP
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://rwamp.io
```

#### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rwamp_laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=hello@rwamp.io
MAIL_FROM_NAME="RWAMP"
```

#### Crypto Payments
```env
CRYPTO_PAYMENTS_ENABLED=true
WALLETCONNECT_ENABLED=true
WALLETCONNECT_PROJECT_ID=your_project_id
STATIC_PAYMENT_DISABLED=true

# Wallet Addresses
CRYPTO_WALLET_TRC20=your_trc20_wallet
CRYPTO_WALLET_ERC20=your_erc20_wallet
CRYPTO_WALLET_BEP20=your_bep20_wallet
CRYPTO_WALLET_BTC=your_btc_wallet

# API Keys
ETHERSCAN_API_KEY=your_etherscan_key
TRONGRID_API_KEY=your_trongrid_key
ALCHEMY_API_KEY=your_alchemy_key
```

#### Security & Analytics
```env
# reCAPTCHA v3
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key
RECAPTCHA_MIN_SCORE=0.5

# Analytics (Optional)
GOOGLE_ANALYTICS_ID=your_ga_id
META_PIXEL_ID=your_pixel_id
ADMIN_EMAIL=admin@rwamp.io
```

#### Broadcasting (Pusher)
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
PUSHER_APP_ENCRYPTED=true
```

## 🔧 Troubleshooting

Follow **ENV-FIX-INSTRUCTIONS.md** for:
- ✅ Common environment issues
- ✅ Configuration fixes
- ✅ Variable validation
- ✅ Setup verification
- ✅ Production configuration

### Common Issues

#### Application Key
```bash
# Generate application key
php artisan key:generate
```

#### Configuration Cache
```bash
# Clear configuration cache
php artisan config:clear

# Cache configuration (production)
php artisan config:cache
```

#### Environment Detection
```bash
# Check current environment
php artisan env

# Verify .env file exists
ls -la .env
```

## 🔒 Security Considerations

### Production Environment
- ✅ Set `APP_ENV=production`
- ✅ Set `APP_DEBUG=false`
- ✅ Use strong `APP_KEY`
- ✅ Secure database credentials
- ✅ Use HTTPS (`APP_URL=https://...`)
- ✅ Protect `.env` file (not in repository)

### Sensitive Data
- ✅ Never commit `.env` file
- ✅ Use strong passwords
- ✅ Rotate API keys regularly
- ✅ Secure database access
- ✅ Protect mail credentials

## 📋 Environment Checklist

### Initial Setup
- [ ] Copy `.env.example` to `.env`
- [ ] Generate `APP_KEY`
- [ ] Configure database credentials
- [ ] Set mail configuration
- [ ] Configure crypto payment settings
- [ ] Add reCAPTCHA keys
- [ ] Configure Pusher (if using)
- [ ] Set production environment variables

### Production Deployment
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure production database
- [ ] Setup SSL (HTTPS)
- [ ] Configure production mail
- [ ] Verify all API keys
- [ ] Test all configurations

## 📚 Related Documentation

- **Main README**: [`../../README.md`](../../README.md)
- **Deployment**: [`../deployment/DEPLOYMENT_GUIDE.md`](../deployment/DEPLOYMENT_GUIDE.md)
- **Database**: [`../database/DATABASE_SETUP_GUIDE.md`](../database/DATABASE_SETUP_GUIDE.md)
- **Fixes**: [`../fixes/`](../fixes/) - Environment troubleshooting

## 🔗 Support

- **Website**: [rwamp.io](https://rwamp.io)
- **Email**: info@rwamp.io
- **Phone**: +92 370 1346038

---

**Last Updated:** January 27, 2025

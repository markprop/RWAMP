<div align="center">

# 🏢 RWAMP
## The Currency of Real Estate Investments

**A modern, secure, and feature-rich Laravel application for real estate tokenization and investment management**

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.3+-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.13+-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=for-the-badge)](LICENSE)

[Website](https://rwamp.net) • [Documentation](#-documentation) • [Support](#-support)

---

</div>

## ✨ Table of Contents

- [🌟 Features](#-features)
- [🛠️ Tech Stack](#️-tech-stack)
- [📦 Installation](#-installation)
- [⚙️ Configuration](#️-configuration)
- [🏗️ Project Structure](#️-project-structure)
- [🔐 Authentication & Security](#-authentication--security)
- [💳 Crypto Payments](#-crypto-payments)
- [👥 User Roles](#-user-roles)
- [📚 Documentation](#-documentation)
- [🚀 Deployment](#-deployment)
- [🤝 Contributing](#-contributing)

---

## 🌟 Features

<div align="center">

### 🎯 Core Features

</div>

| Category | Features |
|----------|----------|
| 🎨 **Frontend** | Server-side rendered Blade templates • Modern UI with TailwindCSS • Reactive components with Alpine.js • Fully responsive design • Smooth animations |
| 🔐 **Security** | Admin 2FA (Laravel Fortify) • CSRF protection • Rate limiting • Honeypot fields • Security headers (CSP) • Input validation • SQL injection prevention |
| 💰 **Payments** | Crypto payment integration (USDT, BTC) • WalletConnect support • QR code generation • Automated transaction monitoring • Manual admin approval |
| 👤 **User Management** | Role-based access control • KYC verification • Email verification • Password reset • Profile management |
| 📊 **Dashboards** | Investor dashboard • Reseller dashboard • Admin dashboard • Real-time metrics • Transaction history |
| 📧 **Communication** | Email notifications • Contact forms • Newsletter subscriptions • Reseller applications • OTP verification |
| 🔍 **SEO** | Dynamic sitemap • Meta tags • Open Graph • Twitter cards • JSON-LD structured data • robots.txt |
| 📱 **Mobile** | Fully responsive • Touch-friendly • Progressive Web App ready • Optimized performance |

---

## 🛠️ Tech Stack

<div align="center">

### 🎨 Frontend Technologies

</div>

| Technology | Version | Purpose |
|------------|---------|---------|
| **Blade** | Laravel 10+ | Server-side templating |
| **TailwindCSS** | 3.3+ | Utility-first CSS framework |
| **Alpine.js** | 3.13+ | Lightweight JavaScript framework |
| **Vite** | 4.0+ | Next-generation frontend tooling |

<div align="center">

### ⚙️ Backend Technologies

</div>

| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 10.x | PHP framework |
| **PHP** | 8.1+ | Programming language |
| **MySQL/SQLite** | Latest | Database |
| **Laravel Fortify** | 1.31+ | Authentication & 2FA |
| **Laravel Sanctum** | 3.2+ | API authentication |
| **Guzzle HTTP** | 7.10+ | HTTP client for APIs |
| **QR Code** | 6.0+ | QR code generation |

---

## 📦 Installation

<div align="center">

### 🚀 Quick Start Guide

</div>

### Prerequisites

- **PHP** >= 8.1 with extensions: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- **Composer** >= 2.0
- **Node.js** >= 16.x and **npm** >= 8.x
- **MySQL** >= 5.7 or **SQLite** >= 3.8

### Step-by-Step Installation

<details>
<summary><b>📥 Step 1: Clone Repository</b></summary>

```bash
git clone https://github.com/markprop/RWAMP.git
cd RWAMP
```

</details>

<details>
<summary><b>📦 Step 2: Install Dependencies</b></summary>

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

</details>

<details>
<summary><b>⚙️ Step 3: Environment Configuration</b></summary>

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

</details>

<details>
<summary><b>🗄️ Step 4: Database Setup</b></summary>

```bash
# Run migrations
php artisan migrate

# (Optional) Seed database with admin user
php artisan db:seed
```

</details>

<details>
<summary><b>🎨 Step 5: Build Assets</b></summary>

```bash
# For development
npm run dev

# For production
npm run build
```

</details>

<details>
<summary><b>🚀 Step 6: Start Development Server</b></summary>

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

</details>

---

## ⚙️ Configuration

### 🔑 Environment Variables

Create a `.env` file from `.env.example` and configure the following:

<details>
<summary><b>📋 Basic Configuration</b></summary>

```env
APP_NAME=RWAMP
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000
```

</details>

<details>
<summary><b>🗄️ Database Configuration</b></summary>

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rwamp_laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

</details>

<details>
<summary><b>📧 Mail Configuration</b></summary>

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=hello@rwamp.com
MAIL_FROM_NAME="RWAMP"
```

</details>

<details>
<summary><b>💳 Crypto Payment Configuration</b></summary>

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

</details>

<details>
<summary><b>🔐 Security & Analytics</b></summary>

```env
# reCAPTCHA v3
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key
RECAPTCHA_MIN_SCORE=0.5

# Analytics (Optional)
GOOGLE_ANALYTICS_ID=your_ga_id
META_PIXEL_ID=your_pixel_id
ADMIN_EMAIL=admin@rwamp.com
```

</details>

### 📊 Database Tables

The application includes the following database tables:

| Table | Description |
|-------|-------------|
| `users` | User accounts with roles, 2FA, KYC, and wallet information |
| `contacts` | Contact form submissions |
| `reseller_applications` | Reseller program applications |
| `newsletter_subscriptions` | Newsletter subscribers |
| `crypto_payments` | Crypto payment records |
| `transactions` | Transaction history |
| `buy_from_reseller_requests` | Buy-from-reseller requests |
| `withdraw_requests` | Withdrawal requests |
| `processed_crypto_transactions` | Processed blockchain transactions |

---

## 🏗️ Project Structure

```
rwamp-laravel/
├── 📁 app/
│   ├── 📁 Actions/
│   │   └── 📁 Fortify/          # Fortify authentication actions
│   ├── 📁 Console/
│   │   └── 📁 Commands/         # Artisan commands
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/      # Application controllers
│   │   └── 📁 Middleware/       # Custom middleware
│   ├── 📁 Mail/                 # Mail classes
│   ├── 📁 Models/               # Eloquent models
│   ├── 📁 Providers/            # Service providers
│   └── 📁 Services/             # Business logic services
├── 📁 config/                   # Configuration files
├── 📁 database/
│   ├── 📁 migrations/           # Database migrations
│   └── 📁 seeders/              # Database seeders
├── 📁 public/                   # Public assets
├── 📁 resources/
│   ├── 📁 css/                  # Stylesheets
│   ├── 📁 js/                   # JavaScript files
│   └── 📁 views/                # Blade templates
│       ├── 📁 auth/             # Authentication views
│       ├── 📁 components/       # Reusable components
│       ├── 📁 dashboard/        # Dashboard views
│       ├── 📁 emails/           # Email templates
│       └── 📁 pages/            # Page views
├── 📁 routes/                   # Route definitions
├── 📁 storage/                  # Storage files
└── 📁 tests/                    # Test files
```

---

## 🔐 Authentication & Security

### 🔒 Role-Based Access Control

| Role | Access Level | Dashboard |
|------|--------------|-----------|
| **Investor** | Basic user access | `/dashboard/investor` |
| **Reseller** | Reseller features + investor access | `/dashboard/reseller` |
| **Admin** | Full system access (2FA required) | `/dashboard/admin` |

### 🛡️ Security Features

<div align="center">

| Feature | Implementation |
|---------|----------------|
| **2FA Authentication** | Laravel Fortify with TOTP |
| **CSRF Protection** | Laravel built-in CSRF tokens |
| **Rate Limiting** | Login (5/min), Forms (3-6/hour) |
| **Honeypot Fields** | Bot protection on forms |
| **Security Headers** | CSP, X-Frame-Options, Referrer-Policy |
| **Input Validation** | Server-side validation on all inputs |
| **SQL Injection Prevention** | Eloquent ORM parameter binding |
| **XSS Protection** | Blade automatic escaping |

</div>

### 🔐 Admin 2FA Setup

1. Login as admin user
2. Navigate to `/admin/2fa/setup`
3. Scan QR code with authenticator app
4. Save recovery codes securely
5. 2FA is now enforced for admin dashboard access

---

## 💳 Crypto Payments

### Supported Payment Methods

| Method | Networks | Status |
|--------|----------|--------|
| **USDT** | TRC20, ERC20, BEP20 | ✅ Active |
| **BTC** | Bitcoin Network | ✅ Active |
| **WalletConnect** | All networks | ✅ Active |

### Payment Flow

```
1. User initiates purchase
   ↓
2. System generates payment QR code
   ↓
3. User sends crypto payment
   ↓
4. System monitors blockchain
   ↓
5. Admin approves transaction
   ↓
6. Tokens credited to user wallet
```

### Features

- ✅ Automated transaction monitoring
- ✅ QR code generation for payments
- ✅ WalletConnect integration
- ✅ Multi-network support
- ✅ Manual admin approval
- ✅ Transaction history tracking

---

## 👥 User Roles

### 👤 Investor

**Features:**
- Purchase RWAMP tokens
- View transaction history
- Manage profile
- KYC verification
- View token balance

**Dashboard:** `/dashboard/investor`

### 🤝 Reseller

**Features:**
- All investor features
- Manage reseller applications
- View commission earnings
- Sell tokens to users
- View reseller statistics

**Dashboard:** `/dashboard/reseller`

### 👨‍💼 Admin

**Features:**
- Full system access
- User management
- KYC approval/rejection
- Crypto payment approval
- Price management
- System analytics
- 2FA required

**Dashboard:** `/dashboard/admin`

---

## 📚 Documentation

<div align="center">

### 📖 Available Documentation

</div>

| Document | Description | Link |
|----------|-------------|------|
| **Admin 2FA** | Two-factor authentication setup guide | [`docs/admin-2fa.md`](docs/admin-2fa.md) |
| **Auth & Roles** | Authentication and role management | [`docs/auth-roles.md`](docs/auth-roles.md) |
| **Crypto Setup** | Crypto payment configuration | [`docs/crypto-setup.md`](docs/crypto-setup.md) |
| **Auto Crypto System** | Automated crypto monitoring | [`docs/auto-crypto-system.md`](docs/auto-crypto-system.md) |
| **Security** | Security best practices | [`docs/security.md`](docs/security.md) |
| **SEO** | SEO optimization guide | [`docs/seo.md`](docs/seo.md) |
| **Forms & Services** | Form handling and services | [`docs/forms.md`](docs/forms.md) |
| **Database Setup** | Database configuration guide | [`DATABASE_SETUP_GUIDE.md`](DATABASE_SETUP_GUIDE.md) |
| **Migration Guide** | Migration instructions | [`MIGRATION_GUIDE.md`](MIGRATION_GUIDE.md) |
| **Reseller System** | Reseller implementation guide | [`RESELLER_SYSTEM_IMPLEMENTATION.md`](RESELLER_SYSTEM_IMPLEMENTATION.md) |

---

## 🚀 Deployment

### 🌐 Hostinger Shared Hosting

<details>
<summary><b>Click to expand deployment steps</b></summary>

1. **Upload Files**: Upload all files to `public_html`
2. **Database**: Create MySQL database and import schema
3. **Environment**: Update `.env` with production settings
4. **Assets**: Run `npm run build` and upload `public/build/`
5. **Permissions**: Set proper file permissions (755 for directories, 644 for files)
6. **Storage**: Create symlink: `php artisan storage:link`

</details>

### 🖥️ VPS/Dedicated Server

<details>
<summary><b>Click to expand deployment steps</b></summary>

1. **Server Setup**: Install PHP 8.1+, Composer, Node.js
2. **Web Server**: Configure Apache/Nginx with proper document root
3. **SSL**: Install SSL certificate (Let's Encrypt recommended)
4. **Database**: Setup MySQL/PostgreSQL
5. **Deploy**: Use Laravel Forge, Envoyer, or manual deployment
6. **Queue Worker**: Setup supervisor for queue processing
7. **Cron Jobs**: Add Laravel scheduler cron job

</details>

### 📋 Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure production database
- [ ] Setup SSL certificate
- [ ] Configure mail settings
- [ ] Run `php artisan optimize`
- [ ] Run `npm run build`
- [ ] Setup queue workers
- [ ] Configure cron jobs
- [ ] Setup backups
- [ ] Enable monitoring

---

## 🔧 Development

### 📝 Available Commands

<details>
<summary><b>Development Commands</b></summary>

```bash
# Start development servers
npm run dev          # Start Vite dev server
php artisan serve    # Start Laravel server
```

</details>

<details>
<summary><b>Production Commands</b></summary>

```bash
# Build and optimize
npm run build        # Build assets for production
php artisan optimize # Optimize for production
```

</details>

<details>
<summary><b>Database Commands</b></summary>

```bash
# Migrations
php artisan migrate        # Run migrations
php artisan migrate:fresh  # Fresh migration with seeders
php artisan migrate:rollback  # Rollback last migration

# Seeders
php artisan db:seed        # Run seeders
php artisan db:seed --class=AdminUserSeeder  # Run specific seeder
```

</details>

<details>
<summary><b>Cache Commands</b></summary>

```bash
# Clear caches
php artisan cache:clear    # Clear application cache
php artisan config:clear   # Clear configuration cache
php artisan view:clear     # Clear view cache
php artisan route:clear    # Clear route cache

# Optimize (Windows PowerShell)
php artisan config:clear; php artisan cache:clear; php artisan optimize:clear
```

</details>

### 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter TestClassName
```

---

## 📄 Public Pages

| Page | Route | Description |
|------|-------|-------------|
| **Home** | `/` | Landing page with hero, about, features |
| **About** | `/about` | About RWAMP |
| **Contact** | `/contact` | Contact form |
| **Become Partner** | `/become-partner` | Reseller application |
| **How to Buy** | `/how-to-buy` | Purchase guide |
| **Whitepaper** | `/whitepaper` | Project whitepaper |
| **Privacy Policy** | `/privacy-policy` | Privacy policy |
| **Terms of Service** | `/terms-of-service` | Terms and conditions |
| **Disclaimer** | `/disclaimer` | Legal disclaimer |
| **Purchase** | `/purchase` | Token purchase (auth required) |

---

## 🤝 Contributing

<div align="center">

We welcome contributions! Please follow these steps:

</div>

1. 🍴 **Fork** the repository
2. 🌿 **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. 💻 **Make** your changes
4. ✅ **Test** thoroughly
5. 📝 **Commit** your changes (`git commit -m 'Add some AmazingFeature'`)
6. 📤 **Push** to the branch (`git push origin feature/AmazingFeature`)
7. 🔄 **Open** a Pull Request

---

## 📄 License

<div align="center">

This project is **proprietary software** owned by **RWAMP**.

All rights reserved. © 2024 RWAMP

</div>

---

## 🆘 Support

<div align="center">

### 📞 Get Help

| Method | Contact |
|--------|---------|
| 📧 **Email** | [info@rwamp.net](mailto:info@rwamp.net) |
| 📱 **Phone** | +92 300 1234567 |
| 🌐 **Website** | [https://rwamp.net](https://rwamp.net) |

---

<div align="center">

### ⭐ Star us on GitHub if you find this project helpful!

**Made with ❤️ by the RWAMP Team**

[⬆ Back to Top](#-rwamp)

</div>

# API Documentation

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=06D6A0&center=true&vCenter=true&width=600&lines=API+Reference+%26+Endpoints" alt="API Header" />
</p>

This directory contains API documentation and endpoint references for the RWAMP platform.

## 📄 Documents

- **API_DOCUMENTATION.md** - Complete API documentation (10KB, 655 lines)

## 🔌 API Overview

The RWAMP API provides endpoints for:

### Authentication
- User registration and login
- Email verification (OTP-based)
- Password reset
- 2FA authentication (admin)

### User Management
- User profile management
- KYC submission and status
- Wallet address management
- Token balance queries

### Crypto Payments
- Payment initiation
- Payment status checking
- QR code generation
- WalletConnect integration
- Automated payment monitoring

### Transactions
- Transaction history
- Portfolio valuation
- Weighted-average pricing
- Transaction details

### Reseller Operations
- Reseller application submission
- Commission tracking
- User management (referred users)
- Payment approval workflow
- Custom pricing management

### Game System
- Game session management
- Real-time price queries
- Trade execution (buy/sell)
- Price history retrieval
- Game state management

### Chat System
- Chat creation (private/group)
- Message sending and receiving
- Media file uploads
- Message reactions and read receipts
- Chat management (pin, mute, archive)

## 📖 Documentation

See **API_DOCUMENTATION.md** for:
- ✅ Complete endpoint list with methods
- ✅ Request/response formats
- ✅ Authentication methods and tokens
- ✅ Error handling and status codes
- ✅ Rate limiting information
- ✅ Code examples and usage
- ✅ Webhook documentation (if applicable)

## 🔐 Authentication

### Methods
- **Web Routes**: Session-based authentication
- **API Routes**: Sanctum token authentication (if enabled)
- **2FA**: TOTP-based for admin routes

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## 📊 API Statistics

- **Total Endpoints**: 100+ routes
- **Public Routes**: ~15 routes
- **Protected Routes**: ~85 routes
- **Admin Routes**: ~30 routes (2FA required)
- **API Routes**: Minimal (most functionality via web routes with AJAX)

## 🎯 Key Endpoints

### Public
- `GET /` - Homepage
- `GET /about` - About page
- `POST /contact` - Contact form
- `POST /reseller` - Reseller application
- `POST /newsletter` - Newsletter subscription

### Authentication
- `POST /login` - User login
- `POST /register` - User registration
- `POST /verify-email` - Email OTP verification
- `POST /forgot-password` - Password reset request

### Protected (Auth Required)
- `GET /dashboard/investor` - Investor dashboard
- `GET /dashboard/reseller` - Reseller dashboard
- `GET /dashboard/admin` - Admin dashboard (2FA required)
- `GET /purchase` - Token purchase page
- `POST /api/user/withdraw` - Withdrawal request

### Game System
- `GET /game` - Game selection
- `GET /game/trading` - Trading interface
- `GET /game/price` - Real-time price API
- `POST /game/trade` - Execute trade

## 🔗 Related Documentation

- **Main README**: [`../../README.md`](../../README.md)
- **Features**: [`../features/`](../features/) - Feature implementation
- **Security**: [`../security.md`](../security.md) - Security documentation
- **Routes**: Check `routes/web.php` and `routes/api.php` in codebase

## 🔗 Support

- **Website**: [rwamp.io](https://rwamp.io)
- **Email**: info@rwamp.net
- **Phone**: +92 370 1346038

---

## 🔙 Navigation

<p align="center">
  <a href="../../README.md">
    <img src="https://img.shields.io/badge/⬅️%20Back%20to%20Main-FF6B6B?style=for-the-badge&logo=arrow-left&logoColor=white" alt="Back to Main" />
  </a>
  <a href="../README.md">
    <img src="https://img.shields.io/badge/📚%20Documentation%20Index-06D6A0?style=for-the-badge&logo=book&logoColor=white" alt="Documentation Index" />
  </a>
</p>

---

**Last Updated:** January 27, 2025

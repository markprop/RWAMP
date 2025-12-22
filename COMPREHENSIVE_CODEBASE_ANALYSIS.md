# Comprehensive RWAMP Laravel Codebase Analysis

## Executive Summary

**RWAMP (Real World Asset Management Platform)** is a Laravel 10-based cryptocurrency investment platform that enables users to purchase, trade, and manage RWAMP tokens. The platform supports multiple user roles (Investors, Resellers, Admins), KYC verification, crypto payments, trading games, and a sophisticated FOPI (Future of Property Investment) game system.

**Technology Stack:**
- **Framework:** Laravel 10.x
- **PHP:** 8.1+
- **Authentication:** Laravel Fortify with 2FA support
- **Frontend:** Alpine.js, Tailwind CSS, Chart.js
- **Real-time:** Pusher (currently disabled for chat)
- **Database:** MySQL/PostgreSQL
- **Crypto Integration:** Multiple networks (TRC20, ERC20, BEP20, BTC)

---

## 1. Authentication & Authorization

### 1.1 Authentication System

**Laravel Fortify Integration:**
- Custom user creation via `App\Actions\Fortify\CreateNewUser`
- Password validation rules
- Profile information updates
- Password reset functionality

**Key Features:**
- **Email Verification:** OTP-based email verification (6-digit codes)
- **Two-Factor Authentication:** Required for admin users, optional for others
- **Password Reset:** Standard Laravel password reset with email validation
- **Session Management:** Tab-based session authentication for multi-tab support

**Files:**
- `app/Http/Controllers/AuthController.php` - Main authentication logic
- `app/Http/Controllers/Auth/EmailVerificationController.php` - OTP verification
- `app/Http/Controllers/Auth/Register/RegisterController.php` - User registration
- `app/Providers/FortifyServiceProvider.php` - Fortify configuration

**Login Flow:**
1. User submits email/password with role selection (investor/reseller)
2. System validates credentials and role match
3. Email verification check (redirects to OTP if not verified)
4. Reseller default password check (forces password change)
5. Tab session authentication setup
6. Role-based dashboard redirect

**Signup Flow:**
1. User fills registration form (name, email, phone, password, referral code)
2. System validates email uniqueness and format
3. Generates unique 16-digit wallet address
4. Creates user account with 'investor' role
5. Sends OTP to email
6. Redirects to email verification page

**Special Features:**
- **Referral System:** Users can register with referral codes (format: RSL{ID})
- **Reseller Applications:** Separate registration flow for reseller applicants
- **Default Password Enforcement:** Resellers with default password must change on first login

### 1.2 Authorization & Middleware

**Role-Based Access Control:**
- **Roles:** `admin`, `reseller`, `investor`
- **Middleware:** `RoleMiddleware` - Enforces role-based access

**Key Middleware:**
1. **`EnsureKycApproved`** - Requires KYC approval for certain routes
2. **`EnsureAdminTwoFactorEnabled`** - Forces 2FA setup for admin users
3. **`EnsureNotInGame`** - Prevents access when user is in active game session
4. **`TabSessionAuth`** - Manages per-tab authentication
5. **`ForceReauthInNewTabs`** - Security for new tab sessions
6. **`SecurityHeaders`** - Adds security headers to responses
7. **`HandleExpiredCsrf`** - Graceful CSRF token expiration handling

**Route Protection:**
- Admin routes require `role:admin` + `admin.2fa` middleware
- Reseller routes require `role:reseller` middleware
- Investor routes require `role:investor` + `kyc.approved` middleware
- Game routes require `auth` + `kyc.approved` middleware

---

## 2. User Management & Profiles

### 2.1 User Model (`app/Models/User.php`)

**Key Attributes:**
- **Identity:** name, email, phone, avatar
- **Role:** admin, reseller, investor
- **Wallet:** wallet_address (16-digit unique), token_balance
- **KYC:** kyc_status, kyc_id_type, kyc_id_number, kyc_full_name, kyc document paths
- **Game:** game_pin_hash, trading_game_pin_hash, fopi_game_pin_hash, is_in_game
- **Reseller:** referral_code, reseller_id (parent reseller), coin_price
- **2FA:** two_factor_secret, two_factor_recovery_codes

**Key Methods:**
- `addTokens($amount, $description)` - Credit tokens with transaction logging
- `deductTokens($amount, $description)` - Debit tokens with validation
- `calculateBalanceFromTransactions()` - Reconcile balance from transaction history
- `reconcileBalance()` - Check balance consistency
- `setGamePin($pin)` / `verifyGamePin($pin)` - Game PIN management
- `setGameSpecificPin($pin, $gameType)` - Game-specific PINs (trading/fopi)
- `canEnterGame()` - Validates game entry prerequisites

**Relationships:**
- `transactions()` - All user transactions
- `cryptoPayments()` - Crypto payment submissions
- `gameSessions()` - Game session history
- `reseller()` - Parent reseller (if referred)
- `referredUsers()` - Users referred by this reseller
- `buyFromResellerRequests()` - Purchase requests from resellers

### 2.2 Profile Management

**Controller:** `app/Http/Controllers/ProfileController.php`

**Features:**
- View/edit profile information
- Update password
- Update wallet address
- Generate new wallet address
- Resend email verification
- Download KYC documents

**Routes:**
- `GET /profile` - View profile
- `PUT /account` - Update account info
- `PUT /account/password` - Change password
- `PUT /wallet` - Update wallet address
- `POST /wallet/generate` - Generate new wallet

---

## 3. KYC (Know Your Customer) System

### 3.1 KYC Submission

**Controller:** `app/Http/Controllers/KycController.php`

**Process:**
1. User uploads KYC documents:
   - ID Type: CNIC, NICOP, or Passport
   - ID Front Image (required)
   - ID Back Image (required for CNIC/NICOP)
   - Selfie Image (required)
2. Documents stored in `storage/app/kyc/{user_id}/`
3. User status set to `pending`
4. Admin reviews and approves/rejects

**KYC Statuses:**
- `null` - Not submitted
- `pending` - Under review
- `approved` - Verified and approved
- `rejected` - Rejected (can resubmit)

**Security:**
- Files stored in user-specific directories
- Path traversal protection
- Secure file download with MIME type validation
- User can only download their own documents

**Routes:**
- `GET /kyc` - KYC submission form
- `POST /kyc/submit` - Submit KYC documents
- `GET /profile/kyc/download/{type}` - Download own documents

### 3.2 Admin KYC Management

**Controller:** `app/Http/Controllers/Admin/AdminKycController.php`

**Features:**
- List all KYC submissions with filters
- Approve/reject KYC applications
- View/download KYC documents
- Update KYC information
- Delete KYC submissions
- Rejection reason tracking

**Routes:**
- `GET /dashboard/admin/kyc` - KYC list
- `POST /dashboard/admin/kyc/{user}/approve` - Approve KYC
- `POST /dashboard/admin/kyc/{user}/reject` - Reject KYC
- `GET /dashboard/admin/kyc/{user}/download/{type}` - Download documents

---

## 4. Payment & Purchase System

### 4.1 Crypto Payment Flow

**Controller:** `app/Http/Controllers/CryptoPaymentController.php`

**Supported Networks:**
- **TRC20** (Tron) - USDT
- **ERC20** (Ethereum) - USDT
- **BEP20** (BNB Chain) - USDT
- **BTC** (Bitcoin)

**Purchase Process:**
1. User selects network and enters amount
2. System calculates token amount based on current prices
3. User receives wallet address and QR code
4. User sends crypto payment
5. User submits transaction hash
6. Payment status: `pending` → Admin reviews → `approved`/`rejected`
7. On approval: Tokens credited to user balance

**Price Calculation:**
- Uses `PriceHelper` for current RWAMP/USDT/BTC prices
- Supports USD and PKR amounts
- Real-time exchange rate fetching

**Features:**
- QR code generation for wallet addresses
- Transaction hash validation
- Duplicate transaction prevention
- Payment status tracking
- Admin approval workflow
- Email notifications

**Routes:**
- `GET /purchase` - Purchase page
- `GET /qr-code/{network}` - Generate QR code
- `POST /api/save-wallet-address` - Save user wallet
- `POST /api/submit-tx-hash` - Submit transaction hash
- `POST /api/check-payment-status` - Check payment status

### 4.2 Manual/Bank Payment Submission

**Controller:** `app/Http/Controllers/PaymentSubmissionController.php`

**Process:**
1. User uploads payment receipt (bank transfer, cash, etc.)
2. Receipt stored securely
3. Admin reviews and approves
4. Tokens credited on approval

**Routes:**
- `GET /dashboard/payments/submit` - Submission form
- `POST /dashboard/payments/submit` - Submit payment proof
- `GET /receipts/payment/{submission}` - View receipt

### 4.3 Admin Payment Management

**Controller:** `app/Http/Controllers/Admin/AdminCryptoPaymentController.php`

**Features:**
- List all crypto payments with filters
- View payment details
- Approve/reject payments
- Download payment screenshots
- Update payment information
- ULID-based short URLs (`/a/p/{payment}/details`)

**Routes:**
- `GET /dashboard/admin/crypto-payments` - Payment list
- `GET /a/p/{payment}/details` - Payment details
- `POST /a/p/{payment}/approve` - Approve payment
- `POST /a/p/{payment}/reject` - Reject payment

---

## 5. Withdrawal System

### 5.1 User Withdrawal

**Controller:** `app/Http/Controllers/WithdrawController.php`

**Requirements:**
- KYC must be approved
- Sufficient token balance
- Valid wallet address

**Process:**
1. User submits withdrawal request with wallet address and amount
2. Tokens deducted immediately from balance
3. Request status: `pending` → Admin processes → `approved`/`rejected`
4. On approval: Admin transfers tokens to user's wallet
5. Receipt uploaded by admin

**Security:**
- Immediate balance deduction prevents double-spending
- KYC verification required
- Balance validation before processing

**Routes:**
- `POST /api/user/withdraw` - Submit withdrawal request
- `GET /api/user/withdrawals` - List withdrawals
- `GET /api/user/withdrawals/{withdrawal}/receipt` - View receipt

### 5.2 Admin Withdrawal Management

**Controller:** `app/Http/Controllers/Admin/AdminWithdrawalController.php`

**Features:**
- List all withdrawal requests
- Approve/reject withdrawals
- Upload transfer receipts
- Update withdrawal information
- Track transaction hashes

**Routes:**
- `GET /dashboard/admin/withdrawals` - Withdrawal list
- `GET /a/w/{withdrawal}` - Withdrawal details
- `POST /a/w/{withdrawal}/approve` - Approve withdrawal
- `POST /a/w/{withdrawal}/reject` - Reject withdrawal
- `POST /a/w/{withdrawal}/submit-receipt` - Upload receipt

---

## 6. Reseller System

### 6.1 Reseller Application

**Model:** `app/Models/ResellerApplication.php`

**Application Process:**
1. User submits reseller application (separate from user registration)
2. Application stored with status `pending`
3. Admin reviews application
4. On approval: User account created with `reseller` role
5. Default password: `RWAMP@agent` (must be changed on first login)
6. Referral code generated: `RSL{user_id}`

**Fields:**
- name, email, phone, password (hashed)
- company_name, investment_capacity, experience
- status: pending, approved, rejected

**Controller:** `app/Http/Controllers/ResellerController.php`

### 6.2 Reseller Dashboard

**Controller:** `app/Http/Controllers/Reseller/ResellerDashboardController.php`

**Features:**
- View referred users
- Manage user payments
- Approve/reject buy requests
- Sell tokens to users
- Set custom coin price
- View transaction history

**Key Functionality:**
- **User Management:** View users referred by reseller
- **Payment Approval:** Approve/reject crypto and bank payments
- **Sell Tokens:** Sell tokens to users with OTP verification
- **Buy Requests:** Approve/reject user buy requests
- **Price Management:** Set custom coin price (markup on base price)

**Routes:**
- `GET /dashboard/reseller` - Dashboard
- `GET /dashboard/reseller/users` - User list
- `GET /dashboard/reseller/payments` - Payment list
- `GET /dashboard/reseller/sell` - Sell tokens page
- `POST /dashboard/reseller/sell` - Execute sell
- `GET /dashboard/reseller/buy-requests` - Buy request list

### 6.3 Buy from Reseller

**Controller:** `app/Http/Controllers/BuyFromReseller/BuyFromResellerController.php`

**Process:**
1. User searches for resellers
2. User creates buy request with OTP verification
3. Reseller approves/rejects request
4. On approval: Tokens transferred from reseller to user
5. Markup applied (5% default)

**Model:** `app/Models/BuyFromResellerRequest.php`

**Routes:**
- `GET /api/user/buy-from-reseller` - Buy page
- `POST /api/user/buy-from-reseller/request` - Create request
- `POST /api/user/buy-from-reseller/send-otp` - Send OTP

---

## 7. Trading Game System

### 7.1 Game Overview

**Two Game Types:**
1. **Trading Game** - Real-time trading simulation with BTC-anchored pricing
2. **FOPI Game** - Property investment simulation game

### 7.2 Trading Game

**Controller:** `app/Http/Controllers/GameController.php`
**Service:** `app/Services/GamePriceEngine.php`

**Game Mechanics:**
- User stakes RWAMP tokens to enter game
- Staked amount locked, game balance = stake × 10
- Prices anchored to BTC/USD at session start
- Real-time price updates based on BTC movement
- Buy/Sell orders with fees and spread
- Exit game: Convert game balance back to RWAMP (÷100 multiplier)

**Price Calculation:**
- Mid price anchored to BTC/USD movement
- Buy price = mid + spread/2
- Sell price = mid - spread/2
- Fees: Buy fee (1%) and Sell fee (1%)
- Spread revenue goes to platform

**Session Management:**
- `GameSession` model tracks active sessions
- `GameTrade` model logs all trades
- `GamePriceHistory` tracks price changes
- Session state: `active`, `completed`, `abandoned`

**Game PIN:**
- 4-digit PIN required to enter game
- Separate PINs for trading and FOPI games
- Locked after 3 failed attempts (5 minutes)

**Routes:**
- `GET /game/select` - Game selection page
- `GET /game/trading` - Trading game interface
- `POST /game/set-pin` - Set game PIN
- `POST /game/enter` - Enter game
- `GET /game/price` - Get current prices
- `POST /game/trade` - Execute trade
- `GET /game/history` - View trade history
- `POST /game/exit` - Exit game and settle

### 7.3 FOPI Game (Future of Property Investment)

**Controller:** `app/Http/Controllers/GameController.php` (FOPI methods)
**Service:** `app/Services/FopiGameEngine.php`

**Game Mechanics:**
- User stakes RWAMP tokens
- Converted to FOPI (Future of Property Investment) tokens (1000 FOPI = 1 RWAMP)
- Buy properties in different regions (Pakistan, UAE, International, Club)
- Properties generate rent income
- Properties appreciate over time
- Convert profit FOPI back to RWAMP
- Time progression: 60 seconds = 1 game day, 30 days = 1 month

**Property Types:**
- **Under Construction (UC):** Lower yield, higher appreciation, delay risk
- **Ready:** Immediate yield, lower appreciation
- **Club Properties:** Premium properties with higher yields

**Features:**
- Property price appreciation (monthly)
- Rent collection (monthly)
- Sell queue (2+ months delay)
- Installment plans
- Syndicate staking
- Mission system (not yet implemented)
- Burn mechanism (fees reduce RWAMP supply)

**State Management:**
- Game state stored in `GameSession.state_json`
- Auto-progression of months based on time elapsed
- Manual month jumps available

**Routes:**
- `GET /game/fopi` - FOPI game interface
- `POST /game/fopi/start` - Start FOPI session
- `POST /game/fopi/jump-month` - Advance month
- `POST /game/fopi/buy` - Buy property
- `POST /game/fopi/sell` - Sell property (not implemented)
- `POST /game/fopi/claim-rent` - Claim rent income
- `POST /game/fopi/convert` - Convert FOPI to RWAMP
- `POST /game/fopi/exit` - Exit game
- `GET /game/fopi/history` - View game history

**Models:**
- `GameSession` - Session data with FOPI state
- `FopiGameEvent` - Event logging

---

## 8. Admin Dashboard

### 8.1 Admin Dashboard Overview

**Controller:** `app/Http/Controllers/Admin/AdminDashboardController.php`

**Metrics Displayed:**
- Total users, resellers, investors
- New users (7 days, 30 days)
- Pending applications, KYC submissions
- Crypto payments, withdrawals
- Coin price

**Security:**
- 2FA required for all admin routes
- Recovery codes for 2FA backup
- ULID-based short URLs for sensitive operations

### 8.2 User Management

**Controller:** `app/Http/Controllers/Admin/AdminUserController.php`

**Features:**
- List all users with filters
- Create new users
- View/edit user details
- Reset user passwords
- Assign wallet addresses
- Delete users
- ULID-based URLs (`/a/u/{user}`)

**Routes:**
- `GET /dashboard/admin/users` - User list
- `POST /a/u/` - Create user
- `GET /a/u/{user}` - User details
- `PUT /a/u/{user}` - Update user
- `DELETE /a/u/{user}` - Delete user
- `POST /a/u/{user}/reset-password` - Reset password

### 8.3 Reseller Application Management

**Controller:** `app/Http/Controllers/Admin/AdminResellerApplicationController.php`

**Features:**
- List all applications
- Approve/reject applications
- Create user account on approval
- View application details
- ULID-based URLs (`/a/ap/{application}`)

**Routes:**
- `GET /dashboard/admin/applications` - Application list
- `GET /a/ap/{application}/details` - Application details
- `PUT /a/ap/{application}/approve` - Approve application
- `PUT /a/ap/{application}/reject` - Reject application

### 8.4 Price Management

**Controller:** `app/Http/Controllers/Admin/AdminPriceController.php`

**Features:**
- View current RWAMP price in PKR
- Update RWAMP price
- Price stored in cache and database
- Affects all purchase calculations

**Routes:**
- `GET /dashboard/admin/prices` - Price management page
- `POST /dashboard/admin/prices/update` - Update price

### 8.5 Admin Sell Coins

**Controller:** `app/Http/Controllers/Admin/AdminSellController.php`

**Features:**
- Search users by name/email/wallet
- Sell tokens to users
- OTP verification required
- Payment proof upload
- Transaction logging

**Routes:**
- `GET /dashboard/admin/sell` - Sell page
- `POST /dashboard/admin/sell` - Execute sell
- `GET /api/admin/search-users` - Search users
- `POST /api/admin/send-otp` - Send OTP
- `POST /api/admin/fetch-payment-proof` - Get payment proof

### 8.6 2FA Management

**Controller:** `app/Http/Controllers/Admin/Admin2FAController.php`

**Features:**
- Setup 2FA (QR code generation)
- Regenerate recovery codes
- Required for all admin access

**Routes:**
- `GET /admin/2fa/setup` - 2FA setup page
- `POST /admin/2fa/regenerate-recovery-codes` - Regenerate codes

---

## 9. Investor Dashboard

### 9.1 Dashboard Overview

**Controller:** `app/Http/Controllers/Investor/InvestorDashboardController.php`

**Features:**
- Token balance display
- Portfolio value calculation (average purchase price)
- Recent payments and transactions
- Pending buy requests
- Game state indicators
- Quick purchase modal

**Metrics:**
- Token balance
- Portfolio value (at average purchase price)
- Official portfolio value (at current price)
- Average purchase price (weighted)

### 9.2 Transaction History

**Controller:** `app/Http/Controllers/Investor/InvestorHistoryController.php`

**Features:**
- View all transactions
- Filter by type, status
- Search functionality
- Pagination

**Transaction Types:**
- `credit` - Token credits
- `debit` - Token debits
- `crypto_purchase` - Crypto payment purchases
- `buy_from_reseller` - Purchases from resellers
- `admin_transfer_credit` - Admin transfers
- `commission` - Reseller commissions

---

## 10. Services & Helpers

### 10.1 PriceHelper (`app/Helpers/PriceHelper.php`)

**Functions:**
- `getRwampPkrPrice()` - Get RWAMP price in PKR (admin-set)
- `getRwampUsdPrice()` - Get RWAMP price in USD (calculated)
- `getUsdtUsdPrice()` - Get USDT price in USD
- `getUsdtPkrPrice()` - Get USDT price in PKR
- `getBtcUsdPrice()` - Get BTC price in USD
- `getBtcPkrPrice()` - Get BTC price in PKR
- `getUsdToPkrRate()` - Get USD/PKR exchange rate (API-fetched)
- `getResellerCommissionRate()` - Get reseller commission (10% default)
- `getResellerMarkupRate()` - Get reseller markup (5% default)

**Caching:**
- Prices cached for performance
- Exchange rates cached for 1 hour
- Database fallback for persistent storage

### 10.2 GamePriceEngine (`app/Services/GamePriceEngine.php`)

**Functions:**
- `getBtcUsdPrice()` - Fetch BTC/USD from Binance API
- `getUsdPkrRate()` - Fetch USD/PKR from exchange API
- `calculateAnchoredPrice()` - Calculate price based on BTC movement
- `calculatePrices()` - Calculate buy/sell prices with spread
- `getCurrentPrices()` - Get live game prices

**Price Anchoring:**
- Prices anchored to BTC/USD at session start
- Velocity multiplier affects price movement
- Spread and fees applied to buy/sell prices

### 10.3 FopiGameEngine (`app/Services/FopiGameEngine.php`)

**Functions:**
- `startSession()` - Initialize FOPI game session
- `loadState()` - Load game state from session
- `jumpMonth()` - Advance game month
- `buyProperty()` - Purchase property
- `claimRent()` - Collect rent income
- `convertFopiToRwamp()` - Convert FOPI to RWAMP
- `exitSession()` - Exit game and settle balance

**Game Logic:**
- Property appreciation (monthly)
- Rent calculation (monthly)
- Sell queue processing
- Installment plan handling
- Burn mechanism

### 10.4 CryptoPaymentVerifier (`app/Services/CryptoPaymentVerifier.php`)

**Functions:**
- `monitorWallets()` - Monitor all admin wallets
- `monitorEthereumWallet()` - Monitor ERC20 transactions
- `monitorTronWallet()` - Monitor TRC20 transactions
- `monitorBitcoinWallet()` - Monitor BTC transactions
- Auto-credit tokens on payment detection

**Note:** Currently not actively used; payments are manual approval workflow

### 10.5 Other Services

- **EmailService** - Email sending functionality
- **QrCodeService** - QR code generation for wallet addresses
- **ResellerService** - Reseller application management
- **ChatService** - Chat functionality (currently disabled)
- **ContactService** - Contact form handling
- **NewsletterService** - Newsletter subscription management
- **TabAuthService** - Tab-based authentication management

---

## 11. Database Structure

### 11.1 Key Tables

**Users:**
- `users` - Main user table with all user data
- `password_reset_tokens` - Password reset tokens

**Payments:**
- `crypto_payments` - Crypto payment submissions
- `payment_submissions` - Manual/bank payment submissions
- `transactions` - All token transactions
- `processed_crypto_transactions` - Processed blockchain transactions

**KYC:**
- KYC data stored in `users` table (kyc_* fields)
- Documents stored in `storage/app/kyc/{user_id}/`

**Resellers:**
- `reseller_applications` - Reseller application submissions
- `buy_from_reseller_requests` - User buy requests from resellers

**Games:**
- `user_game_sessions` - Game session data
- `game_trades` - Trading game trades
- `game_price_history` - Price history for sessions
- `game_settings` - Game configuration
- `fopi_game_events` - FOPI game event logging

**Withdrawals:**
- `withdraw_requests` - Withdrawal requests

**System:**
- `system_settings` - System configuration (prices, rates, etc.)
- `contacts` - Contact form submissions
- `newsletter_subscriptions` - Newsletter subscribers

**Chat (Disabled):**
- `chats` - Chat rooms
- `chat_messages` - Chat messages
- `chat_participants` - Chat participants
- `chat_message_reads` - Message read tracking

### 11.2 ULID Implementation

**Concern:** `app/Concerns/HasUlid.php`

**Purpose:**
- Short, URL-friendly identifiers
- Replaces numeric IDs in URLs
- Used for: Users, Payments, Applications, Withdrawals

**Migration:** `2025_12_02_120000_add_ulid_columns_to_routed_tables.php`

---

## 12. Frontend Assets

### 12.1 JavaScript

**Files:**
- `resources/js/app.js` - Main application JS
- `resources/js/game.js` - Trading game logic
- `resources/js/fopi-game.js` - FOPI game logic
- `resources/js/alpine.js` - Alpine.js components

**Libraries:**
- Alpine.js - Reactive UI components
- Chart.js - Chart rendering
- Laravel Echo - Real-time events (Pusher)
- Axios - HTTP requests

### 12.2 CSS

**Files:**
- `resources/css/app.css` - Main stylesheet
- `resources/css/fopi-game.css` - FOPI game styles

**Framework:**
- Tailwind CSS - Utility-first CSS framework

### 12.3 Views Structure

**Layouts:**
- `resources/views/layouts/app.blade.php` - Main layout

**Auth:**
- `resources/views/auth/login.blade.php`
- `resources/views/auth/signup.blade.php`
- `resources/views/auth/kyc.blade.php`
- `resources/views/auth/verify-email.blade.php`
- `resources/views/auth/two-factor-challenge.blade.php`

**Dashboard:**
- `resources/views/dashboard/admin.blade.php`
- `resources/views/dashboard/investor.blade.php`
- `resources/views/dashboard/reseller.blade.php`
- `resources/views/dashboard/user-history.blade.php`

**Game:**
- `resources/views/game/select.blade.php`
- `resources/views/game/index.blade.php` - Trading game
- `resources/views/game/fopi.blade.php` - FOPI game
- `resources/views/game/fopi-history.blade.php`

**Pages:**
- `resources/views/pages/purchase.blade.php`
- `resources/views/pages/about.blade.php`
- `resources/views/pages/contact.blade.php`

**Components:**
- Reusable Blade components in `resources/views/components/`

**Modals:**
- Purchase modal
- KYC modal
- Game entry modal

---

## 13. Configuration

### 13.1 Crypto Configuration (`config/crypto.php`)

**Rates:**
- RWAMP prices (USD, PKR)
- USDT prices (USD, PKR)
- BTC prices (USD, PKR)
- Exchange rates (USD/PKR, USD/AED)

**Wallets:**
- TRC20, ERC20, BEP20, BTC wallet addresses

**API Keys:**
- Etherscan, Alchemy, TronGrid, Blockstream

**Features:**
- `payments_enabled` - Master switch for payments
- `walletconnect_enabled` - WalletConnect integration
- `static_payment_disabled` - Disable static address payments

**Reseller Settings:**
- Commission rate (10% default)
- Markup rate (5% default)

### 13.2 App Configuration

**Key Settings:**
- `APP_NAME` - Application name
- `APP_ENV` - Environment (local, production)
- `APP_DEBUG` - Debug mode
- `APP_URL` - Application URL

**Mail Configuration:**
- SMTP settings for email sending
- OTP emails
- Notification emails

**Cache:**
- Prices cached for performance
- Exchange rates cached for 1 hour
- Session cache for tab authentication

---

## 14. Security Features

### 14.1 Authentication Security

- **Password Hashing:** bcrypt
- **2FA:** TOTP-based (Google Authenticator compatible)
- **Session Management:** Secure session handling
- **CSRF Protection:** Laravel CSRF tokens
- **Rate Limiting:** Login throttling (5 attempts/minute)

### 14.2 Authorization Security

- **Role-Based Access:** Strict role enforcement
- **KYC Verification:** Required for withdrawals and games
- **Admin 2FA:** Mandatory for admin access
- **Game PIN:** 4-digit PIN with lockout (3 attempts)

### 14.3 Data Security

- **File Storage:** Secure KYC document storage
- **Path Traversal Protection:** File access validation
- **ULID URLs:** Short, non-sequential identifiers
- **Input Validation:** Comprehensive validation rules
- **SQL Injection Protection:** Eloquent ORM

### 14.4 Security Headers

**Middleware:** `SecurityHeaders`

**Headers Added:**
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Content-Security-Policy (if configured)

---

## 15. Error Handling & Logging

### 15.1 Exception Handler

**File:** `app/Exceptions/Handler.php`

**Features:**
- Custom 404 page
- Custom 503 page for production errors
- Debug mode error display
- Error logging

### 15.2 Logging

**Channels:**
- File logging (default)
- Error logging for critical issues
- Info logging for important events
- Warning logging for potential issues

**Logged Events:**
- Authentication attempts
- Payment submissions
- Game actions
- Admin actions
- Error occurrences

---

## 16. Commands & Jobs

### 16.1 Artisan Commands

**Location:** `app/Console/Commands/`

**Commands:**
- `AuditKycImages` - Audit KYC image storage
- `BackfillUlids` - Backfill ULID columns
- `GenerateMissingWallets` - Generate wallet addresses
- `MonitorCryptoPayments` - Monitor blockchain payments
- `PruneGamePriceHistory` - Clean old price history
- `ReconcileUserBalances` - Reconcile user balances
- `ResetStuckGameStates` - Reset stuck game sessions
- `SyncMissingUsers` - Sync user data
- `UpdateExchangeRate` - Update exchange rates

---

## 17. API Endpoints

### 17.1 Public API

**Routes:** `routes/api.php`

**Endpoints:**
- `GET /api/user` - Get authenticated user (Sanctum)

### 17.2 Internal API (AJAX)

**Endpoints:**
- `/api/contact` - Submit contact form
- `/api/reseller` - Submit reseller application
- `/api/newsletter` - Subscribe to newsletter
- `/api/check-referral-code` - Validate referral code
- `/api/check-email` - Check email availability
- `/api/check-phone` - Check phone availability
- `/api/save-wallet-address` - Save wallet address
- `/api/submit-tx-hash` - Submit transaction hash
- `/api/check-payment-status` - Check payment status
- `/api/user/withdraw` - Submit withdrawal
- `/api/user/buy-from-reseller` - Buy from reseller
- `/api/resellers/search` - Search resellers
- `/api/verify-otp` - Verify OTP

---

## 18. Key Features Summary

### 18.1 User Features

✅ **Registration & Login**
- Email/password registration
- OTP-based email verification
- Role-based login (investor/reseller)
- Password reset
- 2FA support

✅ **KYC Verification**
- Document upload (ID front/back, selfie)
- Status tracking (pending/approved/rejected)
- Secure document storage

✅ **Token Purchase**
- Crypto payments (USDT/BTC)
- Manual/bank payment submission
- Real-time price calculation
- QR code generation
- Transaction tracking

✅ **Token Management**
- Balance viewing
- Transaction history
- Portfolio value calculation
- Average purchase price tracking

✅ **Withdrawals**
- Withdrawal requests
- KYC requirement
- Receipt viewing

✅ **Reseller Features**
- Buy from resellers
- OTP-protected requests
- Request approval workflow

✅ **Games**
- Trading game (BTC-anchored)
- FOPI game (property investment)
- Game PIN protection
- Session management
- Trade history

### 18.2 Reseller Features

✅ **Dashboard**
- Referred users management
- Payment approval
- Buy request management
- Custom coin pricing
- Transaction history

✅ **Sell Tokens**
- Search users
- OTP verification
- Payment proof management
- Transaction logging

### 18.3 Admin Features

✅ **User Management**
- Create/edit/delete users
- Password reset
- Wallet assignment
- ULID-based URLs

✅ **KYC Management**
- Approve/reject KYC
- Document viewing/downloading
- Rejection reason tracking

✅ **Payment Management**
- Approve/reject crypto payments
- View payment details
- Download screenshots
- ULID-based URLs

✅ **Withdrawal Management**
- Approve/reject withdrawals
- Upload receipts
- Track transaction hashes

✅ **Reseller Applications**
- Approve/reject applications
- Create user accounts
- Generate referral codes

✅ **Price Management**
- Update RWAMP price
- View current prices

✅ **Sell Coins**
- Search users
- Sell tokens directly
- OTP verification
- Payment proof management

✅ **Dashboard Metrics**
- User statistics
- Payment statistics
- Application statistics
- KYC statistics

✅ **2FA Management**
- Setup 2FA
- Regenerate recovery codes

---

## 19. Known Issues & Limitations

### 19.1 Current Limitations

1. **Chat System:** Currently disabled (routes commented out)
2. **FOPI Sell:** Not yet implemented
3. **FOPI Missions:** Not yet implemented
4. **Auto Payment Detection:** CryptoPaymentVerifier not actively used
5. **WalletConnect:** Configured but may need testing

### 19.2 Technical Debt

1. **Legacy Routes:** Some backward-compatible routes still exist
2. **Code Duplication:** Some repeated logic in controllers
3. **Error Handling:** Some areas could use more comprehensive error handling
4. **Testing:** Limited test coverage

---

## 20. Deployment Considerations

### 20.1 Environment Variables

**Required:**
- `APP_KEY` - Application encryption key
- `DB_*` - Database credentials
- `MAIL_*` - Email configuration
- `CRYPTO_WALLET_*` - Wallet addresses
- `RECAPTCHA_*` - reCAPTCHA keys (optional)

**Optional:**
- `CRYPTO_PAYMENTS_ENABLED` - Enable/disable payments
- `WALLETCONNECT_PROJECT_ID` - WalletConnect project ID
- `ALCHEMY_API_KEY` - Alchemy API key
- `TRONGRID_API_KEY` - TronGrid API key

### 20.2 Database Migrations

Run migrations:
```bash
php artisan migrate
```

### 20.3 Cache Configuration

Ensure cache is configured (Redis recommended for production)

### 20.4 File Storage

Ensure `storage/app/kyc/` directory is writable

### 20.5 Queue Configuration

Some features may benefit from queue workers (email sending, etc.)

---

## 21. Future Enhancements

### 21.1 Potential Improvements

1. **Auto Payment Detection:** Activate CryptoPaymentVerifier for automatic token crediting
2. **FOPI Sell:** Implement property selling functionality
3. **FOPI Missions:** Add mission/achievement system
4. **Chat System:** Re-enable and enhance chat functionality
5. **API Documentation:** Add comprehensive API documentation
6. **Testing:** Increase test coverage
7. **Performance:** Optimize database queries
8. **Monitoring:** Add application monitoring
9. **Analytics:** Add user analytics dashboard
10. **Mobile App:** Consider mobile app development

---

## Conclusion

The RWAMP Laravel application is a comprehensive cryptocurrency investment platform with sophisticated features including:

- Multi-role user management (Investors, Resellers, Admins)
- KYC verification system
- Crypto payment processing
- Trading and FOPI games
- Reseller network management
- Comprehensive admin dashboard

The codebase is well-structured with clear separation of concerns, proper use of Laravel features, and security best practices. The application is production-ready with room for future enhancements.

---

**Document Generated:** {{ date('Y-m-d H:i:s') }}
**Codebase Version:** Laravel 10.x
**Analysis Scope:** Complete codebase review

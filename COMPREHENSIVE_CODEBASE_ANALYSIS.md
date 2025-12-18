# Comprehensive Codebase Analysis - RWAMP Laravel Application

**Generated:** {{ date('Y-m-d H:i:s') }}  
**Project:** RWAMP - The Currency of Real Estate Investments  
**Framework:** Laravel 10.x  
**PHP Version:** ^8.1

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Project Architecture](#project-architecture)
3. [Models & Database Structure](#models--database-structure)
4. [Controllers & Routes](#controllers--routes)
5. [Services & Business Logic](#services--business-logic)
6. [Middleware & Security](#middleware--security)
7. [Roles & Dashboards](#roles--dashboards)
8. [Features & Functionality](#features--functionality)
9. [Views & Resources](#views--resources)
10. [Commands & Scheduled Tasks](#commands--scheduled-tasks)
11. [Configuration & Dependencies](#configuration--dependencies)

---

## Executive Summary

RWAMP is a comprehensive Laravel-based cryptocurrency token platform for real estate investments. The application manages:

- **User Management:** Multi-role system (Admin, Reseller, Investor)
- **Token Transactions:** Crypto payments, manual payments, withdrawals
- **KYC System:** Identity verification with document uploads
- **Gaming Platform:** Trading game and FOPI (real estate simulation) game
- **Reseller Network:** Commission-based partner program
- **Payment Processing:** Multiple payment methods (crypto, bank, cash)

**Key Technologies:**
- Laravel 10.x with PHP 8.1+
- Laravel Fortify (Authentication & 2FA)
- Laravel Sanctum (API tokens)
- MySQL/MariaDB database
- Blade templating engine
- Tailwind CSS for styling

---

## Project Architecture

### Directory Structure

```
app/
├── Actions/              # Action classes (5 files)
├── Concerns/             # Traits (HasUlid)
├── Console/Commands/     # Artisan commands (10 files)
├── Events/               # Event classes (2 files)
├── Exceptions/           # Exception handlers
├── Helpers/              # Helper functions
├── Http/
│   ├── Controllers/      # 57 controller files
│   │   ├── Admin/        # Admin controllers (10 files)
│   │   ├── Investor/     # Investor controllers (2 files)
│   │   ├── Reseller/     # Reseller controllers (7 files)
│   │   └── Auth/         # Authentication controllers
│   ├── Middleware/       # Custom middleware
│   └── Requests/         # Form request validation
├── Mail/                 # Email classes
├── Models/               # 18 Eloquent models
├── Providers/            # Service providers
├── Rules/                # Custom validation rules
├── Services/             # 11 service classes
└── Traits/               # Reusable traits

resources/
├── views/                # Blade templates
│   ├── admin/            # Admin dashboard views
│   ├── auth/             # Authentication views
│   ├── dashboard/        # Role-based dashboards
│   ├── game/             # Game interface views
│   └── pages/            # Public pages

routes/
├── web.php               # Web routes (705 lines)
└── api.php               # API routes

database/
├── migrations/           # Database migrations
└── seeders/              # Database seeders
```

---

## Models & Database Structure

### Core Models (18 Total)

#### 1. **User Model** (`app/Models/User.php`)
**Purpose:** Central user management with multi-role support

**Key Attributes:**
- `id`, `ulid` (26-char ULID for public URLs)
- `name`, `email`, `phone`, `password`
- `role` (admin, reseller, investor, user)
- `token_balance` (RWAMP token balance)
- `wallet_address` (16-digit numeric)
- `referral_code`, `reseller_id`
- KYC fields: `kyc_status`, `kyc_id_type`, `kyc_id_number`, `kyc_full_name`, `kyc_*_path`
- Game fields: `game_pin_hash`, `trading_game_pin_hash`, `fopi_game_pin_hash`, `is_in_game`
- 2FA fields: `two_factor_secret`, `two_factor_recovery_codes`

**Relationships:**
- `transactions()` - hasMany Transaction
- `reseller()` - belongsTo User (reseller_id)
- `referredUsers()` - hasMany User (reseller_id)
- `cryptoPayments()` - hasMany CryptoPayment
- `gameSessions()` - hasMany GameSession
- `activeGameSession()` - hasOne GameSession (active)

**Key Methods:**
- `addTokens($amount, $description)` - Credit tokens with transaction log
- `deductTokens($amount, $description)` - Debit tokens with validation
- `reconcileBalance()` - Check balance consistency
- `fixBalanceFromTransactions()` - Auto-fix balance discrepancies
- `setGameSpecificPin($pin, $gameType)` - Set game PIN (trading/fopi)
- `verifyGameSpecificPin($pin, $gameType)` - Verify game PIN with lockout
- `canEnterGame()` - Check game entry prerequisites

**Security Features:**
- Password hashing (bcrypt)
- 2FA support (Laravel Fortify)
- Game PIN lockout (3 attempts = 5 min lock)
- Balance reconciliation system

---

#### 2. **Transaction Model** (`app/Models/Transaction.php`)
**Purpose:** Complete transaction history for all token movements

**Key Attributes:**
- `user_id`, `sender_id`, `recipient_id`
- `type` (crypto_purchase, admin_transfer_credit, reseller_sell, buy_from_reseller, commission, withdrawal_approved, etc.)
- `amount` (positive = credit, negative = debit)
- `price_per_coin`, `total_price`
- `status` (pending, completed, failed, rejected)
- `payment_type` (usdt, bank, cash)
- `payment_hash`, `payment_receipt`
- `payment_status` (pending, verified, rejected)
- `reference` (transaction reference)

**Relationships:**
- `user()` - belongsTo User
- `sender()` - belongsTo User
- `recipient()` - belongsTo User
- `verifier()` - belongsTo User (verified_by)

**Transaction Types:**
- `crypto_purchase` - Crypto payment approved
- `admin_transfer_credit` - Admin credits user
- `admin_transfer_debit` - Admin debits user (tracking)
- `admin_buy_from_user` - Admin purchases from user
- `reseller_sell` - Reseller sells to user
- `buy_from_reseller` - User buys from reseller
- `commission` - Reseller commission
- `withdrawal_approved` - Withdrawal processed
- `withdrawal_refund` - Withdrawal rejected/refunded

---

#### 3. **CryptoPayment Model** (`app/Models/CryptoPayment.php`)
**Purpose:** Crypto payment submissions awaiting approval

**Key Attributes:**
- `user_id`
- `token_amount`, `usd_amount`, `pkr_amount`
- `coin_price_rs` (calculated price per token)
- `network` (TRC20, ERC20, BEP20, BTC, BNB)
- `tx_hash` (transaction hash)
- `screenshot` (payment proof)
- `status` (pending, approved, rejected)
- `reseller_commission_awarded` (boolean)

**Relationships:**
- `user()` - belongsTo User

**Workflow:**
1. User submits crypto payment with tx_hash
2. Status: pending
3. Admin/Reseller reviews and approves
4. Tokens credited to user
5. Commission awarded to reseller (if applicable)

---

#### 4. **PaymentSubmission Model** (`app/Models/PaymentSubmission.php`)
**Purpose:** Manual/bank payment submissions

**Key Attributes:**
- `user_id`, `recipient_id`, `recipient_type` (admin, reseller)
- `token_amount`, `fiat_amount`, `currency`
- `bank_name`, `account_last4`, `bank_reference`
- `receipt_path` (uploaded receipt)
- `status` (pending, approved, rejected)
- `admin_notes`

**Relationships:**
- `user()` - belongsTo User
- `recipient()` - belongsTo User (recipient_id)

**Workflow:**
1. User submits bank payment with receipt
2. Assigned to admin or specific reseller
3. Reseller approves and transfers tokens from their balance
4. Status updated to approved

---

#### 5. **GameSession Model** (`app/Models/GameSession.php`)
**Purpose:** Game session tracking for Trading and FOPI games

**Key Attributes:**
- `user_id`, `type` (trading, fopi)
- `real_balance_start` (RWAMP tokens staked)
- `game_balance_start` (game tokens)
- `game_balance_end`, `real_balance_end`
- `total_platform_revenue` (fees collected)
- `net_user_pnl_pkr` (profit/loss)
- `anchor_btc_usd`, `anchor_mid_price` (price anchors)
- `status` (active, completed, abandoned)
- `started_at`, `ended_at`
- `chart_state` (JSON), `state_json` (FOPI game state)

**Relationships:**
- `user()` - belongsTo User
- `trades()` - hasMany GameTrade
- `priceHistory()` - hasMany GamePriceHistory

**Key Methods:**
- `calculateCurrentBalance()` - Calculate RWAMP balance from trades
- `calculateCurrentPkrBalance()` - Calculate PKR balance
- `isActive()` - Check if session is active
- `getFopiState()` - Deserialize FOPI state
- `setFopiState($state)` - Serialize FOPI state

---

#### 6. **GameTrade Model** (`app/Models/GameTrade.php`)
**Purpose:** Individual trades within a game session

**Key Attributes:**
- `session_id`
- `side` (BUY, SELL)
- `quantity` (RWAMP tokens)
- `price_pkr` (price per token)
- `fee_pkr` (transaction fee)
- `spread_revenue_pkr` (platform revenue)
- `game_balance_after` (balance after trade)
- `idempotency_key` (prevent duplicates)

**Relationships:**
- `session()` - belongsTo GameSession

---

#### 7. **GamePriceHistory Model** (`app/Models/GamePriceHistory.php`)
**Purpose:** Price history tracking for game sessions

**Key Attributes:**
- `session_id`
- `mid_price`, `buy_price`, `sell_price`
- `btc_usd`, `usd_pkr`
- `recorded_at`

**Relationships:**
- `session()` - belongsTo GameSession

---

#### 8. **WithdrawRequest Model** (`app/Models/WithdrawRequest.php`)
**Purpose:** Token withdrawal requests

**Key Attributes:**
- `user_id`
- `wallet_address` (destination)
- `token_amount`
- `status` (pending, approved, rejected, completed)
- `notes`, `receipt_path`
- `transaction_hash`
- `transfer_completed_at`

**Relationships:**
- `user()` - belongsTo User

**Workflow:**
1. User submits withdrawal request
2. Tokens deducted immediately
3. Admin reviews and approves
4. Admin transfers tokens manually
5. Receipt uploaded, status = completed

---

#### 9. **BuyFromResellerRequest Model** (`app/Models/BuyFromResellerRequest.php`)
**Purpose:** User requests to buy tokens from reseller

**Key Attributes:**
- `user_id`, `reseller_id`
- `coin_quantity`, `coin_price`, `total_amount`
- `status` (pending, approved, rejected, completed)
- `rejection_reason`
- `approved_at`, `rejected_at`, `completed_at`

**Relationships:**
- `user()` - belongsTo User
- `reseller()` - belongsTo User

---

#### 10. **ResellerApplication Model** (`app/Models/ResellerApplication.php`)
**Purpose:** Reseller partner applications

**Key Attributes:**
- `name`, `email`, `phone`, `password`
- `company`, `investment_capacity`, `experience`
- `message`
- `status` (pending, approved, rejected)
- `ip_address`, `user_agent`

**Scopes:**
- `pending()`, `approved()`, `rejected()`

---

#### 11. **GameSetting Model** (`app/Models/GameSetting.php`)
**Purpose:** Singleton game configuration

**Key Attributes:**
- `entry_multiplier` (default: 10.0)
- `exit_divisor` (default: 100.0)
- `exit_fee_rate` (default: 0.0)
- `game_timeout_seconds` (nullable)
- `fopi_per_rwamp` (default: 1000.0)
- `fopi_game_enabled` (default: true)

**Key Method:**
- `current()` - Get or create singleton settings

---

#### 12. **Chat Models** (Chat, ChatMessage, ChatParticipant, ChatMessageRead)
**Purpose:** Chat system (currently disabled)

**Note:** Chat system is disabled but models exist for future use.

---

#### 13. **Support Models**
- `Contact` - Contact form submissions
- `NewsletterSubscription` - Newsletter signups
- `ProcessedCryptoTransaction` - Processed blockchain transactions
- `GamePriceHistory` - Price tracking

---

## Controllers & Routes

### Route Structure (`routes/web.php` - 705 lines)

#### Public Routes
- `/` - Home page
- `/about` - About page
- `/contact` - Contact form
- `/become-partner` - Reseller application
- `/whitepaper` - Whitepaper PDF
- `/how-to-buy` - Purchase guide
- `/privacy-policy`, `/terms-of-service`, `/disclaimer` - Legal pages
- `/robots.txt`, `/sitemap.xml` - SEO

#### Authentication Routes
- `/login` - Login page
- `/register` - Registration
- `/verify-email` - Email verification (OTP-based)
- `/forgot-password` - Password reset
- `/reset-password/{token}` - Password reset form

#### Protected Routes (auth middleware)

**Investor Dashboard:**
- `/dashboard/investor` - Investor dashboard
- `/dashboard/history` - Transaction history
- `/dashboard/payments/submit` - Manual payment submission
- `/profile` - User profile
- `/wallet` - Wallet management

**Reseller Dashboard:**
- `/dashboard/reseller` - Reseller dashboard
- `/dashboard/reseller/users` - Manage referred users
- `/dashboard/reseller/payments` - Approve payments
- `/dashboard/reseller/transactions` - Transaction history
- `/dashboard/reseller/sell` - Sell tokens to users
- `/dashboard/reseller/buy-requests` - Manage buy requests

**Admin Dashboard:**
- `/dashboard/admin` - Admin dashboard (2FA required)
- `/dashboard/admin/users` - User management (ULID URLs: `/a/u/{user}`)
- `/dashboard/admin/crypto-payments` - Crypto payment management (ULID URLs: `/a/p/{payment}`)
- `/dashboard/admin/kyc` - KYC management
- `/dashboard/admin/withdrawals` - Withdrawal management (ULID URLs: `/a/w/{withdrawal}`)
- `/dashboard/admin/applications` - Reseller applications (ULID URLs: `/a/ap/{application}`)
- `/dashboard/admin/prices` - Price management
- `/dashboard/admin/sell` - Admin sell coins
- `/dashboard/admin/history` - Payment/transaction history
- `/admin/2fa/setup` - 2FA setup

**Game Routes:**
- `/game/select` - Game selection
- `/game/trading` - Trading game
- `/game/fopi` - FOPI game
- `/game/price` - Get current prices
- `/game/trade` - Execute trade
- `/game/enter` - Enter game session
- `/game/exit` - Exit game session
- `/game/history` - Game history

**FOPI Game API:**
- `/game/fopi/state` - Get game state
- `/game/fopi/start` - Start FOPI session
- `/game/fopi/jump-month` - Advance month
- `/game/fopi/buy` - Buy property
- `/game/fopi/sell` - Sell property
- `/game/fopi/claim-rent` - Claim rent
- `/game/fopi/convert` - Convert FOPI to RWAMP
- `/game/fopi/mission/claim` - Claim mission reward

**API Routes (within web.php):**
- `/api/contact` - Contact form submission
- `/api/reseller` - Reseller application
- `/api/newsletter` - Newsletter subscription
- `/api/check-referral-code` - Validate referral code
- `/api/check-email`, `/api/check-phone` - Duplicate checks
- `/api/save-wallet-address` - Save wallet address
- `/api/check-payment-status` - Check payment status
- `/api/submit-tx-hash` - Submit crypto payment
- `/api/user/buy-from-reseller` - Buy from reseller
- `/api/user/withdraw` - Submit withdrawal
- `/api/reseller/sell` - Reseller sell tokens
- `/api/reseller/send-otp` - Send OTP for operations
- `/api/verify-otp` - Verify OTP

---

### Controller Analysis

#### Admin Controllers (10 files)

**1. AdminDashboardController**
- `index()` - Dashboard with metrics (users, payments, KYC, withdrawals)

**2. AdminUserController**
- `index()` - List users with search/filters
- `store()` - Create user with optional coin assignment
- `show()` - User details with balance reconciliation
- `update()` - Update user (with balance change tracking)
- `destroy()` - Delete user
- `resetPassword()` - Reset user password
- `assignWalletAddress()` - Generate wallet address

**Key Features:**
- Balance reconciliation before/after updates
- Transaction history for all balance changes
- ULID-based URLs for security
- Price tracking for coin assignments

**3. AdminCryptoPaymentController**
- `index()` - List crypto payments
- `show()` - Payment details
- `approve()` - Approve payment and credit tokens
- `reject()` - Reject payment
- `update()` - Update payment details
- `destroy()` - Delete payment
- `downloadScreenshot()` - Download payment proof
- `history()` - Payment/transaction history
- `approveBank()` - Approve bank payment (view-only for admin)

**Key Features:**
- Automatic token crediting on approval
- Reseller commission calculation
- Transaction logging
- ULID-based URLs

**4. AdminKycController**
- `index()` - List KYC submissions
- `approve()` - Approve KYC (upgrade to investor)
- `reject()` - Reject KYC with reason
- `update()` - Update KYC information
- `destroy()` - Delete KYC submission
- `downloadFile()` - Download KYC documents

**Key Features:**
- Secure file path validation
- Atomic file uploads (all-or-nothing)
- Role preservation (never downgrade admin/reseller)
- Email notifications

**5. AdminWithdrawalController**
- `index()` - List withdrawal requests
- `show()` - Withdrawal details
- `approve()` - Approve withdrawal
- `reject()` - Reject withdrawal (refund tokens)
- `update()` - Update withdrawal (adjust amount)
- `destroy()` - Delete withdrawal (refund tokens)
- `submitReceipt()` - Upload transfer receipt

**Key Features:**
- Token refund on rejection/deletion
- Amount adjustment with balance updates
- Email notifications for all status changes

**6. AdminResellerApplicationController**
- `index()` - List applications
- `show()` - Application details
- `approve()` - Approve and create reseller account
- `reject()` - Reject application
- `update()` - Update application
- `destroy()` - Delete application

**7. AdminPriceController**
- `index()` - Price management page
- `update()` - Update token prices

**8. AdminSellController**
- `index()` - Admin sell coins page
- `store()` - Sell coins to user
- `searchUsers()` - Search users API
- `sendOtp()` - Send OTP for sell operation
- `fetchPaymentProof()` - Fetch user payment proof

**9. Admin2FAController**
- `show()` - 2FA setup page
- `regenerateRecoveryCodes()` - Regenerate recovery codes

**10. AdminChatController**
- Currently disabled (chat system disabled)

---

#### Reseller Controllers (7 files)

**1. ResellerDashboardController**
- `index()` - Reseller dashboard with metrics
- Portfolio value calculation
- Average purchase price tracking
- Game state management

**2. ResellerUserController**
- `index()` - List referred users
- `show()` - User details

**3. ResellerPaymentController**
- `index()` - List pending payments (crypto + bank)
- `show()` - Payment details
- `approve()` - Approve crypto payment
- `reject()` - Reject payment
- `approveBank()` - Approve bank payment (transfer from reseller balance)
- `rejectBank()` - Reject bank payment
- `fetchUserPaymentProof()` - Fetch payment proof

**Key Features:**
- Only shows payments for reseller's referred users
- Token transfer from reseller balance for bank payments
- Transaction logging

**4. ResellerTransactionController**
- `index()` - Transaction history
- `show()` - Transaction details

**5. ResellerSellController**
- `index()` - Sell coins page
- `searchUsers()` - Search users API
- `sendOtp()` - Send OTP
- `store()` - Sell tokens to user (OTP protected)
- `updateCoinPrice()` - Update custom coin price

**Key Features:**
- OTP verification required
- Payment type tracking (USDT, bank, cash)
- Automatic payment status for cash
- Transaction logging for both parties

**6. ResellerBuyRequestController**
- `index()` - List buy requests
- `approve()` - Approve buy request
- `reject()` - Reject buy request

**7. ResellerBuyTransactionController**
- `index()` - Buy transaction history
- `show()` - Transaction details

---

#### Investor Controllers (2 files)

**1. InvestorDashboardController**
- `index()` - Investor dashboard
- Portfolio value calculation
- Recent payments/transactions
- Pending buy requests

**2. InvestorHistoryController**
- `index()` - Transaction history

---

#### Main Controllers

**1. GameController** (1060 lines)
- `select()` - Game selection page
- `index()` - Trading game interface
- `fopiIndex()` - FOPI game interface
- `setPin()` - Set game PIN
- `enter()` - Enter game session
- `price()` - Get current prices
- `trade()` - Execute trade (BUY/SELL)
- `history()` - Game history
- `exit()` - Exit game session
- `forceReset()` - Reset stuck game state
- FOPI game methods: `fopiGetState()`, `fopiStart()`, `fopiJumpMonth()`, `fopiBuy()`, `fopiSell()`, `fopiClaimRent()`, `fopiConvert()`, `fopiClaimMission()`

**Key Features:**
- Game-specific PINs (trading/fopi)
- PIN lockout (3 attempts = 5 min)
- Balance locking on entry
- Price anchoring to BTC
- Idempotency for trades
- Session state management

**2. CryptoPaymentController** (900 lines)
- `create()` - Purchase page
- `generateQrCode()` - Generate wallet QR code
- `saveWalletAddress()` - Save user wallet address
- `checkPaymentStatus()` - Check payment status
- `checkAutoPaymentStatus()` - Check auto-detected payments
- `submitTxHash()` - Submit crypto payment
- `userHistory()` - User payment history
- `buyFromReseller()` - Buy from reseller (legacy)
- `createBuyFromResellerRequest()` - Create buy request
- `sendOtpForBuyRequest()` - Send OTP

**Key Features:**
- Multiple network support (TRC20, ERC20, BEP20, BTC)
- Automatic price calculation
- Transaction record creation
- Email notifications

**3. PaymentSubmissionController**
- `create()` - Manual payment form
- `store()` - Submit payment
- `showReceipt()` - Stream receipt file

**4. WithdrawController**
- `store()` - Submit withdrawal request
- `index()` - List withdrawals
- `viewReceipt()` - View transfer receipt

**5. KycController**
- `show()` - KYC form
- `submit()` - Submit KYC documents
- `downloadFile()` - Download KYC documents

**6. ProfileController**
- `show()` - Profile page
- `updateAccount()` - Update account info
- `updatePassword()` - Change password
- `updateWallet()` - Update wallet address
- `generateWallet()` - Generate new wallet
- `resendEmailVerification()` - Resend OTP

**7. AuthController**
- `showLogin()` - Login page
- `login()` - Process login
- `logout()` - Logout with tab session handling
- `showForgotPassword()` - Forgot password page
- `checkReferralCode()` - Validate referral code

**8. RegisterController**
- `show()` - Registration page
- `store()` - Create account
- `checkEmail()` - Check email availability
- `checkPhone()` - Check phone availability

**9. PageController**
- `index()` - Home page
- `about()` - About page
- `contact()` - Contact page
- `becomePartner()` - Reseller application page
- `whitepaper()` - Whitepaper page
- `serveWhitepaper()` - Serve PDF
- `howToBuy()` - Purchase guide

**10. GameSettingController**
- `show()` - Game settings page
- `update()` - Update game settings

**11. WalletConnectController**
- `handleReturn()` - Handle wallet connection return
- `checkStatus()` - Check connection status

---

## Services & Business Logic

### Service Classes (11 files)

**1. FopiGameEngine** (552 lines)
- `startSession()` - Start FOPI game session
- `loadState()` - Load game state with auto-progress
- `jumpMonth()` - Advance game month
- `buyProperty()` - Buy property
- `claimRent()` - Claim rent income
- `convertFopiToRwamp()` - Convert FOPI to RWAMP
- `exitSession()` - Exit game and convert back

**Key Features:**
- Property appreciation simulation
- Rent collection system
- Installment plans
- Burn mechanism
- Achievement system
- Mission system

**2. GamePriceEngine**
- `getBtcUsdPrice()` - Fetch BTC/USD from Binance
- `getUsdPkrRate()` - Fetch USD/PKR exchange rate
- `getGameParameters()` - Get game settings
- `calculateAnchoredPrice()` - Calculate price based on BTC anchor
- `calculatePrices()` - Calculate buy/sell prices with spread
- `getCurrentPrices()` - Get live game prices

**Key Features:**
- Price caching (30s for BTC, 1h for USD/PKR)
- BTC-anchored pricing
- Velocity multiplier
- Spread and fee calculation

**3. CryptoPaymentVerifier**
- `monitorWallets()` - Monitor all admin wallets
- `monitorEthereumWallet()` - Monitor ERC20 USDT
- `monitorTronWallet()` - Monitor TRC20 USDT
- `monitorBitcoinWallet()` - Monitor BTC
- `processEthereumTransaction()` - Process ERC20 tx
- `processTronTransaction()` - Process TRC20 tx
- `processBitcoinTransaction()` - Process BTC tx
- `creditUserTokens()` - Credit tokens and log transaction

**4. CryptoMonitor**
- `checkEthereumPayments()` - Check ERC20 payments
- `checkTronPayments()` - Check TRC20 payments
- `checkBtcPayments()` - Check BTC payments

**5. ResellerService**
- `createApplication()` - Create reseller application
- `getApplications()` - Get applications with pagination
- `updateStatus()` - Update application status
- `getInvestmentCapacityOptions()` - Get capacity options

**6. EmailService**
- `sendContactNotification()` - Send contact emails
- `sendResellerNotification()` - Send reseller application emails
- `sendWelcomeEmail()` - Send newsletter welcome
- `sendNewsletter()` - Send newsletter to all subscribers

**7. ChatService**
- `createPrivateChat()` - Create private chat
- `createGroupChat()` - Create group chat
- `sendMessage()` - Send message
- `deleteMessage()` - Delete message
- `markAsRead()` - Mark messages as read
- `getUserChats()` - Get user's chats
- `togglePin()`, `toggleMute()`, `toggleArchive()` - Chat actions
- `searchUsers()` - Search users for chat

**Note:** Chat system is currently disabled.

**8. QrCodeService**
- `generateWalletQrCode()` - Generate QR code
- `generateWalletQrCodeWithLabel()` - Generate QR with label

**9. ContactService**
- `createContact()` - Create contact submission
- `getContacts()` - Get contacts with pagination
- `updateStatus()` - Update contact status

**10. NewsletterService**
- `createSubscription()` - Create/update subscription
- `getActiveSubscriptions()` - Get active subscribers
- `unsubscribe()` - Unsubscribe email
- `getStatistics()` - Get subscription stats

**11. TabAuthService**
- `user()` - Get authenticated user for tab
- `setTabUser()` - Set tab user
- `clearTabUser()` - Clear tab user
- `hasTabUser()` - Check if tab has user

---

## Middleware & Security

### Custom Middleware

**1. RoleMiddleware**
- Checks user role against required roles
- Supports multiple roles (comma-separated)
- JSON response for API requests
- Redirect to home for unauthorized

**2. EnsureKycApproved**
- Checks KYC approval status
- Allows access to KYC routes
- Redirects to KYC page if not approved

**3. EnsureAdminTwoFactorEnabled**
- Enforces 2FA for admin users
- Redirects to 2FA setup if not enabled
- JSON response for AJAX requests

**4. SecurityHeaders**
- Sets Content-Security-Policy
- X-Frame-Options (DENY/SAMEORIGIN)
- X-Content-Type-Options (nosniff)
- Referrer-Policy
- Permissions-Policy
- Allows embedding for specific routes (whitepaper, receipts)

**5. TabSessionAuth**
- Manages tab-specific authentication
- Cookie-based tab identification
- Independent session per tab

**6. ForceReauthInNewTabs**
- Forces re-authentication in new tabs
- Prevents session sharing

**7. EnsureNotInGame**
- Prevents certain actions while in game
- Checks `is_in_game` flag

### Security Features

**Authentication:**
- Laravel Fortify (email/password)
- 2FA for admin users (TOTP)
- Recovery codes
- Email verification (OTP-based)
- Password reset via email

**Authorization:**
- Role-based access control (RBAC)
- Route-level permissions
- Resource-level ownership checks

**Data Protection:**
- CSRF protection
- XSS protection (Blade escaping)
- SQL injection protection (Eloquent ORM)
- File upload validation
- Path traversal protection (KYC files)

**Security Headers:**
- CSP (Content Security Policy)
- X-Frame-Options
- X-Content-Type-Options
- Referrer-Policy
- Permissions-Policy

**Rate Limiting:**
- Login: 5 attempts/minute
- Contact form: 3 attempts/hour
- Newsletter: 6 attempts/hour
- OTP verification: Custom throttle

**ULID URLs:**
- Public-facing URLs use ULID instead of numeric IDs
- Prevents enumeration attacks
- Format: `/a/u/{ulid}`, `/a/p/{ulid}`, etc.

---

## Roles & Dashboards

### Role System

**1. Admin Role**
- Full system access
- User management (CRUD)
- Payment approval/rejection
- KYC management
- Price management
- Withdrawal management
- Reseller application approval
- System analytics
- **2FA Required** for dashboard access

**Dashboard Features:**
- User metrics (total, new, by role)
- Payment metrics (pending, total)
- KYC metrics (pending, total)
- Withdrawal metrics
- Application metrics
- Recent applications list

**2. Reseller Role**
- Manage referred users
- Approve payments for users
- Sell tokens to users (OTP protected)
- Set custom coin prices
- View commission earnings
- Manage buy requests
- View transaction history

**Dashboard Features:**
- Total referred users
- Total payments
- Total commission earned
- Token balance
- Portfolio value (average purchase price)
- Recent transactions
- Pending buy requests
- My users list

**3. Investor Role**
- Purchase tokens
- View transaction history
- Submit withdrawals
- Submit payments
- Manage profile
- Access games (KYC required)

**Dashboard Features:**
- Token balance
- Portfolio value
- Recent payments
- Recent transactions
- Pending buy requests

**4. User Role (Default)**
- Basic account
- Limited access
- Must complete KYC to become investor

---

## Features & Functionality

### Payment System

**1. Crypto Payments**
- Networks: TRC20, ERC20, BEP20, BTC, BNB
- Manual submission with tx_hash
- Screenshot upload
- Admin/Reseller approval
- Automatic token crediting
- Reseller commission calculation

**2. Manual/Bank Payments**
- Bank transfer submission
- Receipt upload
- Assignment to admin or reseller
- Reseller approval (transfers from reseller balance)
- Transaction logging

**3. Cash Payments**
- Reseller confirms cash received
- Automatic verification
- No proof required

### Token Management

**1. Token Balance System**
- Real-time balance tracking
- Transaction history
- Balance reconciliation
- Auto-fix discrepancies
- Price tracking per transaction

**2. Token Transfers**
- Admin transfers (credit/debit)
- Reseller sells to users
- Users buy from resellers
- Commission awards
- Withdrawal processing

### KYC System

**1. Document Upload**
- ID front (required)
- ID back (required for CNIC/NICOP)
- Selfie (required)
- Atomic uploads (all-or-nothing)
- Secure file storage
- Path validation

**2. Verification Process**
- Admin review
- Approval/rejection with reason
- Role upgrade (user → investor)
- Email notifications
- Document download

### Gaming Platform

**1. Trading Game**
- BTC-anchored pricing
- Real-time price updates
- BUY/SELL trades
- Fee calculation
- Spread revenue
- Session management
- Balance locking

**2. FOPI Game (Future of Property Investment)**
- Real estate simulation
- Property buying/selling
- Rent collection
- Month progression
- Property appreciation
- Installment plans
- Achievement system
- Mission system
- FOPI to RWAMP conversion

**3. Game Security**
- Game-specific PINs
- PIN lockout (3 attempts)
- Balance locking on entry
- Session state management
- Force reset for stuck states

### Reseller Network

**1. Application Process**
- Online application form
- Admin approval
- Account creation
- Email notification

**2. Commission System**
- Automatic commission on user purchases
- Configurable commission rate
- Transaction logging
- Balance tracking

**3. Reseller Features**
- Custom coin prices
- User management
- Payment approval
- Token sales
- Buy request management

### Withdrawal System

**1. Withdrawal Request**
- KYC required
- Immediate token deduction
- Admin review
- Manual transfer
- Receipt upload
- Status tracking

**2. Withdrawal Management**
- Approval/rejection
- Amount adjustment
- Token refund on rejection
- Email notifications
- Receipt management

---

## Views & Resources

### View Structure

**Admin Views:**
- `dashboard/admin.blade.php` - Admin dashboard
- `dashboard/admin-users.blade.php` - User management
- `dashboard/admin-crypto.blade.php` - Crypto payments
- `dashboard/admin-history.blade.php` - Payment/transaction history
- `dashboard/admin-kyc.blade.php` - KYC management
- `dashboard/admin-withdrawals.blade.php` - Withdrawal management
- `dashboard/admin-applications.blade.php` - Reseller applications
- `dashboard/admin-prices.blade.php` - Price management
- `dashboard/admin-sell.blade.php` - Admin sell coins

**Reseller Views:**
- `dashboard/reseller.blade.php` - Reseller dashboard
- `dashboard/reseller-users.blade.php` - My users
- `dashboard/reseller-payments.blade.php` - Payment approvals
- `dashboard/reseller-transactions.blade.php` - Transaction history
- `dashboard/reseller-sell.blade.php` - Sell coins
- `dashboard/reseller-buy-requests.blade.php` - Buy requests

**Investor Views:**
- `dashboard/investor.blade.php` - Investor dashboard
- `dashboard/user-history.blade.php` - Transaction history
- `dashboard/user-withdrawals.blade.php` - Withdrawal history
- `dashboard/payment-submit.blade.php` - Payment submission
- `dashboard/buy-from-reseller.blade.php` - Buy from reseller

**Game Views:**
- `game/select.blade.php` - Game selection
- `game/index.blade.php` - Trading game
- `game/fopi.blade.php` - FOPI game

**Public Views:**
- `pages/index.blade.php` - Home page
- `pages/about.blade.php` - About page
- `pages/contact.blade.php` - Contact page
- `pages/purchase.blade.php` - Purchase page
- `pages/purchase-guest.blade.php` - Purchase (guest)

**Auth Views:**
- `auth/login.blade.php` - Login
- `auth/register.blade.php` - Registration
- `auth/verify-email.blade.php` - Email verification
- `auth/passwords/reset.blade.php` - Password reset
- `auth/profile.blade.php` - Profile
- `auth/kyc.blade.php` - KYC form

**Components:**
- `components/navbar.blade.php` - Navigation
- `components/admin-sidebar.blade.php` - Admin sidebar
- `components/reseller-sidebar.blade.php` - Reseller sidebar
- `components/investor-sidebar.blade.php` - Investor sidebar
- `components/purchase-modal.blade.php` - Purchase modal
- `components/buy-from-reseller-modal.blade.php` - Buy modal

---

## Commands & Scheduled Tasks

### Artisan Commands (10 files)

**1. AuditKycImages**
- Audits KYC image files
- Checks for missing files
- Validates file paths

**2. BackfillUlids**
- Backfills ULID for existing records
- Generates ULID for models using HasUlid trait

**3. GenerateMissingWallets**
- Generates wallet addresses for users without one
- 16-digit numeric format

**4. MonitorCryptoPayments**
- Monitors blockchain for payments
- Processes detected transactions
- Credits tokens automatically

**5. PruneGamePriceHistory**
- Removes old price history records
- Keeps recent records only

**6. ReconcileUserBalances**
- Reconciles user balances with transactions
- Fixes discrepancies
- Logs reconciliation results

**7. ResetStuckGameStates**
- Resets stuck game sessions
- Clears `is_in_game` flags
- Marks sessions as abandoned

**8. SyncMissingUsers**
- Syncs missing user data
- Backfills required fields

**9. UpdateExchangeRate**
- Updates USD/PKR exchange rate
- Fetches from external API
- Updates config/cache

**10. (Additional commands may exist)**

### Scheduled Tasks

Configured in `app/Console/Kernel.php`:
- Crypto payment monitoring (if enabled)
- Exchange rate updates
- Game state cleanup
- Balance reconciliation (if enabled)

---

## Configuration & Dependencies

### Key Dependencies

**Core:**
- `laravel/framework: ^10.0`
- `laravel/fortify: ^1.31` (Authentication & 2FA)
- `laravel/sanctum: ^3.2` (API tokens)
- `php: ^8.1`

**Utilities:**
- `endroid/qr-code: ^6.0` (QR code generation)
- `guzzlehttp/guzzle: ^7.10` (HTTP client)
- `pusher/pusher-php-server: ^7.2` (Real-time features)

**Development:**
- `laravel/pint: ^1.0` (Code formatting)
- `phpunit/phpunit: ^10.1` (Testing)

### Configuration Files

**Key Config Files:**
- `config/crypto.php` - Crypto wallet addresses, API keys, rates
- `config/fortify.php` - Authentication configuration
- `config/auth.php` - Authentication guards
- `config/mail.php` - Email configuration
- `config/database.php` - Database configuration
- `config/filesystems.php` - File storage configuration

### Environment Variables

**Required:**
- `APP_NAME`, `APP_ENV`, `APP_KEY`
- `DB_*` (Database credentials)
- `MAIL_*` (Email configuration)
- `CRYPTO_*` (Crypto wallet addresses, API keys)

**Optional:**
- `ADMIN_EMAIL` - Admin notification email
- `PUSHER_*` - Pusher configuration
- `TAWK_TO_*` - Tawk.to chat configuration

---

## Security Considerations

### Implemented Security Measures

1. **Authentication & Authorization**
   - Strong password hashing (bcrypt)
   - 2FA for admin users
   - Role-based access control
   - Session management

2. **Data Protection**
   - CSRF tokens
   - XSS protection (Blade)
   - SQL injection protection (Eloquent)
   - File upload validation
   - Path traversal protection

3. **Security Headers**
   - Content-Security-Policy
   - X-Frame-Options
   - X-Content-Type-Options
   - Referrer-Policy

4. **URL Security**
   - ULID-based URLs (prevents enumeration)
   - Signed URLs for sensitive actions

5. **Rate Limiting**
   - Login attempts
   - Form submissions
   - API endpoints

### Recommendations

1. **Regular Security Audits**
   - Review file upload handlers
   - Audit KYC file access
   - Check balance reconciliation logic

2. **Monitoring**
   - Log all balance changes
   - Monitor failed login attempts
   - Track payment approvals

3. **Backup & Recovery**
   - Regular database backups
   - File storage backups
   - Transaction log retention

---

## Performance Considerations

### Optimizations

1. **Caching**
   - Price data (30s-1h)
   - Exchange rates (1h)
   - Game parameters

2. **Database**
   - Indexed foreign keys
   - Pagination for large datasets
   - Eager loading relationships

3. **File Storage**
   - Local storage for sensitive files
   - Public storage for assets
   - Secure file serving

### Potential Improvements

1. **Query Optimization**
   - Review N+1 queries
   - Add database indexes
   - Optimize complex queries

2. **Caching Strategy**
   - Cache user balances (with invalidation)
   - Cache dashboard metrics
   - Cache game prices

3. **Background Jobs**
   - Move heavy operations to queues
   - Async email sending
   - Background payment processing

---

## Conclusion

The RWAMP Laravel application is a comprehensive cryptocurrency token platform with:

- **Robust Architecture:** Well-structured MVC pattern
- **Multi-Role System:** Admin, Reseller, Investor roles
- **Payment Processing:** Multiple payment methods
- **Gaming Platform:** Trading and FOPI games
- **Security:** Multiple security layers
- **Scalability:** Caching, pagination, optimization

**Key Strengths:**
- Comprehensive transaction logging
- Balance reconciliation system
- ULID-based security
- Role-based access control
- Game state management

**Areas for Enhancement:**
- Background job processing
- API documentation
- Test coverage
- Performance monitoring
- Automated testing

---

**Document Version:** 1.0  
**Last Updated:** {{ date('Y-m-d H:i:s') }}  
**Analyzed By:** AI Code Analysis System

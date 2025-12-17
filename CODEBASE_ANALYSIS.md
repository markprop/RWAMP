# RWAMP Laravel Application - Complete Codebase Analysis

## Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture & Technology Stack](#architecture--technology-stack)
3. [Directory Structure](#directory-structure)
4. [Models (Database Entities)](#models-database-entities)
5. [Controllers (Business Logic)](#controllers-business-logic)
6. [Services (Reusable Business Logic)](#services-reusable-business-logic)
7. [Middleware (Request Processing)](#middleware-request-processing)
8. [Routes & API Endpoints](#routes--api-endpoints)
9. [Key Features & Modules](#key-features--modules)
10. [Security Features](#security-features)
11. [Database Schema](#database-schema)

---

## Project Overview

**RWAMP** (Real World Asset Management Platform) is a Laravel-based cryptocurrency token platform that enables:
- Real estate investment tokenization
- Crypto payment processing (USDT, BTC)
- Reseller/Agent network management
- KYC (Know Your Customer) verification
- Trading game simulation
- User wallet management
- Withdrawal system

### Core Functionality
- **User Management**: Three roles - Admin, Reseller, Investor
- **Payment Processing**: Crypto payments via USDT (TRC20/ERC20/BEP20) and BTC
- **Reseller Network**: Agent application system with commission tracking
- **Trading Game**: Simulated trading platform with real-time price engine
- **KYC System**: Document verification for compliance
- **Wallet System**: 16-digit unique wallet addresses for users

---

## Architecture & Technology Stack

### Framework & Core
- **Laravel 10.x** - PHP Framework
- **PHP 8.1+** - Programming Language
- **MySQL/MariaDB** - Database
- **Laravel Fortify** - Authentication
- **Laravel Sanctum** - API Authentication

### Key Dependencies
- `endroid/qr-code` - QR code generation
- `guzzlehttp/guzzle` - HTTP client for API calls
- `pusher/pusher-php-server` - Real-time features (chat system)

### Architecture Pattern
- **MVC (Model-View-Controller)**
- **Service Layer Pattern** - Business logic in Services
- **Repository Pattern** - Data access abstraction
- **Middleware Pattern** - Request filtering

---

## Directory Structure

```
app/
├── Actions/Fortify/          # Fortify authentication actions
├── Concerns/                 # Shared traits (HasUlid)
├── Console/Commands/         # Artisan commands
├── Events/                   # Event classes
├── Exceptions/               # Exception handlers
├── Helpers/                  # Helper classes (PriceHelper)
├── Http/
│   ├── Controllers/          # All controllers
│   │   ├── Admin/           # Admin-specific controllers
│   │   ├── Investor/        # Investor controllers
│   │   ├── Reseller/       # Reseller controllers
│   │   └── Auth/           # Authentication controllers
│   └── Middleware/          # Custom middleware
├── Mail/                     # Email classes
├── Models/                   # Eloquent models
├── Providers/               # Service providers
├── Rules/                   # Custom validation rules
├── Services/                # Business logic services
└── Traits/                  # Reusable traits
```

---

## Models (Database Entities)

### 1. User Model (`app/Models/User.php`)

**Purpose**: Core user entity with authentication, roles, and wallet management.

**Key Attributes**:
- `name`, `email`, `password`, `phone`
- `role` - 'admin', 'reseller', 'investor'
- `wallet_address` - 16-digit unique address
- `token_balance` - RWAMP token balance
- `coin_price` - User's coin price (for resellers)
- `referral_code` - Unique referral code (RSL{id} for resellers)
- `reseller_id` - Linked reseller (for investors)
- `kyc_status` - 'pending', 'approved', 'rejected'
- `kyc_*` fields - KYC document paths
- `game_pin_hash` - Hashed 4-digit game PIN
- `is_in_game` - Boolean flag for active game session

**Key Methods**:
- `addTokens($amount, $description)` - Credit tokens and log transaction
- `deductTokens($amount, $description)` - Debit tokens with validation
- `hasSufficientTokens($amount)` - Check balance
- `calculateBalanceFromTransactions()` - Reconcile balance from transaction history
- `reconcileBalance()` - Check balance consistency
- `fixBalanceFromTransactions()` - Auto-fix balance discrepancies
- `setGamePin($pin)` - Set 4-digit game PIN (bcrypt hashed)
- `verifyGamePin($pin)` - Verify PIN with lockout (3 attempts = 5min lock)
- `canEnterGame()` - Check KYC approval and PIN status
- `isAdmin()` - Role check helper

**Relationships**:
- `transactions()` - HasMany Transaction
- `cryptoPayments()` - HasMany CryptoPayment
- `reseller()` - BelongsTo User (self-referential)
- `referredUsers()` - HasMany User (self-referential)
- `gameSessions()` - HasMany GameSession
- `activeGameSession()` - HasOne GameSession (active)
- `chats()` - BelongsToMany Chat (via chat_participants)

**Security Features**:
- Password hashing (bcrypt)
- Two-factor authentication (Laravel Fortify)
- Game PIN with lockout mechanism
- Email verification

---

### 2. CryptoPayment Model (`app/Models/CryptoPayment.php`)

**Purpose**: Tracks cryptocurrency payment submissions for token purchases.

**Key Attributes**:
- `user_id` - Foreign key to users
- `token_amount` - RWAMP tokens purchased (string for precision)
- `usd_amount` - USD equivalent
- `pkr_amount` - PKR equivalent
- `coin_price_rs` - Price per token in PKR
- `network` - 'TRC20', 'ERC20', 'BEP20', 'BTC'
- `tx_hash` - Blockchain transaction hash
- `screenshot` - Payment proof image path
- `status` - 'pending', 'approved', 'rejected'
- `reseller_commission_awarded` - Boolean flag

**Relationships**:
- `user()` - BelongsTo User

**ULID Support**: Uses `HasUlid` trait for URL-friendly identifiers

---

### 3. Transaction Model (`app/Models/Transaction.php`)

**Purpose**: Comprehensive transaction ledger for all token movements.

**Key Attributes**:
- `user_id` - Foreign key
- `type` - 'credit', 'debit', 'crypto_purchase', 'commission', etc.
- `amount` - Token amount (positive/negative)
- `price_per_coin` - Price at transaction time
- `total_price` - Total value in PKR
- `status` - 'pending', 'completed', 'failed'
- `reference` - Reference ID or description
- `payment_type` - Network type
- `payment_hash` - Transaction hash
- `payment_status` - Payment approval status

**Purpose**: Provides complete audit trail of all balance changes.

---

### 4. ResellerApplication Model (`app/Models/ResellerApplication.php`)

**Purpose**: Manages reseller/agent application submissions.

**Key Attributes**:
- `name`, `email`, `phone`, `company`
- `investment_capacity` - '1-10k', '10-50k', '50-100k', '100k+'
- `experience` - Years of experience
- `message` - Application message
- `status` - 'pending', 'approved', 'rejected'
- `ip_address`, `user_agent` - Security tracking

**Workflow**:
1. User submits application
2. Admin reviews and approves/rejects
3. On approval: User account created/updated with 'reseller' role
4. Email notification sent with password reset link

---

### 5. GameSession Model (`app/Models/GameSession.php`)

**Purpose**: Manages trading game sessions for users.

**Key Attributes**:
- `user_id` - Foreign key
- `real_balance_start` - Real RWAMP tokens staked
- `game_balance_start` - Game tokens (10x multiplier)
- `game_balance_end` - Final game balance
- `real_balance_end` - Final real balance returned
- `anchor_btc_usd` - BTC/USD price at session start
- `anchor_mid_price` - RWAMP mid price at anchor
- `status` - 'active', 'completed', 'abandoned'
- `total_platform_revenue` - Fees collected
- `net_user_pnl_pkr` - User profit/loss

**Key Methods**:
- `calculateCurrentBalance()` - Calculate current RWAMP token balance from trades
- `calculateCurrentPkrBalance()` - Calculate PKR balance from trades

**Relationships**:
- `user()` - BelongsTo User
- `trades()` - HasMany GameTrade
- `priceHistory()` - HasMany GamePriceHistory

**Game Mechanics**:
- User stakes real RWAMP tokens
- Gets 10x game tokens for trading
- Trades RWAMP ↔ PKR with dynamic pricing
- On exit: Game balance / 100 = real tokens returned
- Platform collects fees on each trade

---

### 6. GameTrade Model (`app/Models/GameTrade.php`)

**Purpose**: Records individual buy/sell trades within game sessions.

**Key Attributes**:
- `session_id` - Foreign key to GameSession
- `side` - 'BUY' or 'SELL'
- `quantity` - Token amount traded
- `price_pkr` - Price per token
- `fee_pkr` - Transaction fee
- `spread_revenue_pkr` - Platform revenue from spread
- `game_balance_after` - Balance after trade
- `idempotency_key` - Prevents duplicate trades

---

### 7. GamePriceHistory Model (`app/Models/GamePriceHistory.php`)

**Purpose**: Historical price data for game sessions (every 5 seconds).

**Key Attributes**:
- `session_id` - Foreign key
- `mid_price`, `buy_price`, `sell_price`
- `btc_usd`, `usd_pkr`
- `recorded_at` - Timestamp

---

### 8. BuyFromResellerRequest Model (`app/Models/BuyFromResellerRequest.php`)

**Purpose**: Manages buy requests from investors to resellers.

**Key Attributes**:
- `user_id` - Investor requesting
- `reseller_id` - Reseller selling
- `coin_quantity` - Tokens requested
- `coin_price` - Price per token
- `total_amount` - Total PKR amount
- `status` - 'pending', 'approved', 'rejected', 'completed'

**Workflow**:
1. Investor creates request with OTP verification
2. Reseller approves/rejects
3. On approval: Tokens transferred, transaction logged

---

### 9. WithdrawRequest Model (`app/Models/WithdrawRequest.php`)

**Purpose**: Manages withdrawal requests from users.

**Key Attributes**:
- `user_id` - Foreign key
- `amount` - Withdrawal amount in PKR
- `wallet_address` - Destination wallet
- `status` - 'pending', 'approved', 'rejected', 'completed'
- `admin_receipt_path` - Receipt uploaded by admin
- `admin_notes` - Admin notes

---

### 10. Chat Models (Chat, ChatMessage, ChatParticipant, ChatMessageRead)

**Purpose**: Real-time chat system (currently disabled).

**Features**:
- Private and group chats
- Message reactions
- Read receipts
- File attachments (receipts, voice messages)
- Pin/mute/archive functionality

**Status**: System is disabled but code remains intact.

---

## Controllers (Business Logic)

### Admin Controllers

#### 1. AdminController (`app/Http/Controllers/AdminController.php`)

**Purpose**: Main admin dashboard and legacy reseller application approval.

**Key Methods**:
- `dashboard()` - Main admin dashboard with metrics
  - User counts (total, resellers, investors)
  - New users (7 days, 30 days)
  - Application statistics
  - KYC statistics
  - Payment statistics
  - Withdrawal statistics

**Metrics Tracked**:
- Total users, resellers, investors
- New registrations (7d, 30d)
- Pending/approved/rejected applications
- Pending KYC submissions
- Crypto payment counts
- Withdrawal requests

---

#### 2. AdminResellerApplicationController (`app/Http/Controllers/Admin/AdminResellerApplicationController.php`)

**Purpose**: Manages reseller application workflow.

**Key Methods**:

**`index(Request $request)`**
- Lists all reseller applications
- Search: name, email, phone, company
- Filters: status, investment capacity
- Sorting: name, email, created_at, status
- Pagination: 15 per page

**`show(ResellerApplication $application)`**
- Returns JSON with application details
- Includes associated user info if exists
- Used for modal display

**`approve(Request $request, ResellerApplication $application)`**
- Approves pending application
- Creates/updates user with 'reseller' role
- Generates unique 16-digit wallet address
- Sets default password: 'RWAMP@agent'
- Generates referral code: 'RSL{user_id}'
- Creates password reset token
- Sends approval email with reset link
- Sets password reset required flag in cache

**`reject(Request $request, ResellerApplication $application)`**
- Rejects application
- Sends rejection email

**`update(Request $request, ResellerApplication $application)`**
- Updates application details
- Validates: name, email, phone, company, investment_capacity, message, status

**`destroy(Request $request, ResellerApplication $application)`**
- Deletes application
- Returns success message with applicant details

---

#### 3. AdminUserController (`app/Http/Controllers/Admin/AdminUserController.php`)

**Purpose**: User management for admins.

**Key Methods**:
- `index()` - List all users with search/filter
- `show($user)` - User details (ULID-based)
- `store()` - Create new user
- `update()` - Update user details
- `destroy()` - Delete user
- `resetPassword()` - Reset user password
- `assignWalletAddress()` - Assign/generate wallet address

**Features**:
- ULID-based URLs for security
- Search by name, email, phone
- Filter by role, KYC status
- Bulk operations support

---

#### 4. AdminCryptoPaymentController (`app/Http/Controllers/Admin/AdminCryptoPaymentController.php`)

**Purpose**: Manages crypto payment approvals.

**Key Methods**:
- `index()` - List all payments with filters
- `show($payment)` - Payment details (ULID-based)
- `update()` - Update payment status/notes
- `approve()` - Approve payment
  - Credits tokens to user
  - Creates transaction record
  - Awards reseller commission (if applicable)
  - Sends confirmation email
- `reject()` - Reject payment with reason
- `destroy()` - Delete payment
- `downloadScreenshot()` - Download payment proof
- `history()` - Payment history view

**Approval Process**:
1. Admin reviews payment proof
2. Verifies transaction hash on blockchain
3. Approves → Tokens credited
4. Rejects → User notified

---

#### 5. AdminKycController (`app/Http/Controllers/Admin/AdminKycController.php`)

**Purpose**: KYC document review and approval.

**Key Methods**:
- `index()` - List KYC submissions
- `approve($user)` - Approve KYC
  - Sets `kyc_status = 'approved'`
  - Sets `kyc_approved_at` timestamp
  - Sends approval email
- `reject($user)` - Reject KYC with reason
- `update()` - Update KYC details
- `destroy()` - Delete KYC submission
- `downloadFile($user, $type)` - Download ID front/back/selfie

**KYC Documents**:
- ID Front (kyc_id_front_path)
- ID Back (kyc_id_back_path)
- Selfie (kyc_selfie_path)

---

#### 6. AdminWithdrawalController (`app/Http/Controllers/Admin/AdminWithdrawalController.php`)

**Purpose**: Manages withdrawal requests.

**Key Methods**:
- `index()` - List withdrawals with filters
- `show($withdrawal)` - Withdrawal details
- `approve()` - Approve withdrawal
  - Deducts tokens from user
  - Creates transaction record
  - Marks as completed
- `reject()` - Reject with reason
- `submitReceipt()` - Upload payment receipt
- `update()` - Update withdrawal details
- `destroy()` - Delete withdrawal

---

#### 7. AdminPriceController (`app/Http/Controllers/Admin/AdminPriceController.php`)

**Purpose**: Manages RWAMP token pricing.

**Key Methods**:
- `index()` - Display current prices
- `update()` - Update token price in PKR
  - Updates config/system settings
  - Clears price cache
  - Logs price change

**Price Management**:
- RWAMP price in PKR (admin-set)
- Auto-calculated USD price
- USDT/BTC prices (API-fetched)

---

#### 8. AdminSellController (`app/Http/Controllers/Admin/AdminSellController.php`)

**Purpose**: Admin can sell coins directly to users.

**Key Methods**:
- `index()` - Display sell interface
- `store()` - Process coin sale
  - Validates user, amount, OTP
  - Deducts from admin balance
  - Credits to user
  - Creates transaction record
- `searchUsers()` - Search users by wallet/email
- `sendOtp()` - Send OTP for verification
- `fetchPaymentProof()` - Get payment proof

**Security**: OTP verification required for all sales.

---

#### 9. Admin2FAController (`app/Http/Controllers/Admin/Admin2FAController.php`)

**Purpose**: Two-factor authentication for admins.

**Key Methods**:
- `show()` - Display 2FA setup page
- `regenerateRecoveryCodes()` - Generate new recovery codes

**Security**: All admin routes require 2FA (via `EnsureAdminTwoFactorEnabled` middleware).

---

#### 10. AdminChatController (`app/Http/Controllers/Admin/AdminChatController.php`)

**Purpose**: Admin chat management (currently disabled).

**Status**: Chat system is disabled but controller remains.

---

### Investor Controllers

#### 1. InvestorDashboardController (`app/Http/Controllers/Investor/InvestorDashboardController.php`)

**Purpose**: Investor dashboard with portfolio overview.

**Key Methods**:
- `index()` - Main dashboard
  - Token balance
  - Portfolio value (at purchase price vs official price)
  - Average purchase price calculation
  - Recent payments
  - Recent transactions
  - Pending buy requests

**Metrics**:
- Token balance
- Portfolio value (weighted average)
- Official portfolio value (current price)
- Average purchase price

---

#### 2. InvestorHistoryController (`app/Http/Controllers/Investor/InvestorHistoryController.php`)

**Purpose**: Transaction history for investors.

**Key Methods**:
- `index()` - Display history
  - Crypto payments
  - Transactions
  - Buy requests
  - Search and filters

---

### Reseller Controllers

#### 1. ResellerDashboardController (`app/Http/Controllers/Reseller/ResellerDashboardController.php`)

**Purpose**: Reseller dashboard.

**Key Methods**:
- `index()` - Main dashboard
  - Token balance
  - Recent sales
  - Pending buy requests
  - Commission earnings
  - User referrals

---

#### 2. ResellerUserController (`app/Http/Controllers/Reseller/ResellerUserController.php`)

**Purpose**: Manage reseller's referred users.

**Key Methods**:
- `index()` - List referred users
- `show($user)` - User details

---

#### 3. ResellerPaymentController (`app/Http/Controllers/Reseller/ResellerPaymentController.php`)

**Purpose**: Manage payments from reseller's users.

**Key Methods**:
- `index()` - List payments
- `show($payment)` - Payment details
- `approve()` - Approve payment
- `reject()` - Reject payment
- `fetchUserPaymentProof()` - Get payment screenshot

---

#### 4. ResellerSellController (`app/Http/Controllers/Reseller/ResellerSellController.php`)

**Purpose**: Reseller can sell coins to users.

**Key Methods**:
- `index()` - Display sell interface
- `store()` - Process sale
  - OTP verification
  - Balance check
  - Token transfer
  - Transaction logging
- `updateCoinPrice()` - Update reseller's coin price
- `searchUsers()` - Search users by wallet/email
- `sendOtp()` - Send OTP

---

#### 5. ResellerTransactionController (`app/Http/Controllers/Reseller/ResellerTransactionController.php`)

**Purpose**: Transaction history for resellers.

**Key Methods**:
- `index()` - List transactions
- `show($transaction)` - Transaction details

---

#### 6. ResellerBuyRequestController (`app/Http/Controllers/Reseller/ResellerBuyRequestController.php`)

**Purpose**: Manage buy requests from investors.

**Key Methods**:
- `index()` - List buy requests
- `approve()` - Approve request
  - Transfers tokens
  - Creates transaction
  - Updates request status
- `reject()` - Reject request

---

### Public/Shared Controllers

#### 1. PageController (`app/Http/Controllers/PageController.php`)

**Purpose**: Public pages.

**Key Methods**:
- `index()` - Homepage
- `about()` - About page
- `contact()` - Contact page
- `becomePartner()` - Partner/reseller application page
- `whitepaper()` - Whitepaper page
- `serveWhitepaper()` - PDF download
- `howToBuy()` - Purchase instructions

---

#### 2. CryptoPaymentController (`app/Http/Controllers/CryptoPaymentController.php`)

**Purpose**: Crypto payment submission and processing.

**Key Methods**:

**`create()`**
- Display purchase page
- Shows current rates (RWAMP, USDT, BTC)
- Displays wallet addresses
- QR code generation support

**`generateQrCode($network)`**
- Generates QR code for wallet address
- Supports: TRC20, ERC20, BEP20, BTC

**`saveWalletAddress(Request $request)`**
- Saves user's wallet address
- Logs address updates

**`submitTxHash(Request $request)`**
- Submits transaction hash for payment
- Validates: tx_hash, network, token_amount, usd_amount, pkr_amount
- Creates CryptoPayment record (pending)
- Creates Transaction record
- Calculates coin_price_rs
- Prevents duplicate submissions
- Sends admin notification email

**`checkPaymentStatus()`**
- Checks if payment exists
- Returns payment status

**`checkAutoPaymentStatus()`**
- Checks for auto-detected payments (via monitoring)

**`userHistory()`**
- Payment history
- Transaction history
- Buy request history
- Search and filters

**`investorDashboard()`**
- Compact investor dashboard
- Portfolio metrics
- Recent activity

**`buyFromReseller()`**
- Direct purchase from reseller (OTP protected)
- Token transfer
- Transaction logging

**`createBuyFromResellerRequest()`**
- Creates buy request (OTP protected)
- Validates OTP
- Creates BuyFromResellerRequest record

**`searchResellers()`**
- Search resellers by name/email/code
- Returns JSON list

**`sendOtpForBuyRequest()`**
- Sends OTP for buy request verification

---

#### 3. GameController (`app/Http/Controllers/GameController.php`)

**Purpose**: Trading game management.

**Key Methods**:

**`select()`**
- Game selection page
- Checks prerequisites (KYC, PIN)

**`index()`**
- Main game interface
- Requires active session
- Displays current prices
- Shows game balance

**`setPin(Request $request)`**
- Sets 4-digit game PIN
- Validates format
- Bcrypt hashed

**`enter(Request $request)`**
- Enters game session
- Validates PIN
- Checks prerequisites
- Creates GameSession
  - Locks real balance (stake amount)
  - Creates game balance (stake × 10)
  - Sets anchor prices (BTC/USD, mid price)
- Deducts stake from real balance
- Sets `is_in_game = true`

**`price(Request $request)`**
- Returns current game prices
- Uses anchor prices for consistency
- Records price history (every 5 seconds)

**`trade(Request $request)`**
- Executes BUY or SELL trade
- Validates: side, quantity, idempotency_key
- Checks balance (RWAMP or PKR)
- Calculates fees
- Updates game balance
- Records GameTrade
- Calculates platform revenue

**`history(Request $request)`**
- Returns trade history
- Price history
- Session details

**`exit(Request $request)`**
- Exits game session
- Calculates final balance
- Converts game balance to real (÷ 100)
- Calculates P&L
- Credits real balance
- Marks session as completed
- Sets `is_in_game = false`

**`forceReset()`**
- Resets stuck game state
- Clears `is_in_game` flag
- Marks sessions as abandoned

**Game Mechanics**:
- User stakes real RWAMP tokens
- Gets 10x game tokens
- Trades RWAMP ↔ PKR
- Prices anchored to BTC movement
- On exit: Game balance ÷ 100 = real tokens
- Platform collects fees on trades

---

#### 4. KycController (`app/Http/Controllers/KycController.php`)

**Purpose**: KYC submission for users.

**Key Methods**:
- `show()` - Display KYC form
- `submit()` - Submit KYC documents
  - Validates files (ID front/back, selfie)
  - Stores in storage
  - Creates KYC record
  - Sets status to 'pending'
  - Sends submission email

---

#### 5. WithdrawController (`app/Http/Controllers/WithdrawController.php`)

**Purpose**: Withdrawal request management.

**Key Methods**:
- `index()` - List user's withdrawals
- `store()` - Create withdrawal request
  - Validates amount, wallet address
  - Checks balance
  - Creates WithdrawRequest (pending)
  - Sends notification
- `viewReceipt()` - View admin receipt

---

#### 6. BuyFromResellerController (`app/Http/Controllers/BuyFromReseller/BuyFromResellerController.php`)

**Purpose**: Buy tokens from resellers.

**Key Methods**:
- `index()` - Display buy interface
- `buy()` - Direct purchase (OTP)
- `createRequest()` - Create buy request (OTP)
- `sendOtp()` - Send OTP
- `search()` - Search resellers

---

#### 7. AuthController (`app/Http/Controllers/AuthController.php`)

**Purpose**: Authentication handling.

**Key Methods**:
- `showLogin()` - Display login form
- `login()` - Process login
  - Validates credentials
  - Handles tab session auth
  - Redirects based on role
- `logout()` - Process logout
  - Clears tab sessions
  - Invalidates sessions
- `showForgotPassword()` - Password reset form
- `checkReferralCode()` - Validate referral code (API)

---

#### 8. RegisterController (`app/Http/Controllers/Auth/Register/RegisterController.php`)

**Purpose**: User registration.

**Key Methods**:
- `show()` - Display registration form
- `store()` - Process registration
  - Validates: name, email, phone, password, referral_code
  - Creates user account
  - Links to reseller (if referral code)
  - Generates wallet address
  - Sends OTP for email verification
- `checkEmail()` - Check email availability (API)
- `checkPhone()` - Check phone availability (API)

---

#### 9. EmailVerificationController (`app/Http/Controllers/Auth/EmailVerificationController.php`)

**Purpose**: OTP-based email verification.

**Key Methods**:
- `show()` - Display verification form
- `verify()` - Verify OTP
  - Validates 6-digit OTP
  - Checks cache
  - Marks email as verified
- `resend()` - Resend OTP
- `generateAndSendOtp($email)` - Generate and send OTP
  - Creates 6-digit OTP
  - Stores in cache (10 minutes)
  - Sends email via VerifyOtpMail

---

#### 10. ProfileController (`app/Http/Controllers/ProfileController.php`)

**Purpose**: User profile management.

**Key Methods**:
- `show()` - Display profile
- `updateAccount()` - Update profile info
- `updatePassword()` - Change password
- `updateWallet()` - Update wallet address
- `generateWallet()` - Generate new wallet address
- `resendEmailVerification()` - Resend verification email

---

#### 11. ContactController (`app/Http/Controllers/ContactController.php`)

**Purpose**: Contact form submissions.

**Key Methods**:
- `store()` - Save contact submission
  - Validates: name, email, message
  - Stores in database
  - Sends notification email
  - Rate limited (3 per 60 seconds)

---

#### 12. ResellerController (`app/Http/Controllers/ResellerController.php`)

**Purpose**: Reseller application submissions.

**Key Methods**:
- `store()` - Submit reseller application
  - Validates application data
  - Creates ResellerApplication
  - Sends confirmation email
  - Rate limited (3 per 60 seconds)

---

#### 13. NewsletterController (`app/Http/Controllers/NewsletterController.php`)

**Purpose**: Newsletter subscriptions.

**Key Methods**:
- `store()` - Subscribe to newsletter
  - Validates email
  - Creates NewsletterSubscription
  - Rate limited (6 per 60 seconds)

---

#### 14. GameSettingController (`app/Http/Controllers/GameSettingController.php`)

**Purpose**: Game parameter management (admin only).

**Key Methods**:
- `show()` - Display settings
- `update()` - Update game parameters
  - tokens_per_btc
  - spread_pkr
  - buy_fee_pct
  - sell_fee_pct
  - velocity_multiplier

---

#### 15. WalletConnectController (`app/Http/Controllers/WalletConnectController.php`)

**Purpose**: Mobile wallet deep link handling.

**Key Methods**:
- `handleReturn()` - Process wallet connection return
- `checkStatus()` - Check connection status (API)

---

## Services (Reusable Business Logic)

### 1. CryptoPaymentVerifier (`app/Services/CryptoPaymentVerifier.php`)

**Purpose**: Monitors blockchain for incoming payments.

**Key Methods**:
- `monitorWallets()` - Monitor all admin wallets
- `monitorEthereumWallet($address)` - Monitor ERC20 USDT
- `monitorTronWallet($address)` - Monitor TRC20 USDT
- `monitorBitcoinWallet($address)` - Monitor BTC
- `processEthereumTransaction($tx)` - Process ERC20 transaction
- `processTronTransaction($tx)` - Process TRC20 transaction
- `processBitcoinTransaction($tx)` - Process BTC transaction
- `calculateRwampTokens($usdAmount)` - Convert USD to tokens
- `creditUserTokens()` - Credit tokens and log transaction

**APIs Used**:
- Alchemy API (Ethereum)
- TronGrid API (Tron)
- Blockstream API (Bitcoin)

---

### 2. GamePriceEngine (`app/Services/GamePriceEngine.php`)

**Purpose**: Real-time price calculation for trading game.

**Key Methods**:

**`getBtcUsdPrice()`**
- Fetches BTC/USD from Binance API
- Caches for 30 seconds
- Fallback to PriceHelper

**`getUsdPkrRate()`**
- Fetches USD/PKR from exchangerate-api
- Caches for 1 hour
- Fallback to PriceHelper

**`getGameParameters()`**
- Returns game settings from database
- Caches for performance
- Parameters:
  - tokens_per_btc
  - spread_pkr
  - buy_fee_pct
  - sell_fee_pct
  - velocity_multiplier

**`calculateAnchoredPrice($anchorBtcUsd, $anchorMidPrice, $currentBtcUsd, $velocityMultiplier)`**
- Calculates price based on BTC movement
- Formula: `mid = anchor_mid × (1 + (btc_change × velocity))`
- Ensures price never goes negative

**`calculatePrices($midPrice, $spreadPkr, $buyFeePct, $sellFeePct)`**
- Calculates buy/sell prices with spread
- Buy price = mid + spread/2
- Sell price = mid - spread/2

**`getCurrentPrices($anchorBtcUsd, $anchorMidPrice)`**
- Main method for getting live prices
- Uses anchor for consistency
- Returns: buy_price, sell_price, mid_price, btc_usd, usd_pkr

**Price Anchoring**:
- Session starts with anchor BTC/USD and mid price
- Prices move based on BTC percentage change
- Velocity multiplier controls sensitivity
- Ensures consistent pricing within session

---

### 3. QrCodeService (`app/Services/QrCodeService.php`)

**Purpose**: QR code generation for wallet addresses.

**Key Methods**:
- `generateWalletQrCode($address, $network)` - Generates QR code
  - Returns base64-encoded PNG
  - Includes network label

---

### 4. EmailService (`app/Services/EmailService.php`)

**Purpose**: Centralized email sending.

**Key Methods**:
- Email templates for various events
- Queue support for async sending

---

### 5. ResellerService (`app/Services/ResellerService.php`)

**Purpose**: Reseller-related business logic.

**Key Methods**:
- Commission calculation
- Referral tracking
- User linking

---

### 6. ChatService (`app/Services/ChatService.php`)

**Purpose**: Chat system logic (currently disabled).

---

### 7. ContactService (`app/Services/ContactService.php`)

**Purpose**: Contact form processing.

---

### 8. NewsletterService (`app/Services/NewsletterService.php`)

**Purpose**: Newsletter management.

---

### 9. TabAuthService (`app/Services/TabSessionAuth.php`)

**Purpose**: Multi-tab session management.

**Key Methods**:
- Detects new tabs
- Forces re-authentication in new tabs
- Manages tab sessions

---

### 10. CryptoMonitor (`app/Services/CryptoMonitor.php`)

**Purpose**: Background monitoring of crypto payments.

**Key Methods**:
- Scheduled monitoring
- Auto-detection of payments
- Notification system

---

## Middleware (Request Processing)

### 1. RoleMiddleware (`app/Http/Middleware/RoleMiddleware.php`)

**Purpose**: Role-based access control.

**Functionality**:
- Checks user authentication
- Validates user role against required roles
- Supports multiple roles: `role:admin,reseller`
- Returns 401/403 for API requests
- Redirects to login/home for web requests

**Usage**: `Route::middleware('role:admin')`

---

### 2. EnsureKycApproved (`app/Http/Middleware/EnsureKycApproved.php`)

**Purpose**: Ensures user has approved KYC.

**Functionality**:
- Checks `kyc_status === 'approved'`
- Redirects to KYC page if not approved
- Allows admins to bypass

**Usage**: `Route::middleware('kyc.approved')`

---

### 3. EnsureAdminTwoFactorEnabled (`app/Http/Middleware/EnsureAdminTwoFactorEnabled.php`)

**Purpose**: Requires 2FA for admin routes.

**Functionality**:
- Checks if admin has 2FA enabled
- Redirects to 2FA setup if not enabled
- Protects sensitive admin operations

**Usage**: `Route::middleware('admin.2fa')`

---

### 4. EnsureNotInGame (`app/Http/Middleware/EnsureNotInGame.php`)

**Purpose**: Prevents certain actions while in game.

**Functionality**:
- Checks `is_in_game` flag
- Blocks actions that shouldn't be done during game session

---

### 5. TabSessionAuth (`app/Http/Middleware/TabSessionAuth.php`)

**Purpose**: Multi-tab session management.

**Functionality**:
- Detects new browser tabs
- Forces re-authentication in new tabs
- Prevents session hijacking

---

### 6. ForceReauthInNewTabs (`app/Http/Middleware/ForceReauthInNewTabs.php`)

**Purpose**: Forces re-authentication in new tabs.

**Functionality**:
- Checks tab session token
- Redirects to login if new tab detected

---

### 7. SecurityHeaders (`app/Http/Middleware/SecurityHeaders.php`)

**Purpose**: Adds security headers to responses.

**Headers**:
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Referrer-Policy
- Content-Security-Policy

---

### 8. Authenticate (`app/Http/Middleware/Authenticate.php`)

**Purpose**: Laravel default authentication middleware.

---

### 9. RedirectIfAuthenticated (`app/Http/Middleware/RedirectIfAuthenticated.php`)

**Purpose**: Redirects authenticated users away from guest pages.

---

### 10. VerifyCsrfToken (`app/Http/Middleware/VerifyCsrfToken.php`)

**Purpose**: CSRF protection.

**Exemptions**: API routes, webhook endpoints

---

## Routes & API Endpoints

### Public Routes

```
GET  /                          - Homepage
GET  /about                     - About page
GET  /contact                   - Contact form
GET  /become-partner            - Reseller application page
GET  /whitepaper                - Whitepaper
GET  /how-to-buy                - Purchase instructions
GET  /privacy-policy            - Privacy policy
GET  /terms-of-service          - Terms of service
GET  /disclaimer                - Investment disclaimer
GET  /robots.txt                - Robots.txt
GET  /sitemap.xml               - Dynamic sitemap
POST /contact                   - Submit contact form
POST /reseller                  - Submit reseller application
POST /newsletter                - Subscribe to newsletter
```

### Authentication Routes

```
GET  /login                     - Login form
POST /login                     - Process login
GET  /register                  - Registration form
POST /register                  - Process registration
GET  /verify-email              - Email verification form
POST /verify-email              - Verify OTP
POST /verify-email/resend       - Resend OTP
GET  /forgot-password           - Password reset request
POST /forgot-password           - Send reset link
GET  /reset-password/{token}    - Reset password form
POST /reset-password            - Process reset
POST /logout                    - Logout
```

### Authenticated Routes

#### Investor Routes
```
GET  /dashboard/investor        - Investor dashboard
GET  /dashboard/history         - Transaction history
GET  /purchase                  - Purchase page
POST /api/save-wallet-address   - Save wallet address
POST /api/submit-tx-hash        - Submit payment
POST /api/check-payment-status  - Check payment status
GET  /kyc                       - KYC form
POST /kyc/submit                - Submit KYC
GET  /game                      - Game selection
GET  /game/trading              - Trading game
POST /game/set-pin              - Set game PIN
POST /game/enter                - Enter game
GET  /game/price                - Get prices
POST /game/trade                - Execute trade
GET  /game/history              - Game history
POST /game/exit                 - Exit game
POST /api/user/withdraw         - Create withdrawal
GET  /api/user/withdrawals      - List withdrawals
POST /api/user/buy-from-reseller - Buy from reseller
```

#### Reseller Routes
```
GET  /dashboard/reseller        - Reseller dashboard
GET  /dashboard/reseller/users  - Referred users
GET  /dashboard/reseller/payments - Payment approvals
POST /dashboard/reseller/payments/{payment}/approve - Approve payment
POST /dashboard/reseller/payments/{payment}/reject - Reject payment
GET  /dashboard/reseller/transactions - Transaction history
GET  /dashboard/reseller/sell   - Sell coins interface
POST /dashboard/reseller/sell   - Process sale
POST /dashboard/reseller/coin-price - Update coin price
GET  /dashboard/reseller/buy-requests - Buy requests
POST /dashboard/reseller/buy-requests/{request}/approve - Approve request
POST /dashboard/reseller/buy-requests/{request}/reject - Reject request
```

#### Admin Routes
```
GET  /dashboard/admin           - Admin dashboard
GET  /dashboard/admin/users     - User management
POST /a/u                       - Create user (ULID)
GET  /a/u/{user}                - User details (ULID)
PUT  /a/u/{user}                - Update user (ULID)
DELETE /a/u/{user}              - Delete user (ULID)
POST /a/u/{user}/reset-password - Reset password
POST /a/u/{user}/assign-wallet  - Assign wallet
GET  /dashboard/admin/applications - Reseller applications
GET  /a/ap/{application}/details - Application details (ULID)
PUT  /a/ap/{application}/approve - Approve application
PUT  /a/ap/{application}/reject  - Reject application
GET  /dashboard/admin/crypto-payments - Crypto payments
GET  /a/p/{payment}/details     - Payment details (ULID)
POST /a/p/{payment}/approve     - Approve payment
POST /a/p/{payment}/reject      - Reject payment
GET  /dashboard/admin/kyc       - KYC submissions
POST /dashboard/admin/kyc/{user}/approve - Approve KYC
POST /dashboard/admin/kyc/{user}/reject - Reject KYC
GET  /dashboard/admin/withdrawals - Withdrawals
POST /a/w/{withdrawal}/approve  - Approve withdrawal
POST /a/w/{withdrawal}/reject   - Reject withdrawal
GET  /dashboard/admin/prices    - Price management
POST /dashboard/admin/prices/update - Update price
GET  /dashboard/admin/sell      - Sell coins interface
POST /dashboard/admin/sell      - Process sale
GET  /a/g/settings              - Game settings (ULID)
POST /a/g/settings               - Update game settings
GET  /dashboard/admin/history   - Payment history
```

### API Routes

```
GET  /api/check-referral-code   - Check referral code
GET  /api/check-email            - Check email availability
GET  /api/check-phone            - Check phone availability
POST /api/verify-otp             - Verify OTP
GET  /api/resellers/search       - Search resellers
POST /api/users/lookup-by-wallet - Lookup user by wallet (admin/reseller)
GET  /api/wallet-connect-status  - Wallet connection status
```

---

## Key Features & Modules

### 1. User Management System

**Roles**:
- **Admin**: Full system access, 2FA required
- **Reseller**: Can sell coins, manage users, approve payments
- **Investor**: Can purchase coins, play trading game, withdraw

**Features**:
- Email verification (OTP-based)
- Password reset with secure tokens
- Two-factor authentication (admin only)
- Wallet address generation (16-digit unique)
- Referral system (reseller codes)
- KYC verification

---

### 2. Crypto Payment System

**Supported Networks**:
- TRC20 (Tron USDT)
- ERC20 (Ethereum USDT)
- BEP20 (Binance Smart Chain USDT)
- BTC (Bitcoin)

**Workflow**:
1. User selects network and amount
2. Calculates token amount based on current price
3. Displays wallet address and QR code
4. User sends crypto payment
5. User submits transaction hash
6. Admin reviews and approves
7. Tokens credited to user balance
8. Transaction logged

**Features**:
- Manual approval process
- Payment proof upload (screenshots)
- Transaction hash validation
- Duplicate prevention
- Reseller commission tracking
- Auto-monitoring (optional, via CryptoMonitor)

---

### 3. Reseller Network System

**Application Process**:
1. User submits reseller application
2. Admin reviews application
3. On approval:
   - User account created/updated
   - Role set to 'reseller'
   - Wallet address generated
   - Referral code created (RSL{id})
   - Password reset link sent
4. Reseller can:
   - Set custom coin price
   - Sell coins to users
   - Approve buy requests
   - Manage referred users
   - Earn commissions

**Commission System**:
- Resellers earn commission on referred user purchases
- Tracked in `reseller_commission_awarded` flag
- Calculated on payment approval

---

### 4. Trading Game System

**Purpose**: Simulated trading platform for users to practice trading.

**Mechanics**:
1. User stakes real RWAMP tokens
2. Receives 10x game tokens
3. Trades RWAMP ↔ PKR with dynamic pricing
4. Prices anchored to BTC movement
5. Platform collects fees on trades
6. On exit: Game balance ÷ 100 = real tokens returned

**Price Engine**:
- Anchored to BTC/USD price
- Mid price calculated: `(BTC_USD × USD_PKR) / tokens_per_btc`
- Buy price = mid + spread/2
- Sell price = mid - spread/2
- Velocity multiplier controls BTC sensitivity

**Security**:
- 4-digit PIN required
- PIN lockout after 3 failed attempts (5 minutes)
- Session-based balance tracking
- Idempotency keys prevent duplicate trades

**Features**:
- Real-time price updates (every 5 seconds)
- Price history tracking
- Trade history
- P&L calculation
- Platform revenue tracking

---

### 5. KYC Verification System

**Documents Required**:
- ID Front (government-issued)
- ID Back
- Selfie with ID

**Workflow**:
1. User uploads documents
2. Status set to 'pending'
3. Admin reviews documents
4. Admin approves/rejects
5. User notified via email

**Status Values**:
- `null` - Not submitted
- `pending` - Awaiting review
- `approved` - Verified
- `rejected` - Rejected with reason

**Access Control**:
- KYC required for game access
- KYC required for certain features (configurable)

---

### 6. Withdrawal System

**Workflow**:
1. User creates withdrawal request
2. Specifies amount and wallet address
3. Status set to 'pending'
4. Admin reviews request
5. Admin approves and processes payment
6. Admin uploads receipt
7. Status updated to 'completed'

**Features**:
- Balance validation
- Admin receipt upload
- Status tracking
- Email notifications

---

### 7. Wallet Address System

**Generation**:
- 16-digit unique addresses
- Generated using `GeneratesWalletAddress` trait
- Ensures uniqueness across all users
- Auto-generated on registration/reseller approval

**Management**:
- Users can update wallet address
- Admin can assign/generate addresses
- Wallet lookup API (admin/reseller only)

---

### 8. Referral System

**Reseller Referral Codes**:
- Format: `RSL{user_id}`
- Auto-generated on reseller approval
- Users can link to reseller during registration
- Resellers can see referred users
- Commission tracking on purchases

---

### 9. Price Management System

**Price Sources**:
- **RWAMP Price (PKR)**: Admin-set, stored in config/system_settings
- **USD/PKR Rate**: Fetched from exchangerate-api (cached 1 hour)
- **USDT/USD**: Fetched from API (cached)
- **BTC/USD**: Fetched from Binance API (cached 30 seconds)

**Price Calculation**:
- RWAMP USD = RWAMP PKR / USD_PKR
- USDT PKR = USDT USD × USD_PKR
- BTC PKR = BTC USD × USD_PKR

**Auto-Updates**:
- Scheduled command: `UpdateExchangeRate`
- Updates USD/PKR rate hourly
- Updates BTC/USD rate (via GamePriceEngine)

---

### 10. Transaction Ledger System

**Purpose**: Complete audit trail of all balance changes.

**Transaction Types**:
- `credit` - Token credit
- `debit` - Token debit
- `crypto_purchase` - Crypto payment purchase
- `commission` - Reseller commission
- `admin_transfer_credit` - Admin transfer
- `admin_transfer_debit` - Admin deduction

**Balance Reconciliation**:
- `calculateBalanceFromTransactions()` - Sum all transactions
- `reconcileBalance()` - Compare stored vs calculated
- `fixBalanceFromTransactions()` - Auto-fix discrepancies
- Scheduled command: `ReconcileUserBalances`

---

## Security Features

### 1. Authentication & Authorization

- **Laravel Fortify**: Authentication framework
- **Role-Based Access Control**: Middleware-based
- **Two-Factor Authentication**: Required for admins
- **Email Verification**: OTP-based
- **Password Reset**: Secure token-based

### 2. Session Management

- **Multi-Tab Detection**: Forces re-auth in new tabs
- **Tab Session Auth**: Prevents session hijacking
- **Session Invalidation**: On logout

### 3. Input Validation

- **Form Request Validation**: Laravel validation
- **CSRF Protection**: Token-based
- **XSS Protection**: Input sanitization
- **SQL Injection Prevention**: Eloquent ORM

### 4. Rate Limiting

- **Contact Form**: 3 requests per 60 seconds
- **Reseller Application**: 3 requests per 60 seconds
- **Newsletter**: 6 requests per 60 seconds
- **Login**: 5 requests per 1 minute
- **OTP Verification**: Custom throttle

### 5. Security Headers

- **X-Content-Type-Options**: nosniff
- **X-Frame-Options**: DENY
- **X-XSS-Protection**: 1; mode=block
- **Referrer-Policy**: strict-origin-when-cross-origin
- **Content-Security-Policy**: Custom policy

### 6. Game Security

- **PIN Protection**: 4-digit PIN with lockout
- **Idempotency Keys**: Prevents duplicate trades
- **Balance Validation**: Checks before trades
- **Session Validation**: Ensures active session

### 7. Payment Security

- **OTP Verification**: Required for sensitive operations
- **Transaction Hash Validation**: Prevents duplicates
- **Manual Approval**: All payments require admin approval
- **Payment Proof**: Screenshot upload required

---

## Database Schema

### Core Tables

**users**
- Primary user table
- Roles: admin, reseller, investor
- Wallet addresses, token balances
- KYC information
- Game PIN and session flags

**crypto_payments**
- Payment submissions
- Transaction hashes
- Approval status
- Commission tracking

**transactions**
- Complete transaction ledger
- All balance changes
- Reference tracking

**reseller_applications**
- Application submissions
- Status tracking
- IP/User agent logging

**game_sessions**
- Active game sessions
- Balance tracking
- Anchor prices

**game_trades**
- Individual trades
- Fee calculation
- Idempotency keys

**game_price_history**
- Price snapshots (every 5 seconds)
- Historical data

**buy_from_reseller_requests**
- Buy requests from investors
- Approval workflow

**withdraw_requests**
- Withdrawal requests
- Admin processing

**system_settings**
- Game parameters
- Price settings
- Configuration values

---

## Console Commands

### 1. MonitorCryptoPayments
- Monitors blockchain for payments
- Auto-detects transactions
- Credits tokens automatically

### 2. UpdateExchangeRate
- Updates USD/PKR exchange rate
- Scheduled hourly

### 3. ReconcileUserBalances
- Reconciles user balances
- Fixes discrepancies
- Logs corrections

### 4. GenerateMissingWallets
- Generates wallet addresses for users without one

### 5. BackfillUlids
- Adds ULID to existing records
- Migration support

### 6. ResetStuckGameStates
- Resets stuck game sessions
- Clears `is_in_game` flags

### 7. PruneGamePriceHistory
- Removes old price history
- Database cleanup

### 8. SyncMissingUsers
- Syncs missing user data

---

## Helper Classes

### PriceHelper (`app/Helpers/PriceHelper.php`)

**Purpose**: Centralized price calculation utilities.

**Key Methods**:
- `getRwampPkrPrice()` - RWAMP price in PKR
- `getRwampUsdPrice()` - RWAMP price in USD
- `getUsdToPkrRate()` - USD/PKR rate
- `getUsdtUsdPrice()` - USDT/USD price
- `getUsdtPkrPrice()` - USDT/PKR price
- `getBtcUsdPrice()` - BTC/USD price
- `getBtcPkrPrice()` - BTC/PKR price
- `getResellerMarkupRate()` - Reseller markup percentage

---

## Traits

### 1. HasUlid (`app/Concerns/HasUlid.php`)

**Purpose**: Adds ULID support to models.

**Features**:
- Auto-generates ULID on creation
- URL-friendly identifiers
- Used for: User, CryptoPayment, ResellerApplication, WithdrawRequest

### 2. GeneratesWalletAddress (`app/Traits/GeneratesWalletAddress.php`)

**Purpose**: Generates unique 16-digit wallet addresses.

**Method**:
- `generateUniqueWalletAddress()` - Generates and validates uniqueness

---

## Events

### 1. ChatMessageSent
- Fired when chat message is sent
- Used for real-time notifications

### 2. GameSettingsUpdated
- Fired when game settings change
- Used for cache invalidation

---

## Mail Classes

### VerifyOtpMail (`app/Mail/VerifyOtpMail.php`)
- OTP email template
- 6-digit code display

---

## Custom Rules

### Recaptcha (`app/Rules/Recaptcha.php`)
- Google reCAPTCHA validation
- Used in forms (if enabled)

---

## Configuration Files

### crypto.php (`config/crypto.php`)
- Wallet addresses (TRC20, ERC20, BEP20, BTC)
- API keys (Alchemy, TronGrid, Blockstream)
- Contract addresses (USDT)
- Exchange rates
- Feature flags

---

## Summary

This Laravel application is a comprehensive cryptocurrency token platform with:

1. **Multi-role User System**: Admin, Reseller, Investor
2. **Crypto Payment Processing**: USDT (multiple networks) and BTC
3. **Reseller Network**: Application and commission system
4. **Trading Game**: Simulated trading with real-time pricing
5. **KYC System**: Document verification workflow
6. **Wallet Management**: Unique 16-digit addresses
7. **Transaction Ledger**: Complete audit trail
8. **Security**: 2FA, OTP, PIN protection, rate limiting
9. **Price Management**: Dynamic pricing with API integration
10. **Withdrawal System**: Request and approval workflow

The codebase follows Laravel best practices with:
- MVC architecture
- Service layer pattern
- Middleware for security
- Eloquent ORM for database
- Event-driven architecture
- Queue support for async tasks
- Caching for performance
- Comprehensive logging

---

**End of Analysis**

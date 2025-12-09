# Reseller (Mini-Admin) System Implementation

## ✅ Completed Components

### 1. Database Migrations
- ✅ `2025_11_14_000001_add_referral_to_users.php` - Adds `referral_code` and `reseller_id` to users
- ✅ `2025_11_14_000002_add_commission_to_crypto_payments.php` - Adds `reseller_commission_awarded` flag
- ✅ `2025_11_14_000003_create_withdraw_requests_table.php` - Creates withdrawal requests table

### 2. Models
- ✅ `User` - Added `referral_code`, `reseller_id` to fillable, added relationships
- ✅ `CryptoPayment` - Added `reseller_commission_awarded` to fillable
- ✅ `WithdrawRequest` - New model created

### 3. Controllers
- ✅ `AuthController` - Handles referral code linking on registration (`?ref=RSL1001`)
- ✅ `AdminController` - Awards 10% commission on payment approval, generates referral codes for resellers, withdrawal management
- ✅ `ResellerController` - Dashboard, sell coins (OTP), approve payments, send OTP
- ✅ `WithdrawController` - Create withdrawal requests (KYC gated)
- ✅ `CryptoPaymentController` - Buy from reseller functionality (OTP protected)

### 4. Routes
- ✅ `/dashboard/reseller` - Reseller dashboard
- ✅ `/api/reseller/sell` - Sell coins to user
- ✅ `/api/reseller/send-otp` - Send OTP for sell operation
- ✅ `/api/reseller/crypto-payments/{id}/approve` - Approve payment for own user
- ✅ `/api/user/buy-from-reseller` - Buy tokens from reseller
- ✅ `/api/user/withdraw` - Create withdrawal request
- ✅ `/api/verify-otp` - OTP verification endpoint
- ✅ `/dashboard/admin/withdrawals` - Admin withdrawal management
- ✅ `/dashboard/admin/withdrawals/{id}/approve` - Approve withdrawal

### 5. Configuration
- ✅ `config/crypto.php` - Added `reseller_commission_rate` (0.10) and `reseller_markup_rate` (0.05)

## 📋 Remaining Tasks

### 1. Reseller Dashboard View (`resources/views/dashboard/reseller.blade.php`)
Needs to be created with tabs:
- **My Users Tab**: List users where `reseller_id = current_user.id`
- **Sell Coins Tab**: Search user → enter amount → send OTP → transfer
- **My Approvals Tab**: Approve/reject crypto payments for own users
- **Contact Admin Tab**: Simple form to contact super-admin

### 2. User Dashboard Enhancements (`resources/views/dashboard/investor.blade.php`)
Add:
- **Buy from Reseller Button**: Modal with reseller list, amount input, OTP
- **Withdraw Button**: Form with wallet address and amount (KYC gated)

### 3. Admin Withdrawals View (`resources/views/dashboard/admin-withdrawals.blade.php`)
Create view for managing withdrawal requests with approve/reject functionality

## 🔧 Key Features Implemented

### Referral System
- Users can register with `?ref=RSL1001` to link to reseller
- Resellers get referral code `RSL{user_id}` on approval
- Users automatically linked to reseller on registration

### Commission System
- 10% commission awarded to reseller when super-admin approves payment
- Commission tracked via `reseller_commission_awarded` flag
- Commission logged as transaction

### Reseller Functions
- **Sell Coins**: OTP-protected transfer to own users
- **Approve Payments**: Can approve crypto payments for own users only
- **View Users**: See all users linked to them

### User Functions
- **Buy from Reseller**: Purchase tokens from reseller with 5% markup
- **Withdraw**: Request withdrawal (requires KYC approval, max 90% of balance)

### Security
- All transfers use `DB::transaction()`
- OTP verification for all token transfers
- KYC gate for withdrawals
- Reseller can only manage own users
- Self-transfer prevention
- Rate limiting on sensitive endpoints

## 🚀 Next Steps

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Create the dashboard views (see remaining tasks above)

3. Test the system:
   - Register user with referral code
   - Approve reseller application
   - Test commission awarding
   - Test sell coins flow
   - Test buy from reseller
   - Test withdrawal request

## 📝 Environment Variables

Add to `.env`:
```
RESELLER_COMMISSION_RATE=0.10
RESELLER_MARKUP_RATE=0.05
```

## 🔒 Security Notes

- All token transfers require OTP or approval
- No auto-credit - all movements require action
- Withdrawals require KYC + super-admin approval
- Resellers cannot approve KYC
- Resellers can only manage their own users
- All operations use database transactions


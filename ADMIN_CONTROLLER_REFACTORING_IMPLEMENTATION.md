# AdminController Refactoring - Implementation Guide

## Overview
This document provides the complete structure for refactoring the 2,380-line `AdminController` into smaller, focused controllers.

## Completed Controllers ✅

### 1. AdminDashboardController
**File:** `app/Http/Controllers/Admin/AdminDashboardController.php`  
**Status:** ✅ Created  
**Methods:**
- `index()` - Dashboard with metrics

### 2. AdminUserController
**File:** `app/Http/Controllers/Admin/AdminUserController.php`  
**Status:** ✅ Created  
**Methods:**
- `index()` - List users
- `store()` - Create user
- `show()` - User details
- `update()` - Update user
- `destroy()` - Delete user
- `resetPassword()` - Reset password
- `assignWalletAddress()` - Assign wallet

## Remaining Controllers to Create

### 3. AdminCryptoPaymentController
**File:** `app/Http/Controllers/Admin/AdminCryptoPaymentController.php`

**Methods to Extract from AdminController:**
- `cryptoPayments()` → `index()`
- `approveCryptoPayment()` → `approve()`
- `rejectCryptoPayment()` → `reject()`
- `updateCryptoPayment()` → `update()`
- `deleteCryptoPayment()` → `destroy()`
- `cryptoPaymentDetails()` → `show()`
- `downloadCryptoPaymentScreenshot()` → `downloadScreenshot()`
- `history()` → `history()`

**Dependencies:**
- `App\Models\CryptoPayment`
- `App\Models\Transaction`
- `App\Models\User`
- `Illuminate\Support\Facades\Storage`

### 4. AdminKycController
**File:** `app/Http/Controllers/Admin/AdminKycController.php`

**Methods to Extract:**
- `kycList()` → `index()`
- `approveKyc()` → `approve()`
- `rejectKyc()` → `reject()`
- `updateKyc()` → `update()`
- `deleteKyc()` → `destroy()`
- `downloadKycFile()` → `downloadFile()`

**Dependencies:**
- `App\Models\User`
- `Illuminate\Support\Facades\Storage`

### 5. AdminWithdrawalController
**File:** `app/Http/Controllers/Admin/AdminWithdrawalController.php`

**Methods to Extract:**
- `withdrawals()` → `index()`
- `showWithdrawal()` → `show()`
- `approveWithdrawal()` → `approve()`
- `rejectWithdrawal()` → `reject()`
- `updateWithdrawal()` → `update()`
- `deleteWithdrawal()` → `destroy()`
- `submitReceipt()` → `submitReceipt()`

**Dependencies:**
- `App\Models\WithdrawRequest`
- `App\Models\Transaction`
- `App\Models\User`
- `Illuminate\Support\Facades\Mail`
- `Illuminate\Support\Facades\Storage`

### 6. AdminResellerApplicationController
**File:** `app/Http/Controllers/Admin/AdminResellerApplicationController.php`

**Methods to Extract:**
- `applicationsIndex()` → `index()`
- `approve()` → `approve()`
- `reject()` → `reject()`
- `applicationDetails()` → `show()`
- `applicationUpdate()` → `update()`
- `applicationDelete()` → `destroy()`

**Dependencies:**
- `App\Models\ResellerApplication`
- `App\Models\User`
- `Illuminate\Support\Facades\Mail`
- `Illuminate\Support\Facades\Hash`
- `Illuminate\Support\Facades\Cache`

### 7. AdminPriceController
**File:** `app/Http/Controllers/Admin/AdminPriceController.php`

**Methods to Extract:**
- `priceManagement()` → `index()`
- `updatePrices()` → `update()`
- `fetchUsdtPrice()` → `fetchUsdtPrice()` (private)
- `fetchBtcPrice()` → `fetchBtcPrice()` (private)

**Dependencies:**
- `App\Helpers\PriceHelper`
- `Illuminate\Support\Facades\Cache`
- `GuzzleHttp\Client`

### 8. AdminSellController
**File:** `app/Http/Controllers/Admin/AdminSellController.php`

**Methods to Extract:**
- `sellPage()` → `index()`
- `fetchUserPaymentProof()` → `fetchPaymentProof()`
- `searchUsersForSell()` → `searchUsers()`
- `sendOtpForSell()` → `sendOtp()`
- `sellCoins()` → `store()`
- `generateUniqueWalletAddress()` → Move to trait or service

**Dependencies:**
- `App\Models\User`
- `App\Models\Transaction`
- `App\Models\CryptoPayment`
- `App\Helpers\PriceHelper`
- `App\Http\Controllers\Auth\EmailVerificationController`
- `Illuminate\Support\Facades\Auth`
- `Illuminate\Support\Facades\Cache`

### 9. Admin2FAController
**File:** `app/Http/Controllers/Admin/Admin2FAController.php`

**Methods to Extract:**
- `showTwoFactorSetup()` → `show()`
- `regenerateRecoveryCodes()` → `regenerateRecoveryCodes()`

**Dependencies:**
- `Laravel\Fortify\Actions\GenerateNewRecoveryCodes`
- `Illuminate\Support\Facades\Auth`

### 10. AdminChatController
**File:** `app/Http/Controllers/Admin/AdminChatController.php`

**Methods to Extract:**
- `chatsIndex()` → `index()`
- `viewChat()` → `show()`
- `auditTrail()` → `auditTrail()`

**Dependencies:**
- `App\Models\Chat`
- `App\Models\ChatMessage`
- `App\Models\ChatParticipant`

## Shared Traits/Services

### WalletAddressGenerator Trait
**File:** `app/Traits/GeneratesWalletAddress.php`

```php
<?php

namespace App\Traits;

use App\Models\User;

trait GeneratesWalletAddress
{
    protected function generateUniqueWalletAddress(): string
    {
        do {
            $wallet = str_pad(random_int(1000000000000000, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } while (User::where('wallet_address', $wallet)->exists());

        return $wallet;
    }
}
```

## Route Updates

Update `routes/web.php` to use new controllers:

```php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCryptoPaymentController;
use App\Http\Controllers\Admin\AdminKycController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminResellerApplicationController;
use App\Http\Controllers\Admin\AdminPriceController;
use App\Http\Controllers\Admin\AdminSellController;
use App\Http\Controllers\Admin\Admin2FAController;
use App\Http\Controllers\Admin\AdminChatController;

// Dashboard
Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
    ->middleware(['role:admin','admin.2fa'])
    ->name('dashboard.admin');

// Users
Route::prefix('dashboard/admin/users')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/{user}/details', [AdminUserController::class, 'show'])->name('admin.users.details');
    Route::put('/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.delete');
    Route::post('/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset');
    Route::post('/{user}/assign-wallet', [AdminUserController::class, 'assignWalletAddress'])->name('admin.users.assign-wallet');
});

// Crypto Payments
Route::prefix('dashboard/admin/crypto-payments')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/', [AdminCryptoPaymentController::class, 'index'])->name('admin.crypto.payments');
    Route::get('/{payment}/details', [AdminCryptoPaymentController::class, 'show'])->name('admin.crypto.payments.details');
    Route::get('/{payment}/screenshot', [AdminCryptoPaymentController::class, 'downloadScreenshot'])->name('admin.crypto.payments.screenshot');
    Route::put('/{payment}', [AdminCryptoPaymentController::class, 'update'])->name('admin.crypto.payments.update');
    Route::delete('/{payment}', [AdminCryptoPaymentController::class, 'destroy'])->name('admin.crypto.payments.delete');
    Route::post('/{payment}/approve', [AdminCryptoPaymentController::class, 'approve'])->name('admin.crypto.approve');
    Route::post('/{payment}/reject', [AdminCryptoPaymentController::class, 'reject'])->name('admin.crypto.reject');
});

// KYC
Route::prefix('dashboard/admin/kyc')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/', [AdminKycController::class, 'index'])->name('admin.kyc.list');
    Route::post('/{user}/approve', [AdminKycController::class, 'approve'])->name('admin.kyc.approve');
    Route::post('/{user}/reject', [AdminKycController::class, 'reject'])->name('admin.kyc.reject');
    Route::put('/{user}/update', [AdminKycController::class, 'update'])->name('admin.kyc.update');
    Route::delete('/{user}/delete', [AdminKycController::class, 'destroy'])->name('admin.kyc.delete');
    Route::get('/{user}/download/{type}', [AdminKycController::class, 'downloadFile'])->name('admin.kyc.download');
});

// Withdrawals
Route::prefix('dashboard/admin/withdrawals')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/', [AdminWithdrawalController::class, 'index'])->name('admin.withdrawals');
    Route::get('/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('admin.withdrawals.show');
    Route::post('/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('admin.withdrawals.approve');
    Route::post('/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('admin.withdrawals.reject');
    Route::post('/{withdrawal}/submit-receipt', [AdminWithdrawalController::class, 'submitReceipt'])->name('admin.withdrawals.submit-receipt');
    Route::put('/{withdrawal}', [AdminWithdrawalController::class, 'update'])->name('admin.withdrawals.update');
    Route::delete('/{withdrawal}', [AdminWithdrawalController::class, 'destroy'])->name('admin.withdrawals.delete');
});

// Reseller Applications
Route::prefix('dashboard/admin/applications')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/', [AdminResellerApplicationController::class, 'index'])->name('admin.applications');
    Route::get('/{application}/details', [AdminResellerApplicationController::class, 'show'])->name('admin.applications.details');
    Route::put('/{application}/approve', [AdminResellerApplicationController::class, 'approve'])->name('admin.applications.approve');
    Route::put('/{application}/reject', [AdminResellerApplicationController::class, 'reject'])->name('admin.applications.reject');
    Route::put('/{application}', [AdminResellerApplicationController::class, 'update'])->name('admin.applications.update');
    Route::delete('/{application}', [AdminResellerApplicationController::class, 'destroy'])->name('admin.applications.delete');
});

// Prices
Route::prefix('dashboard/admin/prices')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/', [AdminPriceController::class, 'index'])->name('admin.prices');
    Route::post('/update', [AdminPriceController::class, 'update'])->name('admin.prices.update');
});

// Admin Sell
Route::prefix('dashboard/admin/sell')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/', [AdminSellController::class, 'index'])->name('admin.sell');
    Route::post('/', [AdminSellController::class, 'store'])->name('admin.sell-coins');
});

Route::prefix('api/admin')->middleware(['role:admin','admin.2fa'])->group(function () {
    Route::get('/search-users', [AdminSellController::class, 'searchUsers'])->name('admin.search-users');
    Route::post('/send-otp', [AdminSellController::class, 'sendOtp'])->name('admin.send-otp');
    Route::post('/fetch-payment-proof', [AdminSellController::class, 'fetchPaymentProof']);
});

// 2FA
Route::prefix('admin/2fa')->middleware('role:admin')->group(function () {
    Route::get('/setup', [Admin2FAController::class, 'show'])->name('admin.2fa.setup');
    Route::post('/regenerate-recovery-codes', [Admin2FAController::class, 'regenerateRecoveryCodes'])->name('admin.2fa.regenerate-recovery-codes');
});

// History
Route::get('/dashboard/admin/history', [AdminCryptoPaymentController::class, 'history'])
    ->middleware(['role:admin','admin.2fa'])
    ->name('admin.history');
```

## Migration Checklist

- [ ] Create all controller files
- [ ] Extract methods from AdminController
- [ ] Create GeneratesWalletAddress trait
- [ ] Update routes in web.php
- [ ] Update view references if needed
- [ ] Test all functionality
- [ ] Update documentation
- [ ] Remove or archive old AdminController
- [ ] Update tests if any exist

## Testing Strategy

1. **Unit Tests:** Test each controller method independently
2. **Integration Tests:** Test complete workflows
3. **Manual Testing:** Test in browser for UI/UX
4. **Regression Testing:** Ensure no broken functionality

## Benefits

- ✅ Better code organization
- ✅ Easier to maintain
- ✅ Improved testability
- ✅ Better readability
- ✅ Follows SOLID principles
- ✅ Easier to add new features

## Notes

- Keep old AdminController until all routes are migrated and tested
- Use Git branches for refactoring work
- Test thoroughly before merging
- Update API documentation if routes change
- Consider creating a base AdminController with shared methods if needed


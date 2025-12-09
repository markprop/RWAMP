# AdminController Refactoring Guide

## Overview
The `AdminController` (2,380 lines) has been refactored into smaller, focused controllers following the Single Responsibility Principle.

## New Controller Structure

### 1. AdminDashboardController
**Location:** `app/Http/Controllers/Admin/AdminDashboardController.php`
**Methods:**
- `index()` - Display dashboard with metrics

### 2. AdminUserController
**Location:** `app/Http/Controllers/Admin/AdminUserController.php`
**Methods:**
- `index()` - List users with search/filters
- `store()` - Create new user
- `show()` - Get user details
- `update()` - Update user information
- `destroy()` - Delete user
- `resetPassword()` - Reset user password
- `assignWalletAddress()` - Assign wallet address

### 3. AdminCryptoPaymentController
**Location:** `app/Http/Controllers/Admin/AdminCryptoPaymentController.php`
**Methods:**
- `index()` - List crypto payments
- `show()` - Payment details
- `approve()` - Approve payment
- `reject()` - Reject payment
- `update()` - Update payment
- `destroy()` - Delete payment
- `downloadScreenshot()` - Download payment screenshot
- `history()` - Payment and transaction history

### 4. AdminKycController
**Location:** `app/Http/Controllers/Admin/AdminKycController.php`
**Methods:**
- `index()` - List KYC submissions
- `approve()` - Approve KYC
- `reject()` - Reject KYC
- `update()` - Update KYC information
- `destroy()` - Delete KYC submission
- `downloadFile()` - Download KYC document

### 5. AdminWithdrawalController
**Location:** `app/Http/Controllers/Admin/AdminWithdrawalController.php`
**Methods:**
- `index()` - List withdrawal requests
- `show()` - Withdrawal details
- `approve()` - Approve withdrawal
- `reject()` - Reject withdrawal
- `update()` - Update withdrawal
- `destroy()` - Delete withdrawal
- `submitReceipt()` - Submit transfer receipt

### 6. AdminResellerApplicationController
**Location:** `app/Http/Controllers/Admin/AdminResellerApplicationController.php`
**Methods:**
- `index()` - List applications
- `show()` - Application details
- `approve()` - Approve application
- `reject()` - Reject application
- `update()` - Update application
- `destroy()` - Delete application

### 7. AdminPriceController
**Location:** `app/Http/Controllers/Admin/AdminPriceController.php`
**Methods:**
- `index()` - Price management page
- `update()` - Update prices

### 8. AdminSellController
**Location:** `app/Http/Controllers/Admin/AdminSellController.php`
**Methods:**
- `index()` - Sell coins page
- `searchUsers()` - Search users for selling
- `fetchPaymentProof()` - Fetch user payment proof
- `sendOtp()` - Send OTP for sell transaction
- `store()` - Process sell coins transaction

### 9. Admin2FAController
**Location:** `app/Http/Controllers/Admin/Admin2FAController.php`
**Methods:**
- `show()` - Show 2FA setup page
- `regenerateRecoveryCodes()` - Regenerate recovery codes

### 10. AdminChatController
**Location:** `app/Http/Controllers/Admin/AdminChatController.php`
**Methods:**
- `index()` - List chats
- `show()` - View chat
- `auditTrail()` - Get chat audit trail

## Migration Steps

### Step 1: Update Routes
Update `routes/web.php` to use new controllers:

```php
// Old
Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])

// New
Route::get('/dashboard/admin', [Admin\AdminDashboardController::class, 'index'])
```

### Step 2: Update Route Names
Update route names to match new structure:

```php
// Users
Route::get('/dashboard/admin/users', [Admin\AdminUserController::class, 'index'])->name('admin.users');
Route::post('/dashboard/admin/users', [Admin\AdminUserController::class, 'store'])->name('admin.users.store');
Route::get('/dashboard/admin/users/{user}', [Admin\AdminUserController::class, 'show'])->name('admin.users.show');
Route::put('/dashboard/admin/users/{user}', [Admin\AdminUserController::class, 'update'])->name('admin.users.update');
Route::delete('/dashboard/admin/users/{user}', [Admin\AdminUserController::class, 'destroy'])->name('admin.users.destroy');
```

### Step 3: Update Views
Update view references if needed (most should work as-is).

### Step 4: Test All Functionality
Test each refactored controller to ensure all functionality works.

### Step 5: Remove Old AdminController
Once all routes are updated and tested, the old `AdminController` can be removed or kept as a legacy reference.

## Benefits

1. **Better Organization** - Each controller has a single, clear responsibility
2. **Easier Maintenance** - Smaller files are easier to understand and modify
3. **Better Testability** - Focused controllers are easier to test
4. **Improved Readability** - Developers can quickly find relevant code
5. **Scalability** - Easy to add new admin features without bloating existing controllers

## Notes

- All controllers extend `App\Http\Controllers\Controller`
- All controllers are in `App\Http\Controllers\Admin` namespace
- Middleware (`role:admin`, `admin.2fa`) should be applied to route groups
- Shared helper methods (like `generateUniqueWalletAddress()`) should be moved to traits or services


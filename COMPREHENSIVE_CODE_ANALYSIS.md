# Comprehensive Code Analysis - RWAMP Laravel Application

## Table of Contents
1. [Project Overview](#project-overview)
2. [Configuration Files](#configuration-files)
3. [Routes Analysis](#routes-analysis)
4. [Controllers Analysis](#controllers-analysis)
5. [Models Analysis](#models-analysis)
6. [Helpers Analysis](#helpers-analysis)
7. [Frontend Assets](#frontend-assets)
8. [Views Analysis](#views-analysis)
9. [Middleware & Services](#middleware--services)

---

## Project Overview

**RWAMP (Real Estate Wealth Asset Management Platform)** is a Laravel 10 application for managing real estate token investments across Dubai, Pakistan, and Saudi Arabia.

**Key Technologies:**
- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Alpine.js, Tailwind CSS, Vite
- **Authentication**: Laravel Fortify with 2FA
- **Real-time**: Pusher (currently disabled)
- **Database**: MySQL/MariaDB

---

## Configuration Files

### 1. `composer.json` - PHP Dependencies

```json
{
    "name": "rwamp/laravel-app",           // Package name
    "type": "project",                     // Project type (not a library)
    "description": "RWAMP - The Currency...", // Project description
    "keywords": ["laravel", "framework", "rwamp", "real-estate"],
    "license": "proprietary",              // Proprietary license
```

**Key Dependencies:**
- `php: ^8.1` - Minimum PHP version
- `laravel/framework: ^10.0` - Laravel core
- `laravel/fortify: ^1.31` - Authentication scaffolding
- `pusher/pusher-php-server: ^7.2` - Real-time messaging (disabled)
- `endroid/qr-code: ^6.0` - QR code generation
- `guzzlehttp/guzzle: ^7.10` - HTTP client for API calls

**Autoload Configuration:**
- `App\\` → `app/` directory
- `Database\\Factories\\` → `database/factories/`
- `Database\\Seeders\\` → `database/seeders/`

**Scripts:**
- `post-autoload-dump`: Discovers Laravel packages
- `post-update-cmd`: Publishes Laravel assets
- `post-root-package-install`: Creates `.env` if missing
- `post-create-project-cmd`: Generates app key

---

### 2. `package.json` - JavaScript Dependencies

```json
{
    "private": true,                       // Not publishable to npm
    "type": "module",                     // ES6 modules
```

**Scripts:**
- `build`: Production build with Vite
- `dev`: Development server with hot reload

**Dev Dependencies:**
- `vite: ^4.0.0` - Build tool
- `tailwindcss: ^3.3.2` - CSS framework
- `autoprefixer: ^10.4.14` - CSS vendor prefixes
- `postcss: ^8.4.24` - CSS processor
- `laravel-vite-plugin: ^0.7.2` - Laravel-Vite integration

**Dependencies:**
- `alpinejs: ^3.13.3` - Reactive JavaScript framework
- `@alpinejs/csp: ^3.14.1` - CSP-compliant Alpine.js
- `laravel-echo: ^2.2.6` - Laravel Echo client
- `pusher-js: ^8.4.0` - Pusher JavaScript SDK

---

### 3. `config/app.php` - Application Configuration

**Lines 1-13: Basic Configuration**
```php
'name' => env('APP_NAME', 'RWAMP'),        // App name from .env
'env' => env('APP_ENV', 'production'),     // Environment (local/production)
'debug' => (bool) env('APP_DEBUG', false), // Debug mode
'url' => env('APP_URL', 'http://localhost'), // Base URL
'timezone' => 'UTC',                       // Default timezone
'locale' => 'en',                          // Default language
'fallback_locale' => 'en',                 // Fallback language
'faker_locale' => 'en_US',                 // Faker locale for factories
'key' => env('APP_KEY'),                   // Encryption key
'cipher' => 'AES-256-CBC',                 // Encryption cipher
```

**Lines 15-44: Service Providers**
- Core Laravel providers (Auth, Cache, Database, etc.)
- Custom providers: `AppServiceProvider`, `AuthServiceProvider`, `EventServiceProvider`, `RouteServiceProvider`, `FortifyServiceProvider`

**Lines 46-87: Facade Aliases**
- Provides shortcuts like `Auth::`, `Cache::`, `DB::`, etc.

**Lines 89-92: Custom Config Values**
```php
'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'), // GA tracking ID
'meta_pixel_id' => env('META_PIXEL_ID'),             // Facebook Pixel ID
'admin_email' => env('ADMIN_EMAIL', 'admin@rwamp.com'), // Admin email
```

---

### 4. `tailwind.config.js` - Tailwind CSS Configuration

**Content Paths:**
- Scans `resources/**/*.blade.php`, `resources/**/*.js`, `resources/**/*.vue` for class usage

**Theme Extensions:**
```javascript
colors: {
    primary: '#E30613',    // Red (RWAMP brand color)
    secondary: '#000000',   // Black
    accent: '#FFD700',      // Gold
    success: '#28A745',     // Green
}
```

**Font Families:**
- `montserrat`: Montserrat (headings)
- `roboto`: Roboto (body text)
- `mono`: JetBrains Mono (code/countdown)

**Background Images:**
- `dubai-skyline`: Unsplash image URL for hero section

---

## Routes Analysis (`routes/web.php`)

### Public Routes (Lines 49-97)

**Main Pages:**
```php
Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/become-partner', [PageController::class, 'becomePartner'])->name('become.partner');
Route::get('/whitepaper', [PageController::class, 'whitepaper'])->name('whitepaper');
```

**Purchase Flow:**
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/purchase', [CryptoPaymentController::class, 'create'])->name('purchase.create');
});
Route::get('/qr-code/{network}', [CryptoPaymentController::class, 'generateQrCode'])->name('qr-code.generate');
```

**Legal Pages:**
- Privacy Policy, Terms of Service, Disclaimer - All return views with SEO metadata

**SEO Routes:**
```php
Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml'))
        ->header('Content-Type', 'text/plain');
});
```

**Sitemap (Lines 106-175):**
- Generates dynamic XML sitemap
- Includes static pages (home, about, contact, etc.)
- Optionally includes dynamic content (Posts, News, Projects, Docs) if models exist
- Each URL has `changefreq`, `priority`, and `lastmod` attributes

### Authenticated Routes (Lines 178-419)

**KYC Routes:**
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/kyc', [KycController::class, 'show'])->name('kyc.show');
    Route::post('/kyc/submit', [KycController::class, 'submit'])->name('kyc.submit');
});
```

**Role-Based Dashboards:**
```php
// Investor Dashboard
Route::get('/dashboard/investor', [InvestorDashboardController::class, 'index'])
    ->middleware(['role:investor', 'kyc.approved'])
    ->name('dashboard.investor');

// Reseller Dashboard (legacy route before route group)
Route::get('/dashboard/reseller', [ResellerDashboardController::class, 'index'])
    ->middleware('role:reseller')
    ->name('dashboard.reseller');

// Admin Dashboard
Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])
    ->middleware(['role:admin','admin.2fa'])
    ->name('dashboard.admin');
```

**Reseller Routes (Lines 196-239):**
- Organized in route group: `/dashboard/reseller/*`
- Routes: users, payments, transactions, sell, buy-requests
- Legacy routes maintained for backward compatibility

**Admin Routes (Lines 242-380):**
- **Legacy Route Aliases (Lines 245-254):** Defined BEFORE route groups to avoid conflicts
  - `admin.users`, `admin.sell`, `admin.applications`, etc.
- **Route Groups:**
  - `admin.crypto.payments.*` - Payment management
  - `admin.applications.*` - Reseller application management
  - `admin.users.*` - User management
  - `admin.kyc.*` - KYC management
  - `admin.prices.*` - Price management
  - `admin.withdrawals.*` - Withdrawal management
  - `admin.sell.*` - Admin sell functionality

**Chat System (Lines 382-407):**
- Currently DISABLED (all routes commented out)
- See `CHAT_REENABLE_GUIDE.md` to re-enable

### API Routes (Lines 427-475)

**Public API:**
```php
Route::prefix('api')->group(function () {
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,60');
    Route::post('/reseller', [ResellerController::class, 'store'])->middleware('throttle:3,60');
    Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:6,60');
    Route::get('/check-referral-code', [AuthController::class, 'checkReferralCode'])->name('api.check.referral.code');
    Route::get('/check-email', [RegisterController::class, 'checkEmail'])->name('api.check.email');
    Route::get('/check-phone', [RegisterController::class, 'checkPhone'])->name('api.check.phone');
});
```

**Authenticated API:**
- Crypto payment APIs (save wallet, check status, submit tx hash)
- Wallet lookup (admin/reseller only)
- Reseller APIs (sell, OTP, payment proof)
- User APIs (buy from reseller, withdrawals)
- OTP verification

### Authentication Routes (Lines 477-525)

**Guest Routes:**
```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.post');
    
    // Email Verification (OTP-based)
    Route::get('/verify-email', [EmailVerificationController::class, 'show'])->name('verify-email');
    Route::post('/verify-email', [EmailVerificationController::class, 'verify'])->middleware('throttle:otp-verification')->name('verify-email.post');
    Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:otp-resend')->name('verify-email.resend');
    
    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
        // Uses Laravel's Password facade
    })->name('password.email');
    
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.passwords.reset', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');
    
    Route::post('/reset-password', function (\Illuminate\Http\Request $request) {
        // Password reset logic using Password facade
    })->name('password.update');
});
```

**Logout Route (Lines 527-529):**
```php
// Custom logout route with tab session handling
// Note: Fortify also registers a logout route, but this one takes precedence
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
```
- Uses `match()` to accept both GET and POST
- Handles tab-specific logout via `TabAuthService`

**Profile Routes (Lines 532-539):**
- Profile viewing/editing
- Account updates (name, email, phone)
- Password updates
- Wallet address management
- Email verification resend

---

## Controllers Analysis

### `AuthController.php` - Authentication Logic

**Class Structure:**
```php
class AuthController extends Controller
{
    // Uses TabAuthService for per-tab authentication
    // Handles login, logout, registration, referral code validation
}
```

#### `showLogin()` Method (Lines 18-25)
```php
public function showLogin(Request $request)
{
    // Store intended action in session if provided
    if ($request->has('intended') && $request->intended === 'purchase') {
        $request->session()->put('intended_action', 'purchase');
    }
    return view('auth.login');
}
```
- Stores intended action (e.g., "purchase") in session
- Returns login view

#### `login()` Method (Lines 32-135)

**Lines 34-39: reCAPTCHA Check**
```php
$requireRecaptcha = config('services.recaptcha.secret_key') && 
    !in_array($request->getHost(), ['localhost', '127.0.0.1']) &&
    !str_contains(config('app.url', ''), 'localhost') &&
    !str_contains(config('app.url', ''), '127.0.1') &&
    config('app.env') !== 'local';
```
- Only requires reCAPTCHA in production (not localhost)

**Lines 41-50: Validation**
```php
$credentials = $request->validate([
    'email' => ['required', 'email'],
    'password' => ['required'],
    'role' => ['nullable', 'in:investor,reseller,admin'],
    'remember' => ['nullable', 'boolean'],
    'g-recaptcha-response' => $requireRecaptcha ? ['required', 'recaptcha'] : ['nullable'],
]);
```
- Validates email, password, optional role, remember me, and reCAPTCHA

**Lines 52-69: Role Validation**
```php
$user = User::where('email', $credentials['email'])->first();

// Validate role selection for non-admin users
if ($user && $user->role !== 'admin') {
    if (empty($credentials['role']) || !in_array($credentials['role'], ['investor', 'reseller'])) {
        return back()->withErrors(['role' => 'Please select your role...']);
    }
    
    // Validate that selected role matches user's actual role
    if ($credentials['role'] !== $user->role) {
        return back()->withErrors(['role' => 'The selected role does not match...']);
    }
}
```
- Non-admin users must select role
- Selected role must match user's actual role
- Admin users can login without role selection

**Lines 71-74: Login Attempt**
```php
$remember = $request->has('remember') && $request->input('remember') == '1';
if (Auth::attempt($request->only('email','password'), $remember)) {
    $request->session()->regenerate();
    $user = Auth::user();
```
- Attempts authentication with "remember me" support
- Regenerates session for security

**Lines 77-81: Tab Session**
```php
$tabId = $request->cookie('tab_session_id');
if ($tabId) {
    TabAuthService::setTabUser($tabId, $user->id, 24);
}
```
- Stores tab-specific authentication (24-hour expiry)

**Lines 83-111: Email Verification Check**
```php
if (!$user->email_verified_at) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    // Generate and send OTP
    $emailVerificationController = new EmailVerificationController();
    try {
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($user->email));
        $emailVerificationController->generateAndSendOtp($normalizedEmail);
        $request->session()->put('verification_email', $normalizedEmail);
        Log::info("OTP sent to unverified user during login: {$normalizedEmail}");
    } catch (\Exception $e) {
        // Error handling
    }
    
    return redirect()->route('verify-email', ['email' => $normalizedEmail])
        ->with('error', 'Please verify your email address...');
}
```
- Logs out unverified users
- Sends OTP for verification
- Redirects to verification page

**Lines 113-123: Default Password Check (Resellers)**
```php
if ($user->role === 'reseller') {
    $defaultPassword = 'RWAMP@agent';
    if (Hash::check($defaultPassword, $user->password)) {
        Cache::put('password_reset_required_user_' . $user->id, true, now()->addDays(30));
        return redirect()->route('password.change.required')
            ->with('warning', 'Please set your own password...');
    }
}
```
- Forces password change for resellers using default password

**Lines 125-129: Password Reset Required Check**
```php
if (Cache::get('password_reset_required_user_'.$user->id)) {
    return redirect()->route('password.change.required');
}
return $this->redirectByRole($user, $credentials['role'] ?? null, $request);
```
- Redirects to password change if required
- Otherwise redirects by role

#### `checkReferralCode()` Method (Lines 145-173)

**Purpose:** Validates referral codes via API

**Process:**
1. Normalizes code (uppercase, trim)
2. Validates format: `RSL{number}` (e.g., `RSL1001`)
3. Extracts reseller ID from code
4. Queries database for reseller with matching ID and role
5. Returns JSON with validation result and reseller name

**Response Format:**
```json
{
    "valid": true,
    "reseller_name": "John Doe",
    "reseller_id": 1001,
    "reseller_email": "john@example.com"
}
```

#### `register()` Method (Lines 279-393)

**Lines 281-286: Role Handling**
```php
$role = $request->input('role', 'investor');

// Handle reseller applications differently
if ($role === 'reseller') {
    return $this->registerResellerApplication($request);
}
```
- Resellers go through application process
- Investors register directly

**Lines 288-307: Validation**
- Validates name, email, phone, password, referral code
- reCAPTCHA validation (production only)

**Lines 311-340: Referral Code Processing**
```php
$refCode = $validated['referral_code'] ?? $request->query('ref');

if ($refCode) {
    $refCode = strtoupper(trim($refCode));
    
    // Extract ID from referral code (e.g., RSL1001 -> 1001)
    if (preg_match('/^RSL(\d+)$/i', $refCode, $matches)) {
        $resellerUserId = (int) $matches[1];
        $reseller = User::where('id', $resellerUserId)
            ->where('role', 'reseller')
            ->whereNotNull('referral_code')
            ->first();
        if ($reseller) {
            $resellerId = $reseller->id;
        }
    }
}
```
- Processes referral code from form or URL parameter
- Links user to reseller if valid

**Lines 342-354: User Creation**
```php
$walletAddress = $this->generateUniqueWalletAddress();

$user = User::create([
    'name' => $validated['name'],
    'email' => $normalizedEmail,
    'phone' => $validated['phone'] ?? null,
    'role' => 'investor',
    'password' => Hash::make($validated['password']),
    'email_verified_at' => null, // Email verification required
    'reseller_id' => $resellerId, // Link to reseller if referral code provided
    'wallet_address' => $walletAddress, // Auto-generated wallet address
]);
```
- Generates unique 16-digit wallet address
- Creates user with investor role
- Links to reseller if referral code provided

**Lines 356-380: OTP Generation**
- Generates and sends OTP via `EmailVerificationController`
- Handles email sending failures
- In production, deletes user if OTP fails
- In debug mode, allows registration to continue

#### `logout()` Method (Lines 498-520)

**Tab-Specific Logout:**
```php
$tabId = $request->cookie('tab_session_id');

if ($tabId) {
    TabAuthService::clearTabUser($tabId);
    
    // If caller explicitly wants a global logout
    if ($request->boolean('clear_all')) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
} else {
    // Legacy behaviour: log out completely
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
}
```
- Supports per-tab logout (clears only that tab's session)
- Supports global logout (clears all sessions)
- Falls back to legacy behavior if no tab ID

#### `redirectByRole()` Method (Lines 619-662)

**Purpose:** Redirects users to appropriate dashboard after login

**Logic:**
1. Determines default dashboard by role (admin, reseller, investor)
2. Checks for intended action (e.g., "purchase")
3. Checks for Laravel's intended URL
4. Opens purchase modal automatically (except for admin)
5. Redirects to intended URL or default dashboard

---

## Helpers Analysis

### `PriceHelper.php` - Price Management

**Purpose:** Centralized helper for retrieving cryptocurrency prices

#### `getRwampPkrPrice()` Method (Lines 15-44)

**Priority Order:**
1. **Cache** - Checks `crypto_price_rwamp_pkr` in cache
2. **Database** - Checks `system_settings` table
3. **Config** - Falls back to `config('crypto.rates.rwamp_pkr', 3.0)`

**Implementation:**
```php
public static function getRwampPkrPrice(): float
{
    // Always check cache first - this is the official admin-set price
    $cachedPrice = Cache::get('crypto_price_rwamp_pkr');
    if ($cachedPrice !== null) {
        return (float) $cachedPrice;
    }
    
    // If cache is empty, check database (persistent storage)
    try {
        if (Schema::hasTable('system_settings')) {
            $dbPrice = DB::table('system_settings')
                ->where('key', 'crypto_price_rwamp_pkr')
                ->value('value');
            
            if ($dbPrice !== null) {
                $price = (float) $dbPrice;
                // Store in cache for faster access
                Cache::forever('crypto_price_rwamp_pkr', $price);
                return $price;
            }
        }
    } catch (\Exception $e) {
        // Table might not exist yet, fall through to config
    }
    
    // Fallback to config if cache and database are empty
    $defaultPkr = config('crypto.rates.rwamp_pkr', 3.0);
    return (float) $defaultPkr;
}
```

**Why This Design:**
- Cache is fastest (in-memory)
- Database is persistent (survives cache clears)
- Config is fallback (default value)

#### `getRwampUsdPrice()` Method (Lines 49-60)

**Calculation:**
```php
$rwampPkr = self::getRwampPkrPrice();
$usdPkr = self::getUsdToPkrRate();
return $rwampPkr / $usdPkr;
```
- Calculates USD price from PKR price and exchange rate

#### `getUsdToPkrRate()` Method (Lines 130-145)

**API Integration:**
1. Checks cache first (1-hour expiry)
2. Fetches from `exchangerate-api.com` (free tier)
3. Fallback to `currencyapi.net` (if API key configured)
4. Final fallback to config value (278 PKR per USD)

**Implementation:**
```php
public static function getUsdToPkrRate(): float
{
    // Check cache first (cache for 1 hour)
    $cached = Cache::get('exchange_rate_usd_pkr');
    if ($cached !== null) {
        return (float) $cached;
    }

    // Fetch from API if cache is expired
    $rate = self::fetchUsdToPkrRate();
    
    // Cache for 1 hour
    Cache::put('exchange_rate_usd_pkr', $rate, now()->addHour());
    
    return $rate;
}
```

#### `fetchUsdToPkrRate()` Method (Lines 151-196)

**API Calls:**
1. **Primary:** `https://api.exchangerate-api.com/v4/latest/USD`
   - Free tier, no API key needed
   - Returns JSON with `rates.PKR`

2. **Fallback:** `https://api.currencyapi.com/v3/latest`
   - Requires `CURRENCY_API_KEY` in `.env`
   - Returns JSON with `data.PKR.value`

3. **Final Fallback:** Config value (278 PKR)

**Error Handling:**
- Logs warnings if API calls fail
- Gracefully falls back to next option
- Never throws exceptions (always returns a value)

---

## Models Analysis

### `User.php` - User Model

**Traits:**
```php
use HasApiTokens, Notifiable, TwoFactorAuthenticatable, HasFactory;
```
- `HasApiTokens`: Sanctum API authentication
- `Notifiable`: Email notifications
- `TwoFactorAuthenticatable`: Fortify 2FA support
- `HasFactory`: Model factories for testing

**Fillable Attributes (Lines 22-48):**
- Basic: `name`, `email`, `password`, `phone`, `role`
- Business: `company_name`, `investment_capacity`, `experience`
- Wallet: `wallet_address`, `token_balance`, `coin_price`
- Referral: `referral_code`, `reseller_id`
- KYC: `kyc_status`, `kyc_id_type`, `kyc_id_number`, `kyc_full_name`, `kyc_id_front_path`, `kyc_id_back_path`, `kyc_selfie_path`, `kyc_submitted_at`, `kyc_approved_at`
- Profile: `avatar`, `status`, `receipt_screenshot`

**Hidden Attributes (Lines 55-58):**
- `password`, `remember_token` - Never serialized in JSON

**Casts (Lines 65-70):**
```php
protected $casts = [
    'email_verified_at' => 'datetime',
    'two_factor_confirmed_at' => 'datetime',
    'kyc_submitted_at' => 'datetime',
    'kyc_approved_at' => 'datetime',
];
```
- Converts database strings to Carbon instances

**Relationships:**

1. **transactions()** (Lines 73-76)
   ```php
   return $this->hasMany(Transaction::class);
   ```
   - One-to-many: User has many transactions

2. **reseller()** (Lines 78-81)
   ```php
   return $this->belongsTo(User::class, 'reseller_id');
   ```
   - Many-to-one: User belongs to a reseller (self-referential)

3. **referredUsers()** (Lines 83-86)
   ```php
   return $this->hasMany(User::class, 'reseller_id');
   ```
   - One-to-many: Reseller has many referred users

4. **cryptoPayments()** (Lines 88-91)
   ```php
   return $this->hasMany(CryptoPayment::class);
   ```
   - One-to-many: User has many crypto payments

5. **buyFromResellerRequests()** (Lines 93-96)
   ```php
   return $this->hasMany(BuyFromResellerRequest::class);
   ```
   - One-to-many: User has many buy requests

**Helper Methods:**

1. **isAdmin()** (Lines 124-127)
   ```php
   public function isAdmin(): bool
   {
       return $this->role === 'admin';
   }
   ```

2. **getAvatarUrlAttribute()** (Lines 132-138)
   ```php
   public function getAvatarUrlAttribute(): string
   {
       if ($this->avatar) {
           return asset('storage/' . $this->avatar);
       }
       return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=E30613&color=fff';
   }
   ```
   - Returns avatar URL or generates one from name

3. **addTokens()** (Lines 143-150+)
   ```php
   public function addTokens($amount, $description = 'Token credit')
   {
       $this->increment('token_balance', $amount);
       
       // Log transaction
       $this->transactions()->create([
           'type' => 'credit',
           'amount' => $amount,
           // ...
       ]);
   }
   ```
   - Adds tokens to balance and logs transaction

---

## Frontend Assets

### `resources/js/app.js` - Main JavaScript File

#### Tab Session Management (Lines 9-37)

**Purpose:** Creates unique session ID per browser tab

**Implementation:**
```javascript
(function () {
    try {
        const storageKey = 'tabSessionId';
        let tabSessionId = window.sessionStorage.getItem(storageKey);

        if (!tabSessionId) {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                tabSessionId = window.crypto.randomUUID();
            } else {
                // Fallback UUID-ish string
                tabSessionId = 'tab-' + Math.random().toString(36).slice(2) + Date.now().toString(36);
            }
            window.sessionStorage.setItem(storageKey, tabSessionId);
        }

        // Set lightweight cookie scoped to this browser instance / tab
        let cookie = `tab_session_id=${encodeURIComponent(tabSessionId)}; path=/; SameSite=Lax`;
        if (window.location.protocol === 'https:') {
            cookie += '; Secure';
        }
        document.cookie = cookie;
    } catch (e) {
        console.error('tabSessionId initialization failed', e);
    }
})();
```

**Features:**
- Uses `crypto.randomUUID()` if available (modern browsers)
- Fallback to custom UUID generation
- Stores in `sessionStorage` (tab-specific)
- Sets cookie for server-side access
- Adds `Secure` flag in HTTPS

#### Laravel Echo Initialization (Lines 39-54)

```javascript
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || process.env.MIX_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || process.env.MIX_PUSHER_APP_CLUSTER || 'ap2',
    forceTLS: true,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    }
});
```

**Configuration:**
- Uses Pusher as broadcaster
- Reads keys from Vite env or Mix env (backward compatibility)
- Forces TLS and encryption
- Includes CSRF token in auth headers

#### Alpine.js Components

**1. Countdown Timer (Lines 57-88)**
```javascript
Alpine.data('countdown', () => ({
    targetDate: new Date('2024-06-01T00:00:00Z'),
    timeLeft: { days: 0, hours: 0, minutes: 0, seconds: 0 },
    completed: false,

    init() {
        this.updateCountdown();
        setInterval(() => this.updateCountdown(), 1000);
    },

    updateCountdown() {
        const now = new Date().getTime();
        const distance = this.targetDate.getTime() - now;

        if (distance < 0) {
            this.completed = true;
            return;
        }

        this.timeLeft = {
            days: Math.floor(distance / (1000 * 60 * 60 * 24)),
            hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((distance % (1000 * 60)) / 1000)
        };
    }
}));
```
- Updates every second
- Calculates days, hours, minutes, seconds until target date
- Sets `completed` flag when target date passes

**2. Login Form (Lines 1003-1080)**

**Admin Detection:**
```javascript
checkAdminEmail(value) {
    if (value) {
        const emailLower = value.toLowerCase().trim();
        this.isAdmin = emailLower.includes('admin@') || 
                       emailLower.includes('@admin') ||
                       emailLower === 'admin@rwamp.net' ||
                       emailLower === 'superadmin@rwamp.net' ||
                       emailLower.includes('admin@');
        
        if (this.isAdmin) {
            this.selectedRole = '';
        }
    } else {
        this.isAdmin = false;
    }
}
```
- Detects admin emails (contains "admin@")
- Hides role selection for admins
- Watches email input for changes

**3. Signup Tabs (Lines 599-937)**

**Features:**
- Tab switching (investor/reseller)
- Email validation (debounced, 500ms)
- Phone validation (debounced, 500ms)
- Name validation (full name required)
- Password criteria checking
- Referral code validation

**Referral Code Validation (Lines 874-936):**
```javascript
async validateReferralCode(code) {
    // Normalize code
    const normalizedCode = code.trim().toUpperCase();
    inputEl.value = normalizedCode;

    // Validate format
    if (!/^RSL\d+$/.test(normalizedCode)) {
        // Show error
        return;
    }

    // Check if referral code exists
    try {
        const response = await fetch(`/api/check-referral-code?code=${encodeURIComponent(normalizedCode)}`);
        const data = await response.json();
        
        if (data.valid && data.reseller_name) {
            // Display reseller name prominently
            messageEl.innerHTML = `✓ Valid referral code - You'll be linked to <strong>${data.reseller_name}</strong>`;
            messageEl.className = 'text-sm text-green-600 font-semibold';
            inputEl.classList.add('border-green-500');
        }
    } catch (error) {
        // Error handling
    }
}
```

**4. Scroll Animations (Lines 1114-1208)**

**Intersection Observer:**
```javascript
const animationObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const animationType = entry.target.dataset.animation || 'fadeInUp';
            entry.target.classList.add('animated', animationType);
            animationObserver.unobserve(entry.target);
        }
    });
}, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
});
```

**Features:**
- Observes elements with `.animate-on-scroll` class
- Triggers animation when element enters viewport
- Supports multiple animation types (fadeInUp, fadeInDown, zoomIn, etc.)
- Hero section animates immediately (no scroll needed)
- Stagger delays for child elements

---

## Views Analysis

### `layouts/app.blade.php` - Main Layout

**Lines 1-2: HTML Declaration**
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="font-montserrat font-roboto font-mono">
```
- Sets language from Laravel locale
- Applies font classes

**Lines 4-6: Meta Tags**
```blade
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
```
- UTF-8 encoding
- Responsive viewport
- CSRF token for AJAX requests

**Lines 8-13: SEO Meta Tags**
```blade
<title>{{ $title ?? 'RWAMP – The Currency of Real Estate Investments' }}</title>
<meta name="description" content="{{ $description ?? 'Invest in Dubai...' }}">
<meta name="keywords" content="{{ $keywords ?? 'RWAMP, real estate...' }}">
<meta name="author" content="RWAMP">
<link rel="canonical" href="{{ url()->current() }}">
<meta name="robots" content="index,follow">
```
- Dynamic title with fallback
- SEO description and keywords
- Canonical URL
- Search engine indexing enabled

**Lines 15-20: Favicon**
```blade
<link rel="icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
<link rel="icon" href="{{ asset('images/logo.jpeg') }}?v=2" type="image/jpeg" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('images/logo.jpeg') }}?v=2">
<link rel="manifest" href="{{ asset('manifest.json') }}">
```
- Multiple favicon formats
- Version query string for cache busting
- Apple touch icon
- Web app manifest

**Lines 22-28: Open Graph Tags**
```blade
<meta property="og:title" content="{{ $ogTitle ?? ($title ?? 'RWAMP') }}">
<meta property="og:description" content="{{ $ogDescription ?? ($description ?? 'RWAMP – Real estate investment token.') }}">
<meta property="og:image" content="{{ $ogImage ?? asset('images/logo.jpeg') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="RWAMP">
```
- Facebook/LinkedIn sharing tags
- Dynamic OG image with fallback

**Lines 30-35: Twitter Card Tags**
```blade
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@rwamp">
<meta name="twitter:title" content="{{ $twitterTitle ?? ($title ?? 'RWAMP') }}">
<meta name="twitter:description" content="{{ $twitterDescription ?? ($description ?? 'RWAMP – Real estate investment token.') }}">
<meta name="twitter:image" content="{{ $twitterImage ?? asset('images/logo.jpeg') }}">
```
- Twitter sharing optimization
- Large image card format

**Lines 37-40: Google Fonts**
```blade
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;500;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
```
- Preconnect for faster font loading
- Loads Montserrat, Roboto, JetBrains Mono

**Line 43: Vite Assets**
```blade
@vite(['resources/css/app.css','resources/js/app.js'])
```
- Loads CSS and JS via Vite
- Includes hot module replacement in development

**Lines 45-55: Structured Data (JSON-LD)**
```blade
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "RWAMP",
  "url": "{{ url('/') }}",
  "logo": "{{ asset('images/logo.jpeg') }}",
  "sameAs": []
}
</script>
```
- Schema.org markup for SEO
- Organization type

**Lines 57-66: Google Analytics**
```blade
@if(config('app.google_analytics_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('app.google_analytics_id') }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('app.google_analytics_id') }}');
</script>
@endif
```
- Conditional GA loading
- Async script loading

**Lines 68-87: Meta Pixel (Facebook)**
```blade
@if(config('app.meta_pixel_id'))
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    // ... Facebook Pixel code
    fbq('init', '{{ config('app.meta_pixel_id') }}');
    fbq('track', 'PageView');
</script>
@endif
```
- Facebook Pixel for tracking
- PageView event on load

**Lines 92-106: Tab Session Display**
```blade
@php
    $tabId = request()->cookie('tab_session_id');
@endphp

@if(auth()->check() && $tabId)
    <div class="w-full bg-gray-900 text-gray-300 text-xs px-4 py-1 flex items-center justify-end gap-2">
        <span class="opacity-75">
            Tab: {{ substr($tabId, 0, 8) }}
        </span>
        <span class="opacity-75">&bull;</span>
        <span class="font-semibold">
            {{ auth()->user()->name }}
        </span>
    </div>
@endif
```
- Shows tab ID and user name in debug mode
- Only visible when authenticated and tab ID exists

**Lines 108-112: Main Content**
```blade
@include('components.navbar')

<main class="pt-16">
    @yield('content')
</main>
```
- Includes navbar component
- Main content area with top padding (for fixed navbar)
- Yields to child views

**Line 118: Tawk.to Chat Widget**
```blade
@include('components.tawk-to')
```
- Includes live chat widget

---

## Middleware & Services

### Key Middleware

1. **`role:admin`** - Checks if user has admin role
2. **`role:reseller`** - Checks if user has reseller role
3. **`role:investor`** - Checks if user has investor role
4. **`admin.2fa`** - Requires 2FA for admin routes
5. **`kyc.approved`** - Requires KYC approval
6. **`throttle:5,1`** - Rate limiting (5 requests per minute)

### Services

1. **`TabAuthService`** - Manages per-tab authentication
2. **`EmailService`** - Handles email sending
3. **`PriceHelper`** - Price management (static methods)

---

## Summary

This Laravel application is a comprehensive real estate token investment platform with:

1. **Multi-role System**: Admin, Reseller, Investor
2. **Authentication**: Fortify with 2FA, email verification (OTP)
3. **Payment System**: Crypto payments (USDT, BTC), QR codes
4. **KYC System**: Document upload and approval workflow
5. **Referral System**: Reseller referral codes (RSL format)
6. **Price Management**: Dynamic pricing with cache and database persistence
7. **Real-time Features**: Pusher integration (currently disabled)
8. **Frontend**: Alpine.js, Tailwind CSS, responsive design
9. **SEO**: Comprehensive meta tags, structured data, sitemap
10. **Security**: CSRF protection, rate limiting, reCAPTCHA

The codebase follows Laravel best practices with proper separation of concerns, route organization, and middleware usage.


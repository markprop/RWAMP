<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CryptoPaymentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Main pages
Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/become-partner', [PageController::class, 'becomePartner'])->name('become.partner');
Route::get('/whitepaper', [PageController::class, 'whitepaper'])->name('whitepaper');
Route::get('/whitepaper/pdf', [PageController::class, 'serveWhitepaper'])->name('whitepaper.pdf');
// Purchase flow (KYC requirement disabled - all users can purchase)
Route::middleware(['auth'])->group(function () {
    Route::get('/purchase', [CryptoPaymentController::class, 'create'])->name('purchase.create');
});
Route::get('/qr-code/{network}', [CryptoPaymentController::class, 'generateQrCode'])->name('qr-code.generate');
Route::get('/how-to-buy', [PageController::class, 'howToBuy'])->name('how-to-buy');
Route::get('/privacy-policy', function () {
    return view('pages.privacy-policy', [
        'title' => 'Privacy Policy – RWAMP',
        'description' => 'Learn how RWAMP collects, uses, and protects your personal data. Read our full privacy policy.',
        'ogTitle' => 'Privacy Policy – RWAMP',
        'ogDescription' => 'Learn how RWAMP collects, uses, and protects your personal data.',
        'ogImage' => asset('images/logo.jpeg'),
        'twitterTitle' => 'Privacy Policy – RWAMP',
        'twitterDescription' => 'How RWAMP handles your data securely and transparently.',
        'twitterImage' => asset('images/logo.jpeg'),
    ]);
})->name('privacy.policy');
Route::get('/terms-of-service', function () {
    return view('pages.terms-of-service', [
        'title' => 'Terms of Service – RWAMP',
        'description' => 'Read RWAMP’s terms of service covering account use, payments, disclaimers, and legal obligations.',
        'ogTitle' => 'Terms of Service – RWAMP',
        'ogDescription' => 'Rules for using RWAMP services, accounts, and limitations.',
        'ogImage' => asset('images/logo.jpeg'),
        'twitterTitle' => 'Terms of Service – RWAMP',
        'twitterDescription' => 'Rules for using RWAMP services, accounts, and limitations.',
        'twitterImage' => asset('images/logo.jpeg'),
    ]);
})->name('terms.of.service');
Route::get('/disclaimer', function () {
    return view('pages.disclaimer', [
        'title' => 'Investment Disclaimer – RWAMP',
        'description' => 'Important risk disclosures for RWAMP token investors. Not financial advice. Read before investing.',
        'ogTitle' => 'Investment Disclaimer – RWAMP',
        'ogDescription' => 'Notices on risks, liability, and third‑party links for RWAMP users.',
        'ogImage' => asset('images/logo.jpeg'),
        'twitterTitle' => 'Investment Disclaimer – RWAMP',
        'twitterDescription' => 'Important risk disclosures for RWAMP token investors.',
        'twitterImage' => asset('images/logo.jpeg'),
    ]);
})->name('disclaimer');

// robots.txt
Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml'))
        ->header('Content-Type', 'text/plain');
});

// Dynamic XML sitemap (public pages + optional future content)
Route::get('/sitemap.xml', function () {
    $now = now()->toAtomString();

    $staticUrls = [
        [ 'loc' => route('home'),               'changefreq' => 'weekly',  'priority' => '1.0', 'lastmod' => $now ],
        [ 'loc' => route('about'),              'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => $now ],
        [ 'loc' => route('contact'),            'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => $now ],
        [ 'loc' => route('privacy.policy'),     'changefreq' => 'yearly',  'priority' => '0.4', 'lastmod' => $now ],
        [ 'loc' => route('terms.of.service'),   'changefreq' => 'yearly',  'priority' => '0.4', 'lastmod' => $now ],
        [ 'loc' => route('disclaimer'),         'changefreq' => 'yearly',  'priority' => '0.4', 'lastmod' => $now ],
    ];

    // Optional index pages for projects/docs if your site exposes them
    $staticUrls[] = [ 'loc' => url('/projects'), 'changefreq' => 'weekly', 'priority' => '0.7', 'lastmod' => $now ];
    $staticUrls[] = [ 'loc' => url('/docs'),     'changefreq' => 'weekly', 'priority' => '0.6', 'lastmod' => $now ];

    // Future-proof: include blog/news if models exist
    $dynamicUrls = [];

    if (class_exists('App\\Models\\Post')) {
        foreach (App\Models\Post::query()->latest('updated_at')->get(['slug','updated_at']) as $post) {
            $dynamicUrls[] = [
                'loc' => url('/blog/'.$post->slug),
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'lastmod' => optional($post->updated_at)->toAtomString() ?? $now,
            ];
        }
    }

    if (class_exists('App\\Models\\News')) {
        foreach (App\Models\News::query()->latest('updated_at')->get(['slug','updated_at']) as $news) {
            $dynamicUrls[] = [
                'loc' => url('/news/'.$news->slug),
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'lastmod' => optional($news->updated_at)->toAtomString() ?? $now,
            ];
        }
    }

    // Dynamic: Projects
    if (class_exists('App\\Models\\Project')) {
        foreach (App\Models\Project::query()->latest('updated_at')->get(['slug','updated_at']) as $p) {
            $dynamicUrls[] = [
                'loc' => url('/projects/'.$p->slug),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => optional($p->updated_at)->toAtomString() ?? $now,
            ];
        }
    }

    // Dynamic: Docs
    if (class_exists('App\\Models\\Doc')) {
        foreach (App\Models\Doc::query()->latest('updated_at')->get(['slug','updated_at']) as $d) {
            $dynamicUrls[] = [
                'loc' => url('/docs/'.$d->slug),
                'changefreq' => 'weekly',
                'priority' => '0.6',
                'lastmod' => optional($d->updated_at)->toAtomString() ?? $now,
            ];
        }
    }

    $urls = array_merge($staticUrls, $dynamicUrls);

    $xml = view('sitemap.xml', compact('urls'))->render();
    return response($xml)->header('Content-Type', 'application/xml');
});

// KYC routes
Route::middleware(['auth'])->group(function () {
    Route::get('/kyc', [KycController::class, 'show'])->name('kyc.show');
    Route::post('/kyc/submit', [KycController::class, 'submit'])->name('kyc.submit');
});

// Role-based dashboards
Route::middleware(['auth'])->group(function () {
    // Change password required (for first-time reseller logins)
    Route::get('/change-password-required', [AuthController::class, 'showChangePasswordRequired'])->name('password.change.required');
    Route::post('/change-password-required', [AuthController::class, 'changePasswordRequired'])->name('password.change.required.post');
    
    Route::get('/dashboard/investor', [CryptoPaymentController::class, 'investorDashboard'])->middleware(['role:investor', 'kyc.approved'])->name('dashboard.investor');
    Route::middleware('role:reseller')->group(function () {
        Route::get('/dashboard/reseller', [ResellerController::class, 'dashboard'])->name('dashboard.reseller');
        Route::get('/dashboard/reseller/users', [ResellerController::class, 'users'])->name('reseller.users');
        Route::get('/dashboard/reseller/users/{user}', [ResellerController::class, 'viewUser'])->name('reseller.users.view');
        Route::get('/dashboard/reseller/payments', [ResellerController::class, 'payments'])->name('reseller.payments');
        Route::get('/dashboard/reseller/payments/{payment}', [ResellerController::class, 'viewPayment'])->name('reseller.payments.view');
        Route::post('/dashboard/reseller/payments/{payment}/reject', [ResellerController::class, 'rejectPayment'])->name('reseller.payments.reject');
        Route::get('/dashboard/reseller/transactions', [ResellerController::class, 'transactions'])->name('reseller.transactions');
        Route::get('/dashboard/reseller/transactions/{transaction}', [ResellerController::class, 'viewTransaction'])->name('reseller.transactions.view');
        Route::get('/dashboard/reseller/sell', [ResellerController::class, 'sellPage'])->name('reseller.sell');
        Route::get('/api/reseller/search-users', [ResellerController::class, 'searchUsersForSell'])->name('reseller.search-users');
        Route::put('/dashboard/reseller/coin-price', [ResellerController::class, 'updateCoinPrice'])->name('reseller.update-coin-price');
        Route::get('/dashboard/reseller/buy-requests', [ResellerController::class, 'buyRequests'])->name('reseller.buy-requests');
        Route::post('/dashboard/reseller/buy-requests/{buyRequest}/approve', [ResellerController::class, 'approveBuyRequest'])->name('reseller.buy-requests.approve');
        Route::post('/dashboard/reseller/buy-requests/{buyRequest}/reject', [ResellerController::class, 'rejectBuyRequest'])->name('reseller.buy-requests.reject');
    });
    Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])->middleware(['role:admin','admin.2fa'])->name('dashboard.admin');
    Route::post('/dashboard/admin/regenerate-recovery-codes', [AdminController::class, 'regenerateRecoveryCodes'])->middleware(['role:admin','admin.2fa'])->name('admin.regenerate-recovery-codes');
    Route::get('/dashboard/admin/crypto-payments', [AdminController::class, 'cryptoPayments'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.payments');
    Route::get('/dashboard/admin/crypto-payments/{payment}/details', [AdminController::class, 'cryptoPaymentDetails'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.payments.details');
    Route::get('/dashboard/admin/crypto-payments/{payment}/screenshot', [AdminController::class, 'downloadCryptoPaymentScreenshot'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.payments.screenshot');
    Route::put('/dashboard/admin/crypto-payments/{payment}', [AdminController::class, 'updateCryptoPayment'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.payments.update');
    Route::delete('/dashboard/admin/crypto-payments/{payment}', [AdminController::class, 'deleteCryptoPayment'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.payments.delete');
    Route::get('/dashboard/admin/history', [AdminController::class, 'history'])->middleware(['role:admin','admin.2fa'])->name('admin.history');
    Route::get('/dashboard/admin/applications', [AdminController::class, 'applicationsIndex'])->middleware(['role:admin','admin.2fa'])->name('admin.applications');
    // Admin User Management
    Route::get('/dashboard/admin/users', [AdminController::class, 'usersIndex'])->middleware(['role:admin','admin.2fa'])->name('admin.users');
    Route::post('/dashboard/admin/users', [AdminController::class, 'usersStore'])->middleware(['role:admin','admin.2fa'])->name('admin.users.store');
    Route::get('/dashboard/admin/users/{user}/details', [AdminController::class, 'userDetails'])->middleware(['role:admin','admin.2fa'])->name('admin.users.details');
    Route::put('/dashboard/admin/users/{user}', [AdminController::class, 'usersUpdate'])->middleware(['role:admin','admin.2fa'])->name('admin.users.update');
    Route::post('/dashboard/admin/users/{user}/reset-password', [AdminController::class, 'usersResetPassword'])->middleware(['role:admin','admin.2fa'])->name('admin.users.reset');
    Route::post('/dashboard/admin/users/{user}/assign-wallet', [AdminController::class, 'assignWalletAddress'])->middleware(['role:admin','admin.2fa'])->name('admin.users.assign-wallet');
    Route::delete('/dashboard/admin/users/{user}', [AdminController::class, 'usersDelete'])->middleware(['role:admin','admin.2fa'])->name('admin.users.delete');
    Route::get('/dashboard/history', [CryptoPaymentController::class, 'userHistory'])->name('user.history');
    Route::post('/admin/crypto-payments/{payment}/approve', [AdminController::class, 'approveCryptoPayment'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.approve');
    Route::post('/admin/crypto-payments/{payment}/reject', [AdminController::class, 'rejectCryptoPayment'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.reject');
    // 2FA setup route with dedicated UI
    Route::get('/admin/2fa/setup', [AdminController::class, 'showTwoFactorSetup'])->middleware('role:admin')->name('admin.2fa.setup');
    Route::post('/admin/2fa/regenerate-recovery-codes', [AdminController::class, 'regenerateRecoveryCodes'])->middleware('role:admin')->name('admin.2fa.regenerate-recovery-codes');
    Route::put('/admin/applications/{application}/approve', [AdminController::class, 'approve'])->middleware(['role:admin','admin.2fa'])->name('admin.applications.approve');
    Route::put('/admin/applications/{application}/reject', [AdminController::class, 'reject'])->middleware(['role:admin','admin.2fa'])->name('admin.applications.reject');
    Route::get('/dashboard/admin/applications/{application}/details', [AdminController::class, 'applicationDetails'])->middleware(['role:admin','admin.2fa'])->name('admin.applications.details');
    Route::put('/dashboard/admin/applications/{application}', [AdminController::class, 'applicationUpdate'])->middleware(['role:admin','admin.2fa'])->name('admin.applications.update');
    Route::delete('/dashboard/admin/applications/{application}', [AdminController::class, 'applicationDelete'])->middleware(['role:admin','admin.2fa'])->name('admin.applications.delete');
    
    // Admin KYC routes
    Route::middleware(['role:admin','admin.2fa'])->group(function () {
        Route::get('/dashboard/admin/kyc', [AdminController::class, 'kycList'])->name('admin.kyc.list');
        Route::post('/admin/kyc/{user}/approve', [AdminController::class, 'approveKyc'])->name('admin.kyc.approve');
        Route::post('/admin/kyc/{user}/reject', [AdminController::class, 'rejectKyc'])->name('admin.kyc.reject');
        Route::put('/admin/kyc/{user}/update', [AdminController::class, 'updateKyc'])->name('admin.kyc.update');
        Route::delete('/admin/kyc/{user}/delete', [AdminController::class, 'deleteKyc'])->name('admin.kyc.delete');
        Route::get('/admin/kyc/{user}/download/{type}', [AdminController::class, 'downloadKycFile'])->name('admin.kyc.download');
    });
    
    // Admin Price Management routes
    Route::middleware(['role:admin','admin.2fa'])->group(function () {
        Route::get('/dashboard/admin/prices', [AdminController::class, 'priceManagement'])->name('admin.prices');
        Route::post('/dashboard/admin/prices/update', [AdminController::class, 'updatePrices'])->name('admin.prices.update');
    });
    
    // Admin Withdrawal Management routes
    Route::middleware(['role:admin','admin.2fa'])->group(function () {
        Route::get('/dashboard/admin/withdrawals', [AdminController::class, 'withdrawals'])->name('admin.withdrawals');
        Route::get('/dashboard/admin/withdrawals/{withdrawal}', [AdminController::class, 'showWithdrawal'])->name('admin.withdrawals.show');
        Route::post('/dashboard/admin/withdrawals/{withdrawal}/approve', [AdminController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
        Route::post('/dashboard/admin/withdrawals/{withdrawal}/reject', [AdminController::class, 'rejectWithdrawal'])->name('admin.withdrawals.reject');
        Route::post('/dashboard/admin/withdrawals/{withdrawal}/submit-receipt', [AdminController::class, 'submitReceipt'])->name('admin.withdrawals.submit-receipt');
        Route::put('/dashboard/admin/withdrawals/{withdrawal}', [AdminController::class, 'updateWithdrawal'])->name('admin.withdrawals.update');
        Route::delete('/dashboard/admin/withdrawals/{withdrawal}', [AdminController::class, 'deleteWithdrawal'])->name('admin.withdrawals.delete');
    });
    
    // Admin Sell Coins routes
    Route::middleware(['role:admin','admin.2fa'])->group(function () {
        Route::get('/dashboard/admin/sell', [AdminController::class, 'sellPage'])->name('admin.sell');
        Route::get('/api/admin/search-users', [AdminController::class, 'searchUsersForSell'])->name('admin.search-users');
        Route::post('/api/admin/send-otp', [AdminController::class, 'sendOtpForSell'])->name('admin.send-otp');
        Route::post('/api/admin/fetch-payment-proof', [AdminController::class, 'fetchUserPaymentProof']);
        Route::post('/api/admin/sell-coins', [AdminController::class, 'sellCoins'])->name('admin.sell-coins');
    });
    
    // ============================================
    // CHAT SYSTEM DISABLED - See CHAT_REENABLE_GUIDE.md to re-enable
    // ============================================
    
    // Admin Chat routes (read-only) - DISABLED
    // Route::middleware(['role:admin','admin.2fa'])->group(function () {
    //     Route::get('/dashboard/admin/chats', [AdminController::class, 'chatsIndex'])->name('admin.chats.index');
    //     Route::get('/dashboard/admin/chats/{chat}', [AdminController::class, 'viewChat'])->name('admin.chat.view');
    //     Route::get('/dashboard/admin/chats/{chat}/audit', [AdminController::class, 'auditTrail'])->name('admin.chat.audit');
    // });
    
    // Chat routes (User/Reseller) - DISABLED
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::get('/chat/{chat}', [ChatController::class, 'show'])->name('chat.show');
    // Route::get('/api/chat/{chat}', [ChatController::class, 'show'])->name('api.chat.show');
    // Route::post('/chat/create-private', [ChatController::class, 'createPrivateChat'])->name('chat.create.private');
    // Route::post('/chat/create-group', [ChatController::class, 'createGroupChat'])->name('chat.create.group');
    // Route::post('/chat/{chat}/message', [ChatController::class, 'storeMessage'])->name('chat.message.store');
    // Route::post('/chat/{chat}/receipt', [ChatController::class, 'uploadReceipt'])->name('chat.receipt.upload');
    // Route::post('/chat/{chat}/voice', [ChatController::class, 'uploadVoice'])->name('chat.voice.upload');
    // Route::post('/chat/{chat}/message/{message}/react', [ChatController::class, 'reactToMessage'])->name('chat.message.react');
    // Route::post('/chat/{chat}/message/{message}/read', [ChatController::class, 'markMessageAsRead'])->name('chat.message.read');
    // Route::delete('/chat/{chat}/message/{message}', [ChatController::class, 'deleteMessage'])->name('chat.message.delete');
    // Route::post('/chat/{chat}/pin', [ChatController::class, 'togglePin'])->name('chat.pin');
    // Route::post('/chat/{chat}/mute', [ChatController::class, 'toggleMute'])->name('chat.mute');
    // Route::post('/chat/{chat}/archive', [ChatController::class, 'toggleArchive'])->name('chat.archive');
    // Route::get('/api/chat/search-users', [ChatController::class, 'searchUsers'])->name('chat.search.users');

    // Helper: Open purchase modal on the appropriate dashboard (used for post-login/signup redirect)
    Route::get('/open-purchase', function () {
        $user = auth()->user();
        $dashboard = match ($user->role ?? null) {
            'admin' => route('dashboard.admin'),
            'reseller' => route('dashboard.reseller'),
            default => route('dashboard.investor'),
        };
        return redirect()->to($dashboard . '?open=purchase');
    })->name('open.purchase');
});

// Form submissions
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,60')->name('contact.store');
Route::post('/reseller', [ResellerController::class, 'store'])->middleware('throttle:3,60')->name('reseller.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:6,60')->name('newsletter.store');

// API routes for AJAX requests
Route::prefix('api')->group(function () {
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,60');
    Route::post('/reseller', [ResellerController::class, 'store'])->middleware('throttle:3,60');
    Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:6,60');
    Route::get('/check-referral-code', [AuthController::class, 'checkReferralCode'])->name('api.check.referral.code');
    Route::get('/check-email', [AuthController::class, 'checkEmail'])->name('api.check.email');
    Route::get('/check-phone', [AuthController::class, 'checkPhone'])->name('api.check.phone');
    
    // Crypto payment API routes
    Route::middleware('auth')->group(function () {
        Route::post('/save-wallet-address', [CryptoPaymentController::class, 'saveWalletAddress']);
        Route::post('/check-payment-status', [CryptoPaymentController::class, 'checkPaymentStatus']);
        Route::post('/submit-tx-hash', [CryptoPaymentController::class, 'submitTxHash']);
        Route::post('/check-auto-payment', [CryptoPaymentController::class, 'checkAutoPaymentStatus']);
        
        // Wallet lookup API (admin/reseller only)
        Route::middleware('role:admin,reseller')->group(function () {
            Route::post('/users/lookup-by-wallet', [\App\Http\Controllers\UserController::class, 'lookupByWallet'])
                ->middleware('throttle:10,1')
                ->name('api.users.lookup-by-wallet');
        });
        
        // Reseller API routes
        Route::middleware('role:reseller')->group(function () {
            Route::post('/reseller/sell', [ResellerController::class, 'sell'])->middleware('throttle:5,1');
            Route::post('/reseller/send-otp', [ResellerController::class, 'sendOtp']);
            Route::post('/reseller/fetch-payment-proof', [ResellerController::class, 'fetchUserPaymentProof']);
            Route::post('/reseller/crypto-payments/{payment}/approve', [ResellerController::class, 'approveCryptoPayment']);
        });
        
        // User API routes
        Route::post('/user/buy-from-reseller', [CryptoPaymentController::class, 'buyFromReseller']);
        Route::get('/user/buy-from-reseller', [CryptoPaymentController::class, 'buyFromResellerPage'])->name('buy.from.reseller');
        Route::post('/user/buy-from-reseller/request', [CryptoPaymentController::class, 'createBuyFromResellerRequest'])->name('buy.from.reseller.request');
        Route::post('/user/buy-from-reseller/send-otp', [CryptoPaymentController::class, 'sendOtpForBuyRequest'])->name('buy.from.reseller.send-otp');
        Route::post('/user/withdraw', [WithdrawController::class, 'store'])->name('user.withdraw.store');
        Route::get('/user/withdrawals', [WithdrawController::class, 'index'])->name('user.withdrawals');
        Route::get('/user/withdrawals/{withdrawal}/receipt', [WithdrawController::class, 'viewReceipt'])->name('user.withdrawals.receipt');
        
        // Reseller search API (accessible to authenticated users)
        Route::get('/resellers/search', [CryptoPaymentController::class, 'searchResellers'])->name('api.resellers.search');
        
        // OTP verification
        Route::post('/verify-otp', [EmailVerificationController::class, 'verify']);
    });
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    
    // Email Verification (OTP-based)
    Route::get('/verify-email', [EmailVerificationController::class, 'show'])->name('verify-email');
    Route::post('/verify-email', [EmailVerificationController::class, 'verify'])->middleware('throttle:otp-verification')->name('verify-email.post');
    Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:otp-resend')->name('verify-email.resend');
    // Forgot password (request reset link)
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email']);
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );
        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    // Reset password form
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.passwords.reset', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');

    // Handle reset
    Route::post('/reset-password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);
        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($password),
                ])->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->save();
            }
        );
        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    })->name('password.update');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Profile & Account
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/account', [ProfileController::class, 'updateAccount'])->name('account.update');
    Route::put('/account/password', [ProfileController::class, 'updatePassword'])->name('account.password');
    Route::put('/wallet', [ProfileController::class, 'updateWallet'])->name('wallet.update');
    Route::post('/wallet/generate', [ProfileController::class, 'generateWallet'])->name('wallet.generate');
    Route::post('/email/verification/resend', [ProfileController::class, 'resendEmailVerification'])->name('email.verification.resend');
});

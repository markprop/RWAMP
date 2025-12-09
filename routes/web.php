<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\Register\RegisterController;
use App\Http\Controllers\Auth\Password\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CryptoPaymentController;
use App\Http\Controllers\AdminController;
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
use App\Http\Controllers\Investor\InvestorDashboardController;
use App\Http\Controllers\Investor\InvestorHistoryController;
use App\Http\Controllers\BuyFromReseller\BuyFromResellerController;
use App\Http\Controllers\Reseller\ResellerDashboardController;
use App\Http\Controllers\Reseller\ResellerUserController;
use App\Http\Controllers\Reseller\ResellerPaymentController;
use App\Http\Controllers\Reseller\ResellerTransactionController;
use App\Http\Controllers\Reseller\ResellerSellController;
use App\Http\Controllers\Reseller\ResellerBuyRequestController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameSettingController;
use App\Http\Controllers\WalletConnectController;

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
        'ogImage' => asset('images/logo.png'),
        'twitterTitle' => 'Privacy Policy – RWAMP',
        'twitterDescription' => 'How RWAMP handles your data securely and transparently.',
        'twitterImage' => asset('images/logo.png'),
    ]);
})->name('privacy.policy');
Route::get('/terms-of-service', function () {
    return view('pages.terms-of-service', [
        'title' => 'Terms of Service – RWAMP',
        'description' => 'Read RWAMP’s terms of service covering account use, payments, disclaimers, and legal obligations.',
        'ogTitle' => 'Terms of Service – RWAMP',
        'ogDescription' => 'Rules for using RWAMP services, accounts, and limitations.',
        'ogImage' => asset('images/logo.png'),
        'twitterTitle' => 'Terms of Service – RWAMP',
        'twitterDescription' => 'Rules for using RWAMP services, accounts, and limitations.',
        'twitterImage' => asset('images/logo.png'),
    ]);
})->name('terms.of.service');
Route::get('/disclaimer', function () {
    return view('pages.disclaimer', [
        'title' => 'Investment Disclaimer – RWAMP',
        'description' => 'Important risk disclosures for RWAMP token investors. Not financial advice. Read before investing.',
        'ogTitle' => 'Investment Disclaimer – RWAMP',
        'ogDescription' => 'Notices on risks, liability, and third‑party links for RWAMP users.',
        'ogImage' => asset('images/logo.png'),
        'twitterTitle' => 'Investment Disclaimer – RWAMP',
        'twitterDescription' => 'Important risk disclosures for RWAMP token investors.',
        'twitterImage' => asset('images/logo.png'),
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
    Route::get('/change-password-required', [PasswordController::class, 'show'])->name('password.change.required');
    Route::post('/change-password-required', [PasswordController::class, 'update'])->name('password.change.required.post');
    
    // Investor Dashboard
    Route::get('/dashboard/investor', [InvestorDashboardController::class, 'index'])->middleware(['role:investor', 'kyc.approved'])->name('dashboard.investor');
    
    // Legacy reseller dashboard route for backward compatibility (must be before route group)
    Route::get('/dashboard/reseller', [ResellerDashboardController::class, 'index'])->middleware('role:reseller')->name('dashboard.reseller');
    
    // Reseller Routes
    Route::middleware('role:reseller')->prefix('dashboard/reseller')->name('dashboard.reseller.')->group(function () {
        // Note: index route is defined above as 'dashboard.reseller' for backward compatibility
        
        // Users
        Route::get('/users', [ResellerUserController::class, 'index'])->name('users');
        Route::get('/users/{user}', [ResellerUserController::class, 'show'])->name('users.show');
        
        // Payments
        Route::get('/payments', [ResellerPaymentController::class, 'index'])->name('payments');
        Route::get('/payments/{payment}', [ResellerPaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/reject', [ResellerPaymentController::class, 'reject'])->name('payments.reject');
        Route::post('/payments/{payment}/approve', [ResellerPaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/payments/fetch-proof', [ResellerPaymentController::class, 'fetchUserPaymentProof'])->name('payments.fetch-proof');
        
        // Transactions
        Route::get('/transactions', [ResellerTransactionController::class, 'index'])->name('transactions');
        Route::get('/transactions/{transaction}', [ResellerTransactionController::class, 'show'])->name('transactions.show');
        
        // Sell
        Route::get('/sell', [ResellerSellController::class, 'index'])->name('sell');
        Route::post('/sell', [ResellerSellController::class, 'store'])->name('sell.store');
        Route::post('/coin-price', [ResellerSellController::class, 'updateCoinPrice'])->name('coin-price');
        
        // Buy Requests
        Route::get('/buy-requests', [ResellerBuyRequestController::class, 'index'])->name('buy-requests');
        Route::post('/buy-requests/{buyRequest}/approve', [ResellerBuyRequestController::class, 'approve'])->name('buy-requests.approve');
        Route::post('/buy-requests/{buyRequest}/reject', [ResellerBuyRequestController::class, 'reject'])->name('buy-requests.reject');
    });
    
    // Legacy reseller routes (for backward compatibility - will be removed)
    Route::middleware('role:reseller')->group(function () {
        Route::get('/dashboard/reseller/users', [ResellerUserController::class, 'index'])->name('reseller.users');
        Route::get('/dashboard/reseller/users/{user}', [ResellerUserController::class, 'show'])->name('reseller.users.view');
        Route::get('/dashboard/reseller/payments', [ResellerPaymentController::class, 'index'])->name('reseller.payments');
        Route::get('/dashboard/reseller/payments/{payment}', [ResellerPaymentController::class, 'show'])->name('reseller.payments.view');
        Route::post('/dashboard/reseller/payments/{payment}/reject', [ResellerPaymentController::class, 'reject'])->name('reseller.payments.reject');
        Route::get('/dashboard/reseller/transactions', [ResellerTransactionController::class, 'index'])->name('reseller.transactions');
        Route::get('/dashboard/reseller/transactions/{transaction}', [ResellerTransactionController::class, 'show'])->name('reseller.transactions.view');
        Route::get('/dashboard/reseller/sell', [ResellerSellController::class, 'index'])->name('reseller.sell');
        Route::put('/dashboard/reseller/coin-price', [ResellerSellController::class, 'updateCoinPrice'])->name('reseller.update-coin-price');
        Route::get('/dashboard/reseller/buy-requests', [ResellerBuyRequestController::class, 'index'])->name('reseller.buy-requests');
        Route::post('/dashboard/reseller/buy-requests/{buyRequest}/approve', [ResellerBuyRequestController::class, 'approve'])->name('reseller.buy-requests.approve');
        Route::post('/dashboard/reseller/buy-requests/{buyRequest}/reject', [ResellerBuyRequestController::class, 'reject'])->name('reseller.buy-requests.reject');
    });
    
    // Admin Dashboard
    Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])->middleware(['role:admin','admin.2fa'])->name('dashboard.admin');
    
    // Legacy admin route aliases for backward compatibility (views use these names)
    // These must be registered BEFORE route groups to avoid conflicts
    Route::get('/dashboard/admin/users', [AdminUserController::class, 'index'])
        ->middleware(['role:admin','admin.2fa'])
        ->name('admin.users');
    Route::get('/dashboard/admin/sell', [AdminSellController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.sell');
    Route::get('/dashboard/admin/applications', [AdminResellerApplicationController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.applications');
    Route::get('/dashboard/admin/crypto-payments', [AdminCryptoPaymentController::class, 'index'])
        ->middleware(['role:admin','admin.2fa'])
        ->name('admin.crypto.payments');
    // Admin withdrawals index (backward-compatible path)
    Route::get('/dashboard/admin/withdrawals', [AdminWithdrawalController::class, 'index'])
        ->middleware(['role:admin','admin.2fa'])
        ->name('admin.withdrawals');
    Route::get('/dashboard/admin/kyc', [AdminKycController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.kyc.list');
    Route::get('/dashboard/admin/prices', [AdminPriceController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.prices');
    Route::get('/dashboard/admin/history', [AdminCryptoPaymentController::class, 'history'])->middleware(['role:admin','admin.2fa'])->name('admin.history');
    Route::post('/dashboard/admin/regenerate-recovery-codes', [Admin2FAController::class, 'regenerateRecoveryCodes'])->middleware(['role:admin','admin.2fa'])->name('admin.regenerate-recovery-codes');
    
    // Admin Crypto Payments (ULID-based short URLs)
    Route::prefix('a/p')->middleware(['role:admin','admin.2fa'])->name('admin.crypto.payments.')->group(function () {
        // Note: index route is defined above as 'admin.crypto.payments' for backward compatibility
        Route::get('/{payment}/details', [AdminCryptoPaymentController::class, 'show'])->name('details');
        Route::get('/{payment}/screenshot', [AdminCryptoPaymentController::class, 'downloadScreenshot'])->name('screenshot');
        Route::put('/{payment}', [AdminCryptoPaymentController::class, 'update'])->name('update');
        Route::delete('/{payment}', [AdminCryptoPaymentController::class, 'destroy'])->name('delete');
        Route::post('/{payment}/approve', [AdminCryptoPaymentController::class, 'approve'])->name('approve');
        Route::post('/{payment}/reject', [AdminCryptoPaymentController::class, 'reject'])->name('reject');
    });
    
    // Legacy crypto payment routes for backward compatibility (commented out - route group above handles these)
    // Route::post('/admin/crypto-payments/{payment}/approve', [AdminCryptoPaymentController::class, 'approve'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.approve');
    // Route::post('/admin/crypto-payments/{payment}/reject', [AdminCryptoPaymentController::class, 'reject'])->middleware(['role:admin','admin.2fa'])->name('admin.crypto.reject');
    
    // Admin History
    Route::get('/dashboard/admin/history', [AdminCryptoPaymentController::class, 'history'])->middleware(['role:admin','admin.2fa'])->name('admin.history');
    
    // Admin Applications (reseller applications) - ULID URLs
    Route::prefix('a/ap')->middleware(['role:admin','admin.2fa'])->name('admin.applications.')->group(function () {
        // Note: index route is defined above as 'admin.applications' for backward compatibility
        Route::get('/{application}/details', [AdminResellerApplicationController::class, 'show'])->name('details');
        Route::put('/{application}/approve', [AdminResellerApplicationController::class, 'approve'])->name('approve');
        Route::put('/{application}/reject', [AdminResellerApplicationController::class, 'reject'])->name('reject');
        Route::put('/{application}', [AdminResellerApplicationController::class, 'update'])->name('update');
        Route::delete('/{application}', [AdminResellerApplicationController::class, 'destroy'])->name('delete');
    });
    
    // Legacy application routes for backward compatibility (using different names to avoid conflicts)
    // Note: The route group above already handles these, so these are just for backward compatibility
    // Route::get('/dashboard/admin/applications', [AdminResellerApplicationController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.applications');
    // Route::put('/admin/applications/{application}/approve', [AdminResellerApplicationController::class, 'approve'])->middleware(['role:admin','admin.2fa'])->name('admin.applications.approve');
    // Route::put('/admin/applications/{application}/reject', [AdminResellerApplicationController::class, 'reject'])->middleware(['role:admin','admin.2fa'])->name('admin.applications.reject');
    // Admin User Management - ULID URLs
    Route::middleware(['role:admin','admin.2fa'])->group(function () {
        // Short ULID-based URLs
        Route::prefix('a/u')->name('admin.users.')->group(function () {
            // Note: index route is defined above as 'admin.users' for backward compatibility
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/details', [AdminUserController::class, 'show'])->name('details'); // Alias
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{user}/assign-wallet', [AdminUserController::class, 'assignWalletAddress'])->name('assign-wallet');
        });

        // Legacy numeric ID URLs → 301 to ULID URLs
        Route::get('/dashboard/admin/users/{id}', function (int $id) {
            $user = \App\Models\User::findOrFail($id);
            return redirect()->route('admin.users.show', $user);
        })->whereNumber('id');
    });
    
    // Legacy admin user routes for backward compatibility (commented out - route group above handles these)
    // Route::get('/dashboard/admin/users', [AdminUserController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.users');
    // Route::post('/dashboard/admin/users', [AdminUserController::class, 'store'])->middleware(['role:admin','admin.2fa'])->name('admin.users.store');
    // Route::get('/dashboard/admin/users/{user}/details', [AdminUserController::class, 'show'])->middleware(['role:admin','admin.2fa'])->name('admin.users.details');
    // Route::put('/dashboard/admin/users/{user}', [AdminUserController::class, 'update'])->middleware(['role:admin','admin.2fa'])->name('admin.users.update');
    // Route::post('/dashboard/admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->middleware(['role:admin','admin.2fa'])->name('admin.users.reset');
    // Route::post('/dashboard/admin/users/{user}/assign-wallet', [AdminUserController::class, 'assignWalletAddress'])->middleware(['role:admin','admin.2fa'])->name('admin.users.assign-wallet');
    // Route::delete('/dashboard/admin/users/{user}', [AdminUserController::class, 'destroy'])->middleware(['role:admin','admin.2fa'])->name('admin.users.delete');
    
    // User History
    Route::get('/dashboard/history', [InvestorHistoryController::class, 'index'])->name('user.history');
    
    // Admin 2FA routes
    Route::prefix('admin/2fa')->middleware('role:admin')->name('admin.2fa.')->group(function () {
        Route::get('/setup', [Admin2FAController::class, 'show'])->name('setup');
        Route::post('/regenerate-recovery-codes', [Admin2FAController::class, 'regenerateRecoveryCodes'])->name('regenerate-recovery-codes');
    });
    
    // Admin KYC routes
    Route::prefix('dashboard/admin/kyc')->middleware(['role:admin','admin.2fa'])->name('admin.kyc.')->group(function () {
        Route::get('/', [AdminKycController::class, 'index'])->name('list');
        Route::post('/{user}/approve', [AdminKycController::class, 'approve'])->name('approve');
        Route::post('/{user}/reject', [AdminKycController::class, 'reject'])->name('reject');
        Route::put('/{user}/update', [AdminKycController::class, 'update'])->name('update');
        Route::delete('/{user}/delete', [AdminKycController::class, 'destroy'])->name('delete');
        Route::get('/{user}/download/{type}', [AdminKycController::class, 'downloadFile'])->name('download');
    });
    
    // Legacy KYC routes for backward compatibility (commented out - route group above handles these)
    // Route::post('/admin/kyc/{user}/approve', [AdminKycController::class, 'approve'])->middleware(['role:admin','admin.2fa'])->name('admin.kyc.approve');
    // Route::post('/admin/kyc/{user}/reject', [AdminKycController::class, 'reject'])->middleware(['role:admin','admin.2fa'])->name('admin.kyc.reject');
    // Route::put('/admin/kyc/{user}/update', [AdminKycController::class, 'update'])->middleware(['role:admin','admin.2fa'])->name('admin.kyc.update');
    // Route::delete('/admin/kyc/{user}/delete', [AdminKycController::class, 'destroy'])->middleware(['role:admin','admin.2fa'])->name('admin.kyc.delete');
    // Route::get('/admin/kyc/{user}/download/{type}', [AdminKycController::class, 'downloadFile'])->middleware(['role:admin','admin.2fa'])->name('admin.kyc.download');
    
    // Admin Price Management routes
    Route::prefix('dashboard/admin/prices')->middleware(['role:admin','admin.2fa'])->name('admin.prices.')->group(function () {
        // Note: index route is defined above as 'admin.prices' for backward compatibility
        Route::post('/update', [AdminPriceController::class, 'update'])->name('update');
    });
    
    // Legacy price routes for backward compatibility (commented out - route group above handles these)
    // Route::get('/dashboard/admin/prices', [AdminPriceController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.prices');
    // Route::post('/dashboard/admin/prices/update', [AdminPriceController::class, 'update'])->middleware(['role:admin','admin.2fa'])->name('admin.prices.update');
    
    // Admin Withdrawal Management routes - ULID URLs
    Route::prefix('a/w')->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals.')->group(function () {
        // Note: index route is defined above as 'admin.withdrawals' for backward compatibility
        Route::get('/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('show');
        Route::post('/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('reject');
        Route::post('/{withdrawal}/submit-receipt', [AdminWithdrawalController::class, 'submitReceipt'])->name('submit-receipt');
        Route::put('/{withdrawal}', [AdminWithdrawalController::class, 'update'])->name('update');
        Route::delete('/{withdrawal}', [AdminWithdrawalController::class, 'destroy'])->name('delete');
    });
    
    // Legacy withdrawal routes for backward compatibility (commented out - route group above handles these)
    // Route::get('/dashboard/admin/withdrawals', [AdminWithdrawalController::class, 'index'])->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals');
    // Route::get('/dashboard/admin/withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'show'])->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals.show');
    // Route::post('/dashboard/admin/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals.approve');
    // Route::post('/dashboard/admin/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals.reject');
    // Route::post('/dashboard/admin/withdrawals/{withdrawal}/submit-receipt', [AdminWithdrawalController::class, 'submitReceipt'])->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals.submit-receipt');
    // Route::put('/dashboard/admin/withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'update'])->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals.update');
    // Route::delete('/dashboard/admin/withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'destroy'])->middleware(['role:admin','admin.2fa'])->name('admin.withdrawals.delete');
    
    // Admin Sell Coins routes
    Route::prefix('dashboard/admin/sell')->middleware(['role:admin','admin.2fa'])->name('admin.sell.')->group(function () {
        // Note: index route is defined above as 'admin.sell' for backward compatibility
        Route::post('/', [AdminSellController::class, 'store'])->name('store');
    });
    
    Route::prefix('api/admin')->middleware(['role:admin','admin.2fa'])->name('admin.')->group(function () {
        Route::get('/search-users', [AdminSellController::class, 'searchUsers'])->name('search-users');
        Route::post('/send-otp', [AdminSellController::class, 'sendOtp'])->name('send-otp');
        Route::post('/fetch-payment-proof', [AdminSellController::class, 'fetchPaymentProof'])->name('fetch-payment-proof');
    });
    
    // Legacy sell routes for backward compatibility
    Route::post('/api/admin/sell-coins', [AdminSellController::class, 'store'])->middleware(['role:admin','admin.2fa'])->name('admin.sell-coins');
    
    // ============================================
    // CHAT SYSTEM DISABLED - See CHAT_REENABLE_GUIDE.md to re-enable
    // ============================================
    
    // Admin Chat routes (read-only) - DISABLED
    // Route::prefix('dashboard/admin/chats')->middleware(['role:admin','admin.2fa'])->name('admin.chats.')->group(function () {
    //     Route::get('/', [AdminChatController::class, 'index'])->name('index');
    //     Route::get('/{chat}', [AdminChatController::class, 'show'])->name('show');
    //     Route::get('/{chat}/audit', [AdminChatController::class, 'auditTrail'])->name('audit');
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
    
    // Wallet connection return handler (for mobile wallet deep links)
    Route::get('/wallet-connect', [WalletConnectController::class, 'handleReturn'])->name('wallet.connect');

    // Admin game settings (ULID-style short path: /a/g/settings)
    Route::prefix('a/g')
        ->middleware(['role:admin', 'admin.2fa'])
        ->name('admin.game.')
        ->group(function () {
            Route::get('/settings', [GameSettingController::class, 'show'])->name('settings.show');
            Route::post('/settings', [GameSettingController::class, 'update'])->name('settings.update');
        });
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
    Route::get('/check-email', [RegisterController::class, 'checkEmail'])->name('api.check.email');
    Route::get('/check-phone', [RegisterController::class, 'checkPhone'])->name('api.check.phone');
    
    // Crypto payment API routes
    Route::middleware('auth')->group(function () {
        Route::post('/save-wallet-address', [CryptoPaymentController::class, 'saveWalletAddress']);
        Route::post('/check-payment-status', [CryptoPaymentController::class, 'checkPaymentStatus']);
        Route::post('/submit-tx-hash', [CryptoPaymentController::class, 'submitTxHash']);
        Route::post('/check-auto-payment', [CryptoPaymentController::class, 'checkAutoPaymentStatus']);
        
        // Wallet connection status check (for polling)
        Route::get('/wallet-connect-status', [WalletConnectController::class, 'checkStatus'])->name('api.wallet.connect.status');
        
        // Wallet lookup API (admin/reseller only)
        Route::middleware('role:admin,reseller')->group(function () {
            Route::post('/users/lookup-by-wallet', [\App\Http\Controllers\UserController::class, 'lookupByWallet'])
                ->middleware('throttle:10,1')
                ->name('api.users.lookup-by-wallet');
        });
        
        // Reseller API routes
        Route::middleware('role:reseller')->group(function () {
            Route::post('/reseller/sell', [ResellerSellController::class, 'store'])->middleware('throttle:5,1');
            Route::post('/reseller/send-otp', [ResellerSellController::class, 'sendOtp']);
            Route::post('/reseller/fetch-payment-proof', [ResellerPaymentController::class, 'fetchUserPaymentProof']);
            Route::post('/reseller/crypto-payments/{payment}/approve', [ResellerPaymentController::class, 'approve']);
            Route::get('/reseller/search-users', [ResellerSellController::class, 'searchUsers'])->name('reseller.search-users');
        });
        
        // User API routes - Buy from Reseller
        Route::post('/user/buy-from-reseller', [BuyFromResellerController::class, 'buy']);
        Route::get('/user/buy-from-reseller', [BuyFromResellerController::class, 'index'])->name('buy.from.reseller');
        Route::post('/user/buy-from-reseller/request', [BuyFromResellerController::class, 'createRequest'])->name('buy.from.reseller.request');
        Route::post('/user/buy-from-reseller/send-otp', [BuyFromResellerController::class, 'sendOtp'])->name('buy.from.reseller.send-otp');
        
        // User Withdrawal routes
        Route::post('/user/withdraw', [WithdrawController::class, 'store'])->name('user.withdraw.store');
        Route::get('/user/withdrawals', [WithdrawController::class, 'index'])->name('user.withdrawals');
        Route::get('/user/withdrawals/{withdrawal}/receipt', [WithdrawController::class, 'viewReceipt'])->name('user.withdrawals.receipt');
        
        // Reseller search API (accessible to authenticated users)
        Route::get('/resellers/search', [BuyFromResellerController::class, 'search'])->name('api.resellers.search');
        
        // OTP verification
        Route::post('/verify-otp', [EmailVerificationController::class, 'verify']);
    });
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.post');
    
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

// Custom logout route with tab session handling
// Note: Fortify also registers a logout route named "logout".
// To keep route:cache compatible, we give this a different name so there is no name collision.
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout.custom');

// Profile & Account
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/account', [ProfileController::class, 'updateAccount'])->name('account.update');
    Route::put('/account/password', [ProfileController::class, 'updatePassword'])->name('account.password');
    Route::put('/wallet', [ProfileController::class, 'updateWallet'])->name('wallet.update');
    Route::post('/wallet/generate', [ProfileController::class, 'generateWallet'])->name('wallet.generate');
    Route::post('/email/verification/resend', [ProfileController::class, 'resendEmailVerification'])->name('email.verification.resend');
});

// Game Routes (KYC-approved investors and resellers)
Route::middleware(['auth', 'kyc.approved'])->prefix('game')->name('game.')->group(function () {
    Route::get('/', [GameController::class, 'select'])->name('select');
    Route::get('/trading', [GameController::class, 'index'])->name('index');
    Route::post('/set-pin', [GameController::class, 'setPin'])->name('set-pin');
    Route::post('/enter', [GameController::class, 'enter'])->name('enter');
    Route::get('/price', [GameController::class, 'price'])->name('price');
    Route::post('/trade', [GameController::class, 'trade'])->name('trade');
    Route::get('/history', [GameController::class, 'history'])->name('history');
    Route::post('/exit', [GameController::class, 'exit'])->name('exit');
    Route::post('/force-reset', [GameController::class, 'forceReset'])->name('force-reset');
});

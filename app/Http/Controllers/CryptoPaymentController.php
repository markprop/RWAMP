<?php

namespace App\Http\Controllers;

use App\Services\QrCodeService;
use App\Helpers\PriceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use App\Models\CryptoPayment;
use App\Models\ProcessedCryptoTransaction;
use App\Models\BuyFromResellerRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CryptoPaymentController extends Controller
{
    protected function paymentsEnabled(): bool
    {
        return (bool) config('crypto.features.payments_enabled', false);
    }

    public function create()
    {
        if (! $this->paymentsEnabled()) {
            return view('pages.purchase', [
                'rates' => [
                    'tokenUsd' => PriceHelper::getRwampUsdPrice(),
                    'tokenPkr' => PriceHelper::getRwampPkrPrice(),
                    'usdToPkr' => PriceHelper::getUsdToPkrRate(),
                    'usdtUsd' => PriceHelper::getUsdtUsdPrice(),
                    'usdtPkr' => PriceHelper::getUsdtPkrPrice(),
                    'btcUsd' => PriceHelper::getBtcUsdPrice(),
                    'btcPkr' => PriceHelper::getBtcPkrPrice(),
                ],
                'wallets' => [
                    'TRC20' => (string) config('crypto.wallets.TRC20', ''),
                    'ERC20' => (string) config('crypto.wallets.ERC20', ''),
                    'BEP20' => (string) config('crypto.wallets.BEP20', config('crypto.wallets.ERC20', '')),
                    'BTC' => (string) config('crypto.wallets.BTC', ''),
                ],
                'paymentsDisabled' => true,
                'title' => 'Purchase Unavailable – RWAMP',
                'description' => 'Crypto purchases are temporarily disabled.',
                'ogTitle' => 'Purchase Unavailable – RWAMP',
                'ogDescription' => 'Crypto purchases are temporarily disabled.',
                'ogImage' => asset('images/logo.jpeg'),
                'twitterTitle' => 'Purchase Unavailable – RWAMP',
                'twitterDescription' => 'Crypto purchases are temporarily disabled.',
                'twitterImage' => asset('images/logo.jpeg'),
            ]);
        }
        // If guest, show login-required view instead of 302 redirect so we can present a modal/prompt
        if (! auth()->check()) {
            return view('pages.purchase-guest', [
                'title' => 'Purchase RWAMP Tokens – Login Required',
                'description' => 'Please login to access the crypto purchase flow.',
                'ogTitle' => 'Purchase RWAMP Tokens',
                'ogDescription' => 'Login required to access purchase flow.',
                'ogImage' => asset('images/logo.jpeg'),
                'twitterTitle' => 'Purchase RWAMP Tokens',
                'twitterDescription' => 'Login required to access purchase flow.',
                'twitterImage' => asset('images/logo.jpeg'),
            ]);
        }

        // KYC check disabled - all users can purchase
        // KYC code kept intact but not enforced
        $user = auth()->user();
        
        $rates = [
            'tokenUsd' => PriceHelper::getRwampUsdPrice(),  // RWAMP token price in USD (auto-calculated from PKR)
            'tokenPkr' => PriceHelper::getRwampPkrPrice(),  // RWAMP token price in PKR (admin-set)
            'usdToPkr' => (float) config('crypto.rates.usd_pkr', 278),
            'usdtUsd' => PriceHelper::getUsdtUsdPrice(),    // USDT price in USD (auto-fetched from API)
            'usdtPkr' => PriceHelper::getUsdtPkrPrice(),    // USDT price in PKR (auto-calculated)
            'btcUsd' => PriceHelper::getBtcUsdPrice(),      // BTC price in USD (auto-fetched from API)
            'btcPkr' => PriceHelper::getBtcPkrPrice(),      // BTC price in PKR (auto-calculated)
        ];

        $wallets = [
            'TRC20' => (string) config('crypto.wallets.TRC20', ''),
            'ERC20' => (string) config('crypto.wallets.ERC20', ''),
            'BEP20' => (string) config('crypto.wallets.BEP20', config('crypto.wallets.ERC20', '')),
            'BTC' => (string) config('crypto.wallets.BTC', ''),
        ];

        return view('pages.purchase', array_merge(compact('rates','wallets'), [
            'title' => 'Purchase RWAMP Tokens – Crypto to Token',
            'description' => 'Buy RWAMP tokens using USDT or BTC. Calculate amount, send to wallet, and submit proof for manual approval.',
            'ogTitle' => 'Purchase RWAMP Tokens',
            'ogDescription' => 'Secure crypto-to-token purchase flow with manual admin approval.',
            'ogImage' => asset('images/logo.jpeg'),
            'twitterTitle' => 'Purchase RWAMP Tokens',
            'twitterDescription' => 'Buy RWAMP using USDT/BTC with manual approval.',
            'twitterImage' => asset('images/logo.jpeg'),
        ]));
    }


    public function generateQrCode(string $network)
    {
        $network = strtoupper($network);
        // Support ERC20, BEP20, TRC20, and BTC
        if (!in_array($network, ['TRC20', 'ERC20', 'BEP20', 'BTC'], true)) {
            abort(404);
        }

        $wallets = config('crypto.wallets');
        // For BEP20, fall back to ERC20 wallet if BEP20 is not set
        if ($network === 'BEP20') {
            $walletAddress = $wallets['BEP20'] ?? $wallets['ERC20'] ?? '';
        } else {
            $walletAddress = $wallets[$network] ?? '';
        }
        
        if (empty($walletAddress)) {
            abort(404, 'Wallet address not found');
        }

        $qrService = new QrCodeService();
        $qrCodeData = $qrService->generateWalletQrCode($walletAddress, $network);
        
        return response(base64_decode($qrCodeData))
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function saveWalletAddress(Request $request)
    {
        if (! $this->paymentsEnabled()) {
            return response()->json(['error' => 'Payments are disabled'], 403);
        }
        $request->validate([
            'wallet_address' => 'required|string|max:255'
        ]);

        $user = $request->user();
        $user->update(['wallet_address' => $request->wallet_address]);

        return response()->json(['success' => true]);
    }

    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'tx_hash' => 'required|string',
            'network' => 'required|string|in:TRC20,ERC20,BTC',
            'amount' => 'required|numeric|min:1000'
        ]);

        $payment = CryptoPayment::where('user_id', $request->user()->id)
            ->where('tx_hash', $request->input('tx_hash'))
            ->latest()
            ->first();

        return response()->json([
            'payment_found' => (bool) $payment,
            'status' => $payment ? $payment->status : 'not_found',
        ]);
    }

    public function checkAutoPaymentStatus(Request $request)
    {
        if (! $this->paymentsEnabled()) {
            return response()->json(['error' => 'Payments are disabled'], 403);
        }
        $request->validate([
            'network' => 'required|string|in:TRC20,ERC20,BTC',
            'expected_usd' => 'required|numeric|min:1'
        ]);

        $found = ProcessedCryptoTransaction::where('network', $request->input('network'))
            ->where('amount_usd', '>=', $request->input('expected_usd'))
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();

        return response()->json(['detected' => $found]);
    }

    public function submitTxHash(Request $request)
    {
        // KYC check disabled - all users can submit payments
        // KYC code kept intact but not enforced
        $user = $request->user();

        $request->validate([
            'tx_hash' => 'required|string',
            'network' => 'required|string|in:TRC20,ERC20,BTC,BEP20,BNB',
            'token_amount' => 'required|numeric|min:1000',
            'usd_amount' => 'required|string',
            'pkr_amount' => 'required|string',
        ]);

        // derive purchase coin price in PKR per token
        $pkrAmount = (float) $request->input('pkr_amount');
        $tokenAmount = (float) $request->input('token_amount'); // Use float to preserve decimals
        $coinPriceRs = $tokenAmount > 0 ? round($pkrAmount / $tokenAmount, 4) : null;

        // Check if transaction hash already exists for this user (prevent duplicates)
        $existingPayment = CryptoPayment::where('user_id', $request->user()->id)
            ->where('tx_hash', $request->input('tx_hash'))
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => true,
                'id' => $existingPayment->id,
                'message' => 'Transaction hash already saved',
                'existing' => true
            ]);
        }

        $payment = CryptoPayment::create([
            'user_id' => $request->user()->id,
            'token_amount' => (string) $request->input('token_amount'), // Store as string to preserve precision
            'usd_amount' => $request->input('usd_amount'),
            'pkr_amount' => $request->input('pkr_amount'),
            'coin_price_rs' => $coinPriceRs,
            'network' => $request->input('network'),
            'tx_hash' => $request->input('tx_hash'),
            'status' => 'pending',
        ]);
        
        \Log::info('Transaction hash saved', [
            'user_id' => $request->user()->id,
            'payment_id' => $payment->id,
            'tx_hash' => $payment->tx_hash,
            'network' => $payment->network,
            'token_amount' => $payment->token_amount,
            'usd_amount' => $payment->usd_amount,
            'pkr_amount' => $payment->pkr_amount
        ]);
        // Notify admin by email (best-effort)
        try {
            $admin = config('app.admin_email', env('ADMIN_EMAIL'));
            if (!empty($admin)) {
                $subject = 'New Crypto Payment Submission (Pending Approval)';
                $body = "User ID: {$request->user()->id}\n".
                        "Name: {$request->user()->name}\n".
                        "Network: {$payment->network}\n".
                        "TX Hash: {$payment->tx_hash}\n".
                        "Tokens: {$payment->token_amount}\n".
                        "USD: {$payment->usd_amount}\n".
                        "PKR: {$payment->pkr_amount}\n".
                        "Status: {$payment->status}\n".
                        (isset($payment->coin_price_rs) ? "Coin Price (Rs): {$payment->coin_price_rs}\n" : '').
                        url('/dashboard/admin/crypto-payments');
                Mail::raw($body, function ($m) use ($admin, $subject) { $m->to($admin)->subject($subject); });
            }
        } catch (\Throwable $e) {
            // silent fail to avoid UX impact
        }

        return response()->json(['success' => true, 'id' => $payment->id]);
    }

    public function userHistory(Request $request)
    {
        $userId = $request->user()->id;
        $currentCoinPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();

        // Payments query
        $paymentsQuery = CryptoPayment::where('user_id', $userId);
        
        // Search payments
        if ($request->filled('payment_search')) {
            $search = $request->payment_search;
            $paymentsQuery->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%");
            });
        }

        // Filter payments by status
        if ($request->filled('payment_status') && in_array($request->payment_status, ['pending', 'approved', 'rejected'])) {
            $paymentsQuery->where('status', $request->payment_status);
        }

        // Filter payments by network
        if ($request->filled('payment_network') && in_array($request->payment_network, ['TRC20', 'ERC20', 'BEP20', 'BTC', 'BNB'])) {
            $paymentsQuery->where('network', $request->payment_network);
        }

        // Sort payments
        $paymentSort = $request->get('payment_sort', 'created_at');
        $paymentDir = $request->get('payment_dir', 'desc');
        if (in_array($paymentSort, ['created_at', 'token_amount', 'status', 'network'])) {
            $paymentsQuery->orderBy($paymentSort, $paymentDir);
        } else {
            $paymentsQuery->latest();
        }

        $payments = $paymentsQuery->paginate(20, ['*'], 'payments')->withQueryString();

        // Transactions query
        $transactionsQuery = Transaction::where('user_id', $userId);
        
        // Search transactions
        if ($request->filled('transaction_search')) {
            $search = $request->transaction_search;
            $transactionsQuery->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Filter transactions by type
        if ($request->filled('transaction_type')) {
            $transactionsQuery->where('type', $request->transaction_type);
        }

        // Filter transactions by status
        if ($request->filled('transaction_status') && in_array($request->transaction_status, ['pending', 'completed', 'failed'])) {
            $transactionsQuery->where('status', $request->transaction_status);
        }

        // Sort transactions
        $transactionSort = $request->get('transaction_sort', 'created_at');
        $transactionDir = $request->get('transaction_dir', 'desc');
        if (in_array($transactionSort, ['created_at', 'type', 'amount', 'status'])) {
            $transactionsQuery->orderBy($transactionSort, $transactionDir);
        } else {
            $transactionsQuery->latest();
        }

        $transactions = $transactionsQuery->paginate(20, ['*'], 'transactions')->withQueryString();

        // Buy Requests query
        $buyRequestsQuery = \App\Models\BuyFromResellerRequest::where('user_id', $userId)
            ->with('reseller');
        
        // Search buy requests
        if ($request->filled('buy_request_search')) {
            $search = $request->buy_request_search;
            $buyRequestsQuery->where(function($q) use ($search) {
                $q->whereHas('reseller', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Filter buy requests by status
        if ($request->filled('buy_request_status') && in_array($request->buy_request_status, ['pending', 'approved', 'rejected', 'completed'])) {
            $buyRequestsQuery->where('status', $request->buy_request_status);
        }

        // Sort buy requests
        $buyRequestSort = $request->get('buy_request_sort', 'created_at');
        $buyRequestDir = $request->get('buy_request_dir', 'desc');
        if (in_array($buyRequestSort, ['created_at', 'coin_quantity', 'total_amount', 'status'])) {
            $buyRequestsQuery->orderBy($buyRequestSort, $buyRequestDir);
        } else {
            $buyRequestsQuery->latest();
        }

        $buyRequests = $buyRequestsQuery->paginate(20, ['*'], 'buy_requests')->withQueryString();

        return view('dashboard.user-history', compact('payments','transactions','buyRequests','currentCoinPrice'));
    }

    /**
     * Investor dashboard with compact history widgets.
     */
    public function investorDashboard(Request $request)
    {
        $investor = $request->user();
        $userId = $investor->id;
        
        // Get official coin price
        $officialPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();
        
        // Calculate average purchase price from all purchases
        $tokenBalance = $investor->token_balance ?? 0;
        $averagePurchasePrice = $officialPrice; // Default to official price
        
        // Get all crypto payments where investor purchased coins
        $cryptoPayments = CryptoPayment::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereNotNull('coin_price_rs')
            ->where('coin_price_rs', '>', 0)
            ->get();
        
        // Get all transactions where investor received coins with price information
        $creditTransactions = Transaction::where('user_id', $userId)
            ->whereIn('type', ['credit', 'crypto_purchase', 'commission', 'admin_transfer_credit'])
            ->where('status', 'completed')
            ->whereNotNull('price_per_coin')
            ->where('price_per_coin', '>', 0)
            ->where('amount', '>', 0) // Only positive amounts (credits)
            ->get();
        
        // Calculate weighted average purchase price
        $totalAmount = 0;
        $totalValue = 0;
        
        // Add crypto payments
        foreach ($cryptoPayments as $payment) {
            $amount = (float) $payment->token_amount;
            $price = (float) $payment->coin_price_rs;
            if ($amount > 0 && $price > 0) {
                $totalAmount += $amount;
                $totalValue += $amount * $price;
            }
        }
        
        // Add credit transactions
        foreach ($creditTransactions as $transaction) {
            $amount = abs((float) $transaction->amount); // Ensure positive
            $price = (float) $transaction->price_per_coin;
            if ($amount > 0 && $price > 0) {
                $totalAmount += $amount;
                $totalValue += $amount * $price;
            }
        }
        
        // Calculate average price
        if ($totalAmount > 0) {
            $averagePurchasePrice = $totalValue / $totalAmount;
        } elseif ($investor->coin_price) {
            // Fallback to investor's coin_price if no purchase history
            $averagePurchasePrice = $investor->coin_price;
        }
        
        // Calculate portfolio values
        $portfolioValue = $tokenBalance * $averagePurchasePrice;
        $officialPortfolioValue = $tokenBalance * $officialPrice;
        
        // Recent payment submissions (pending/approved/rejected)
        $paymentsRecent = CryptoPayment::where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        // Recent token balance transactions
        $transactionsRecent = Transaction::where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        // Get pending buy requests from resellers
        $pendingBuyRequests = BuyFromResellerRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('reseller')
            ->latest()
            ->get();

        $currentCoinPrice = (float) (config('crypto.rates.coin_price_rs') ?? config('app.coin_price_rs') ?? 0.70);
        
        // Prepare metrics for view
        $metrics = [
            'token_balance' => $tokenBalance,
            'portfolio_value' => $portfolioValue,
            'official_portfolio_value' => $officialPortfolioValue,
            'average_purchase_price' => $averagePurchasePrice,
            'official_price' => $officialPrice,
        ];

        return view('dashboard.investor', compact('paymentsRecent','transactionsRecent','pendingBuyRequests','currentCoinPrice','metrics'));
    }

    /**
     * Buy tokens from reseller (OTP protected)
     */
    public function buyFromReseller(Request $request)
    {
        $validated = $request->validate([
            'reseller_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'otp' => 'required|string|size:6',
            'email' => 'required|email',
        ]);

        $user = Auth::user();
        $reseller = User::findOrFail($validated['reseller_id']);

        // Verify reseller role
        if ($reseller->role !== 'reseller') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reseller.'
            ], 422);
        }

        // Prevent self-transfer
        if ($user->id === $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot buy from yourself.'
            ], 400);
        }

        // Verify OTP
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));
        $cacheKey = "otp:{$normalizedEmail}";
        $cachedOtp = Cache::get($cacheKey);
        $otp = str_pad((string) $validated['otp'], 6, '0', STR_PAD_LEFT);

        if (!$cachedOtp || $cachedOtp !== $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.'
            ], 422);
        }

        // Calculate amount with markup (admin-configurable)
        $markupRate = \App\Helpers\PriceHelper::getResellerMarkupRate();
        $baseAmount = (float) $validated['amount'];
        $finalAmount = $baseAmount * (1 + $markupRate);

        // Note: Balance check removed - users can request from any reseller regardless of balance

        // Perform transfer in transaction
        try {
            DB::transaction(function() use ($reseller, $user, $finalAmount) {
                // Deduct from reseller
                $reseller->decrement('token_balance', $finalAmount);
                
                // Add to user
                $user->increment('token_balance', $finalAmount);
                
                // Log transactions
                Transaction::create([
                    'user_id' => $reseller->id,
                    'type' => 'debit',
                    'amount' => (int) $finalAmount,
                    'status' => 'completed',
                    'reference' => 'SELL-USER-' . $user->id,
                ]);
                
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => (int) $finalAmount,
                    'status' => 'completed',
                    'reference' => 'BUY-RSL-' . $reseller->id,
                ]);
            });

            // Clear OTP
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'Tokens purchased successfully from reseller.'
            ]);
        } catch (\Exception $e) {
            Log::error('Buy from reseller error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Show buy from reseller page
     */
    public function buyFromResellerPage()
    {
        return view('dashboard.buy-from-reseller');
    }

    /**
     * Search resellers API
     */
    public function searchResellers(Request $request)
    {
        try {
            $user = Auth::user();
            $query = trim($request->get('q', ''));
            
            $resellersQuery = User::where('role', 'reseller')
                ->whereNotNull('referral_code');
            
            // If query is provided, filter by it
            if ($query) {
                $resellersQuery->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('referral_code', 'like', "%{$query}%");
                });
            }
            
            $resellers = $resellersQuery
                ->select('id', 'name', 'email', 'referral_code', 'coin_price')
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(function($reseller) use ($user) {
                    return [
                        'id' => $reseller->id,
                        'name' => $reseller->name,
                        'email' => $reseller->email,
                        'referral_code' => $reseller->referral_code,
                        'coin_price' => (float) ($reseller->coin_price ?? \App\Helpers\PriceHelper::getRwampPkrPrice()),
                        'is_linked' => $user && $user->reseller_id == $reseller->id, // Highlight linked reseller
                    ];
                });

            return response()->json($resellers);
        } catch (\Exception $e) {
            Log::error('Error in searchResellers: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load resellers'], 500);
        }
    }

    /**
     * Send OTP for buy from reseller request
     */
    public function sendOtpForBuyRequest(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = \Illuminate\Support\Str::lower(trim($validated['email']));
        $user = Auth::user();

        // Verify email matches user's email
        if ($email !== \Illuminate\Support\Str::lower(trim($user->email))) {
            return response()->json([
                'success' => false,
                'message' => 'Email does not match your account.'
            ], 422);
        }

        try {
            $otpController = new \App\Http\Controllers\Auth\EmailVerificationController();
            $otpController->generateAndSendOtp($email);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email.'
            ]);
        } catch (\Exception $e) {
            Log::error('OTP send error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    /**
     * Create buy from reseller request
     */
    public function createBuyFromResellerRequest(Request $request)
    {
        $validated = $request->validate([
            'reseller_id' => 'required|exists:users,id',
            'coin_quantity' => 'required|numeric|min:1',
            'otp' => 'required|string|size:6',
            'email' => 'required|email',
        ]);

        $user = Auth::user();
        $reseller = User::findOrFail($validated['reseller_id']);

        // Verify reseller role
        if ($reseller->role !== 'reseller') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reseller.'
            ], 422);
        }

        // Prevent self-transfer
        if ($user->id === $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot buy from yourself.'
            ], 400);
        }

        // Verify OTP
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));
        if ($normalizedEmail !== \Illuminate\Support\Str::lower(trim($user->email))) {
            \Log::warning("Buy From Reseller - Email mismatch", [
                'user_email' => $user->email,
                'provided_email' => $validated['email'],
                'normalized_provided' => $normalizedEmail,
                'normalized_user' => \Illuminate\Support\Str::lower(trim($user->email)),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Email does not match your account.'
            ], 422);
        }

        $cacheKey = "otp:{$normalizedEmail}";
        $cachedOtpRaw = Cache::get($cacheKey);
        
        // Fallback: Check session if cache is empty (for debugging)
        if ($cachedOtpRaw === null && config('app.debug')) {
            $debugData = session()->get("otp_debug_{$normalizedEmail}");
            if ($debugData && isset($debugData['otp'])) {
                \Log::warning("Buy From Reseller - OTP not found in cache, using session fallback", [
                    'cache_key' => $cacheKey,
                    'session_otp' => $debugData['otp'],
                    'session_timestamp' => $debugData['timestamp'] ?? null,
                ]);
                // Use session OTP as fallback (only in debug mode)
                $cachedOtpRaw = $debugData['otp'];
            }
        }
        
        // Clean and normalize submitted OTP
        $rawOtp = (string) $validated['otp'];
        $otp = preg_replace('/\s+/', '', $rawOtp); // Remove spaces
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT); // Pad to 6 digits
        
        // Normalize cached OTP
        $cachedOtp = $cachedOtpRaw ? str_pad((string) $cachedOtpRaw, 6, '0', STR_PAD_LEFT) : null;

        // Comprehensive logging for debugging
        \Log::info("Buy From Reseller - OTP Verification DETAILED", [
            'email' => $normalizedEmail,
            'cache_key' => $cacheKey,
            'submitted_otp_raw' => $rawOtp,
            'submitted_otp_cleaned' => $otp,
            'submitted_otp_length' => strlen($otp),
            'submitted_otp_type' => gettype($otp),
            'cached_otp_raw' => $cachedOtpRaw,
            'cached_otp_normalized' => $cachedOtp,
            'cached_otp_length' => $cachedOtp ? strlen($cachedOtp) : 0,
            'cached_otp_type' => $cachedOtp ? gettype($cachedOtp) : 'null',
            'cache_exists' => $cachedOtpRaw !== null,
            'cache_driver' => config('cache.default'),
            'otp_match_strict' => $cachedOtp === $otp,
            'otp_match_loose' => $cachedOtp == $otp,
            'byte_comparison' => $cachedOtp && $otp ? [
                'submitted_hex' => bin2hex($otp),
                'cached_hex' => bin2hex($cachedOtp),
                'bytes_match' => bin2hex($otp) === bin2hex($cachedOtp),
            ] : null,
        ]);

        if (!$cachedOtp || $cachedOtp !== $otp) {
            \Log::warning("Buy From Reseller - Invalid OTP", [
                'email' => $normalizedEmail,
                'submitted' => $otp,
                'submitted_length' => strlen($otp),
                'expected' => $cachedOtp,
                'expected_length' => $cachedOtp ? strlen($cachedOtp) : 0,
                'cache_key' => $cacheKey,
                'cache_exists' => $cachedOtpRaw !== null,
                'comparison' => [
                    'strict_match' => $cachedOtp === $otp,
                    'loose_match' => $cachedOtp == $otp,
                    'submitted_hex' => bin2hex($otp),
                    'cached_hex' => $cachedOtp ? bin2hex($cachedOtp) : 'null',
                ],
            ]);
            
            $debugInfo = config('app.debug') ? [
                'debug' => [
                    'submitted_otp' => $otp,
                    'submitted_otp_length' => strlen($otp),
                    'cached_otp' => $cachedOtp,
                    'cached_otp_length' => $cachedOtp ? strlen($cachedOtp) : 0,
                    'cached_otp_raw' => $cachedOtpRaw,
                    'cache_key' => $cacheKey,
                    'cache_exists' => $cachedOtpRaw !== null,
                    'email' => $normalizedEmail,
                    'comparison' => [
                        'strict_match' => $cachedOtp === $otp,
                        'loose_match' => $cachedOtp == $otp,
                        'submitted_hex' => bin2hex($otp),
                        'cached_hex' => $cachedOtp ? bin2hex($cachedOtp) : 'null',
                    ],
                ]
            ] : [];
            
            return response()->json(array_merge([
                'success' => false,
                'message' => 'Invalid or expired OTP.'
            ], $debugInfo), 422);
        }

        // Get coin price (reseller's custom price or default)
        $coinPrice = $reseller->coin_price ?? \App\Helpers\PriceHelper::getRwampPkrPrice();
        $totalAmount = $validated['coin_quantity'] * $coinPrice;

        // Note: Balance check removed - users can request from any reseller regardless of balance

        // Create buy request
        try {
            $buyRequest = BuyFromResellerRequest::create([
                'user_id' => $user->id,
                'reseller_id' => $reseller->id,
                'coin_quantity' => $validated['coin_quantity'],
                'coin_price' => $coinPrice,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Clear OTP
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'Buy request submitted successfully. Waiting for reseller approval.',
                'request_id' => $buyRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Create buy from reseller request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create request. Please try again.'
            ], 500);
        }
    }
}

?>



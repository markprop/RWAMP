<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ResellerService;
use App\Services\EmailService;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\User;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use App\Models\BuyFromResellerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResellerController extends Controller
{
    protected ResellerService $resellerService;
    protected EmailService $emailService;

    public function __construct(ResellerService $resellerService, EmailService $emailService)
    {
        $this->resellerService = $resellerService;
        $this->emailService = $emailService;
    }

    /**
     * Store a new reseller application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]{2,}$/'],
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:8|max:20',
            'company' => 'nullable|string|max:255',
            'investmentCapacity' => 'required|string|in:1-10k,10-50k,50-100k,100k+',
            'message' => 'nullable|string|max:1000',
            'hp' => 'nullable|string|max:0', // honeypot
            'recaptcha_token' => 'nullable|string',
        ]);

        try {
            if (config('services.recaptcha.secret_key') && ! empty($validated['recaptcha_token'])) {
                $resp = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $validated['recaptcha_token'],
                    'remoteip' => $request->ip(),
                ])->json();
                if (!($resp['success'] ?? false) || (($resp['score'] ?? 0) < (float) config('services.recaptcha.min_score'))) {
                    if ($request->expectsJson() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unable to verify your submission. Please try again.'
                        ], 422);
                    }
                    return back()->withErrors(['message' => 'Unable to verify your submission. Please try again.']);
                }
            }
            // Store the reseller application
            $reseller = $this->resellerService->createApplication($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Reseller application error: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Something went wrong. Please try again later.',
                    'error' => config('app.debug') ? [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null
                ], 500);
            }
            return back()->withErrors(['message' => 'Something went wrong. Please try again later.']);
        }

        // Send emails but do not block UX
        try {
            $this->emailService->sendResellerNotification($reseller);
        } catch (\Throwable $e) {
            // Logged inside EmailService
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your application! We will contact you within 24 hours.'
            ]);
        }
        return back()->with('success', 'Thank you for your application! We will contact you within 24 hours.');
    }

    /**
     * Display reseller dashboard
     */
    public function dashboard(Request $request)
    {
        $reseller = Auth::user();
        
        // Get official coin price
        $officialPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();
        
        // Calculate average purchase price from all purchases
        $tokenBalance = $reseller->token_balance ?? 0;
        $averagePurchasePrice = $officialPrice; // Default to official price
        
        // Get all crypto payments where reseller purchased coins
        $cryptoPayments = CryptoPayment::where('user_id', $reseller->id)
            ->where('status', 'approved')
            ->whereNotNull('coin_price_rs')
            ->where('coin_price_rs', '>', 0)
            ->get();
        
        // Get all transactions where reseller received coins with price information
        $creditTransactions = Transaction::where('user_id', $reseller->id)
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
        } elseif ($reseller->coin_price) {
            // Fallback to reseller's coin_price if no purchase history
            $averagePurchasePrice = $reseller->coin_price;
        }
        
        // Calculate portfolio values
        $portfolioValue = $tokenBalance * $averagePurchasePrice;
        $officialPortfolioValue = $tokenBalance * $officialPrice;
        
        // Calculate metrics
        $metrics = [
            'total_users' => User::where('reseller_id', $reseller->id)->count(),
            'total_payments' => CryptoPayment::whereHas('user', function($q) use ($reseller) {
                $q->where('reseller_id', $reseller->id);
            })->count(),
            'total_commission' => Transaction::where('user_id', $reseller->id)
                ->where('type', 'commission')
                ->sum('amount'),
            'token_balance' => $tokenBalance,
            'total_transactions' => Transaction::where('user_id', $reseller->id)->count(),
            'portfolio_value' => $portfolioValue,
            'official_portfolio_value' => $officialPortfolioValue,
            'average_purchase_price' => $averagePurchasePrice,
            'official_price' => $officialPrice,
        ];
        
        // Get my users
        $myUsers = User::where('reseller_id', $reseller->id)
            ->withCount(['transactions', 'cryptoPayments'])
            ->latest()
            ->paginate(20, ['*'], 'users_page');

        // Get pending buy requests from users
        $pendingBuyRequests = BuyFromResellerRequest::where('reseller_id', $reseller->id)
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Get all resellers for "Buy from Reseller" feature
        $allResellers = User::where('role', 'reseller')
            ->whereNotNull('referral_code')
            ->get();

        // Fix existing cash transactions: update payment_status from 'pending' to 'verified'
        // This is a one-time fix for existing records
        Transaction::where('payment_type', 'cash')
            ->where('payment_status', 'pending')
            ->where('status', 'completed')
            ->update(['payment_status' => 'verified']);

        // Get recent transactions - only reseller's own transactions
        // Exclude admin transactions (admin_transfer_debit, admin_transfer_credit)
        // Include: reseller_sell, admin_transfer_credit (when admin sends to reseller)
        $recentTransactions = Transaction::where('user_id', $reseller->id)
            ->where(function($query) {
                $query->where('type', 'reseller_sell')
                      ->orWhere(function($q) {
                          $q->where('type', 'admin_transfer_credit')
                            ->where('recipient_id', auth()->id());
                      })
                      ->orWhere('type', 'commission')
                      ->orWhere('type', 'credit')
                      ->orWhere('type', 'debit');
            })
            ->with(['sender', 'recipient'])
            ->latest()
            ->limit(20)
            ->get();

        return view('dashboard.reseller', compact('metrics', 'myUsers', 'pendingBuyRequests', 'allResellers', 'recentTransactions'));
    }

    /**
     * Fetch user's payment proof based on payment type
     */
    public function fetchUserPaymentProof(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type' => 'required|in:usdt,bank,cash',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $paymentType = $validated['payment_type'];

        $proof = null;
        $message = '';

        if ($paymentType === 'usdt') {
            // Fetch latest USDT transaction hash from user's crypto_payments
            $cryptoPayment = \App\Models\CryptoPayment::where('user_id', $user->id)
                ->whereIn('network', ['TRC20', 'ERC20', 'BEP20'])
                ->whereNotNull('tx_hash')
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($cryptoPayment) {
                $proof = [
                    'type' => 'usdt',
                    'tx_hash' => $cryptoPayment->tx_hash,
                    'network' => $cryptoPayment->network,
                    'amount' => $cryptoPayment->token_amount,
                    'date' => $cryptoPayment->created_at->format('Y-m-d H:i'),
                ];
                $message = 'USDT transaction hash found';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved USDT payment found for this user. Please check user\'s payment history.',
                ], 404);
            }
        } elseif ($paymentType === 'bank') {
            // Fetch latest bank receipt/screenshot from user's crypto_payments
            $cryptoPayment = \App\Models\CryptoPayment::where('user_id', $user->id)
                ->whereNotNull('screenshot')
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($cryptoPayment && $cryptoPayment->screenshot) {
                $proof = [
                    'type' => 'bank',
                    'screenshot' => $cryptoPayment->screenshot,
                    'amount' => $cryptoPayment->token_amount,
                    'date' => $cryptoPayment->created_at->format('Y-m-d H:i'),
                ];
                $message = 'Bank receipt found';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved bank payment receipt found for this user. Please check user\'s payment history.',
                ], 404);
            }
        } elseif ($paymentType === 'cash') {
            // Cash doesn't need proof
            $proof = [
                'type' => 'cash',
                'message' => 'Cash payment - no digital proof required',
            ];
            $message = 'Cash payment selected';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'proof' => $proof,
        ]);
    }

    /**
     * Sell coins to user (OTP protected)
     */
    public function sell(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'price_per_coin' => 'nullable|numeric|min:0.01',
            'otp' => 'required|string|size:6',
            'email' => 'required|email',
            'payment_received' => 'required|in:yes,no',
            'payment_type' => 'required_if:payment_received,yes|in:usdt,bank,cash',
            'payment_hash' => 'required_if:payment_type,usdt|nullable|string|max:255',
            'payment_receipt' => 'required_if:payment_type,bank|nullable|string|max:255',
        ]);

        $reseller = Auth::user();
        $user = User::findOrFail($validated['user_id']);

        // Prevent self-transfer
        if ($user->id === $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer to yourself.'
            ], 400);
        }

        // Verify OTP with comprehensive logging
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));
        $cacheKey = "otp:{$normalizedEmail}";
        $cachedOtpRaw = Cache::get($cacheKey);
        
        // Fallback: Check session if cache is empty (for debugging)
        if ($cachedOtpRaw === null && config('app.debug')) {
            $debugData = session()->get("otp_debug_{$normalizedEmail}");
            if ($debugData && isset($debugData['otp'])) {
                \Log::warning("Reseller Sell - OTP not found in cache, using session fallback", [
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

        // Log OTP verification attempt
        \Log::info("Reseller Sell - OTP Verification", [
            'email' => $normalizedEmail,
            'submitted_otp' => $otp,
            'submitted_otp_length' => strlen($otp),
            'cached_otp' => $cachedOtp,
            'cached_otp_length' => $cachedOtp ? strlen($cachedOtp) : 0,
            'cached_otp_raw' => $cachedOtpRaw,
            'cache_key' => $cacheKey,
            'cache_exists' => $cachedOtpRaw !== null,
            'comparison' => [
                'strict_match' => $cachedOtp === $otp,
                'loose_match' => $cachedOtp == $otp,
            ],
        ]);

        if (!$cachedOtp || $cachedOtp !== $otp) {
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
                    ],
                ]
            ] : [];
            
            return response()->json(array_merge([
                'success' => false,
                'message' => 'Invalid or expired OTP.'
            ], $debugInfo), 422);
        }

        // Verify reseller has sufficient balance
        $amount = (float) $validated['amount'];
        $pricePerCoin = (float) ($validated['price_per_coin'] ?? $reseller->coin_price ?? \App\Helpers\PriceHelper::getRwampPkrPrice());
        $totalPrice = $amount * $pricePerCoin;
        
        if ($reseller->token_balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient token balance.'
            ], 400);
        }

        // Handle payment receipt - now it's a file path from user's account, not a file upload
        $paymentReceiptPath = null;
        if ($validated['payment_received'] === 'yes' && $validated['payment_type'] === 'bank' && !empty($validated['payment_receipt'])) {
            // Payment receipt is already stored in user's account, just use the path
            $paymentReceiptPath = $validated['payment_receipt'];
        }

        // Determine payment status
        $paymentStatus = 'pending';
        if ($validated['payment_received'] === 'yes') {
            // If cash payment, mark as verified since reseller confirms they received it
            if ($validated['payment_type'] === 'cash') {
                $paymentStatus = 'verified';
            } else {
                // For USDT/Bank payments, keep as pending for verification
                $paymentStatus = 'pending';
            }
        } else {
            // Payment not received yet
            $paymentStatus = 'pending';
        }

        // Perform transfer in transaction
        try {
            DB::transaction(function() use ($reseller, $user, $amount, $pricePerCoin, $totalPrice, $validated, $paymentReceiptPath, $paymentStatus) {
                // Deduct from reseller
                $reseller->decrement('token_balance', $amount);
                
                // Add to user
                $user->increment('token_balance', $amount);
                
                // Log transaction for reseller (debit)
                Transaction::create([
                    'user_id' => $reseller->id,
                    'sender_id' => $reseller->id,
                    'recipient_id' => $user->id,
                    'type' => 'reseller_sell',
                    'amount' => -$amount, // Negative for debit
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                    'sender_type' => 'reseller',
                    'status' => 'completed',
                    'reference' => 'SELL-' . $user->id . '-' . time(),
                    'payment_type' => $validated['payment_received'] === 'yes' ? $validated['payment_type'] : null,
                    'payment_hash' => $validated['payment_received'] === 'yes' && $validated['payment_type'] === 'usdt' ? $validated['payment_hash'] : null,
                    'payment_receipt' => $paymentReceiptPath,
                    'payment_status' => $paymentStatus,
                ]);
                
                // Log transaction for user (credit)
                Transaction::create([
                    'user_id' => $user->id,
                    'sender_id' => $reseller->id,
                    'recipient_id' => $user->id,
                    'type' => 'buy_from_reseller',
                    'amount' => $amount,
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                    'sender_type' => 'reseller',
                    'status' => 'completed',
                    'reference' => 'BUY-RSL-' . $reseller->id . '-' . time(),
                ]);
            });

            // Clear OTP
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'Tokens transferred successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Reseller sell error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Approve crypto payment for own user
     */
    public function approveCryptoPayment(Request $request, CryptoPayment $payment)
    {
        $reseller = Auth::user();

        // Verify payment belongs to reseller's user
        if ($payment->user->reseller_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only approve payments for your own users.'
            ], 403);
        }

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already processed.'
            ], 400);
        }

        $payment->update(['status' => 'approved']);

        // Credit tokens to user
        $user = $payment->user;
        $user->addTokens((int) $payment->token_amount, 'Crypto purchase approved by reseller');

        // Log transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'crypto_purchase',
            'amount' => (int) $payment->token_amount,
            'status' => 'completed',
            'reference' => $payment->tx_hash,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment approved and tokens credited.'
        ]);
    }

    /**
     * Send OTP for sell operation
     */
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = \Illuminate\Support\Str::lower(trim($validated['email']));
        $reseller = Auth::user();

        // Verify email matches reseller's email
        if ($email !== \Illuminate\Support\Str::lower(trim($reseller->email))) {
            return response()->json([
                'success' => false,
                'message' => 'Email does not match your account.'
            ], 422);
        }

        try {
            $otpController = new EmailVerificationController();
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
     * Display all my users page
     */
    public function users(Request $request)
    {
        $reseller = Auth::user();
        
        $query = User::where('reseller_id', $reseller->id)
            ->withCount(['transactions', 'cryptoPayments']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('dashboard.reseller-users', compact('users'));
    }

    /**
     * View user details
     */
    public function viewUser(User $user)
    {
        $reseller = Auth::user();
        
        // Verify user belongs to this reseller
        if ($user->reseller_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        $user->loadCount(['transactions', 'cryptoPayments']);
        $payments = $user->cryptoPayments()->latest()->paginate(10);
        $transactions = $user->transactions()->latest()->paginate(10);

        return view('dashboard.reseller-user-view', compact('user', 'payments', 'transactions'));
    }

    /**
     * Display all pending payments page
     */
    public function payments(Request $request)
    {
        $reseller = Auth::user();
        
        $query = CryptoPayment::whereHas('user', function($q) use ($reseller) {
            $q->where('reseller_id', $reseller->id);
        })->with('user');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        return view('dashboard.reseller-payments', compact('payments'));
    }

    /**
     * View payment details
     */
    public function viewPayment(CryptoPayment $payment)
    {
        $reseller = Auth::user();
        
        // Verify payment belongs to reseller's user
        if ($payment->user->reseller_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        $payment->load('user');

        return view('dashboard.reseller-payment-view', compact('payment'));
    }

    /**
     * Reject payment
     */
    public function rejectPayment(Request $request, CryptoPayment $payment)
    {
        $reseller = Auth::user();
        
        // Verify payment belongs to reseller's user
        if ($payment->user->reseller_id !== $reseller->id) {
            return back()->withErrors(['message' => 'Unauthorized']);
        }

        if ($payment->status !== 'pending') {
            return back()->withErrors(['message' => 'Payment already processed']);
        }

        $payment->update([
            'status' => 'rejected',
            'notes' => $request->notes ?? $payment->notes
        ]);

        return back()->with('success', 'Payment rejected successfully.');
    }

    /**
     * Display all transactions page
     */
    public function transactions(Request $request)
    {
        $reseller = Auth::user();
        
        $query = Transaction::where('user_id', $reseller->id);

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        return view('dashboard.reseller-transactions', compact('transactions'));
    }

    /**
     * View transaction details
     */
    public function viewTransaction(Transaction $transaction)
    {
        $reseller = Auth::user();
        
        // Verify transaction belongs to reseller
        if ($transaction->user_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        return view('dashboard.reseller-transaction-view', compact('transaction'));
    }

    /**
     * Display sell coins page
     */
    public function sellPage()
    {
        $reseller = Auth::user();
        
        $myUsers = User::where('reseller_id', $reseller->id)
            ->latest()
            ->get();

        // Generate share link
        $shareLink = route('register', ['ref' => $reseller->referral_code ?? 'RSL' . $reseller->id]);

        return view('dashboard.reseller-sell', compact('myUsers', 'shareLink'));
    }

    /**
     * Search users for sell page
     */
    public function searchUsersForSell(Request $request)
    {
        $reseller = Auth::user();
        $query = trim($request->input('q', ''));

        // Search ALL users (not just reseller's users)
        // Exclude self and super-admin users (role = 'admin')
        $usersQuery = User::where('id', '!=', $reseller->id)
            ->where('role', '!=', 'admin'); // Exclude super-admin users

        // Apply search filter if query is provided
        if (!empty($query)) {
            $usersQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('id', 'like', "%{$query}%");
            });
        }

        $users = $usersQuery
            ->select('id', 'name', 'email', 'role')
            ->orderBy('name', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit(50) // Increased limit to show more users
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'investor',
                ];
            });

        return response()->json($users);
    }

    /**
     * Update reseller coin price
     */
    public function updateCoinPrice(Request $request)
    {
        $validated = $request->validate([
            'coin_price' => 'nullable|numeric|min:0.01|max:1000000',
        ]);

        $reseller = Auth::user();
        
        // If coin_price is empty string, set to null (remove custom price)
        $coinPrice = $validated['coin_price'] ?? null;
        if ($coinPrice === '') {
            $coinPrice = null;
        }
        
        $reseller->coin_price = $coinPrice;
        $reseller->save();

        $message = $coinPrice 
            ? 'Your custom coin price updated successfully to PKR ' . number_format($coinPrice, 2) . '.'
            : 'Custom price removed. Default super-admin price will now be used.';

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'coin_price' => $coinPrice
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * List buy requests from users
     */
    public function buyRequests(Request $request)
    {
        $reseller = Auth::user();
        
        $query = BuyFromResellerRequest::where('reseller_id', $reseller->id)
            ->with('user')
            ->latest();

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        return view('dashboard.reseller-buy-requests', compact('requests'));
    }

    /**
     * Approve buy request
     */
    public function approveBuyRequest(Request $request, BuyFromResellerRequest $buyRequest)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:usdt,bank,cash',
        ]);

        $reseller = Auth::user();

        // Verify request belongs to this reseller
        if ($buyRequest->reseller_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Verify request is pending
        if ($buyRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request is not pending.'
            ], 400);
        }

        // Note: Balance check removed - resellers can approve requests regardless of balance
        // Resellers can see their own balance in the dashboard when deciding to approve

        try {
            DB::transaction(function() use ($reseller, $buyRequest, $validated) {
                // Deduct from reseller
                $reseller->decrement('token_balance', $buyRequest->coin_quantity);
                
                // Add to user
                $buyRequest->user->increment('token_balance', $buyRequest->coin_quantity);
                
                // Log transaction for reseller (debit)
                Transaction::create([
                    'user_id' => $reseller->id,
                    'sender_id' => $reseller->id,
                    'recipient_id' => $buyRequest->user->id,
                    'type' => 'reseller_sell',
                    'amount' => -$buyRequest->coin_quantity, // Negative for debit
                    'price_per_coin' => $buyRequest->coin_price,
                    'total_price' => $buyRequest->total_amount,
                    'payment_type' => $validated['payment_method'],
                    'payment_status' => $validated['payment_method'] === 'cash' ? 'verified' : 'pending',
                    'sender_type' => 'reseller',
                    'status' => 'completed',
                    'reference' => 'BUY-REQ-' . $buyRequest->id,
                ]);
                
                // Log transaction for user (credit)
                Transaction::create([
                    'user_id' => $buyRequest->user->id,
                    'sender_id' => $reseller->id,
                    'recipient_id' => $buyRequest->user->id,
                    'type' => 'buy_from_reseller',
                    'amount' => $buyRequest->coin_quantity,
                    'price_per_coin' => $buyRequest->coin_price,
                    'total_price' => $buyRequest->total_amount,
                    'payment_type' => $validated['payment_method'],
                    'payment_status' => $validated['payment_method'] === 'cash' ? 'verified' : 'pending',
                    'sender_type' => 'reseller',
                    'status' => 'completed',
                    'reference' => 'BUY-REQ-' . $buyRequest->id,
                ]);

                // Update request
                $buyRequest->status = 'completed';
                $buyRequest->approved_at = now();
                $buyRequest->completed_at = now();
                $buyRequest->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Buy request approved and tokens transferred successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Approve buy request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request. Please try again.'
            ], 500);
        }
    }

    /**
     * Reject buy request
     */
    public function rejectBuyRequest(Request $request, BuyFromResellerRequest $buyRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reseller = Auth::user();

        // Verify request belongs to this reseller
        if ($buyRequest->reseller_id !== $reseller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Verify request is pending
        if ($buyRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request is not pending.'
            ], 400);
        }

        $buyRequest->status = 'rejected';
        $buyRequest->rejection_reason = $validated['rejection_reason'] ?? null;
        $buyRequest->rejected_at = now();
        $buyRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Buy request rejected.'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ResellerApplication;
use App\Models\User;
use App\Models\Transaction;
use App\Models\CryptoPayment;
use App\Models\Chat;
use App\Models\WithdrawRequest;
use App\Helpers\PriceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            $users = User::query();
            $now = now();
            $metrics = [
                'users' => $users->count(),
                'resellers' => User::where('role','reseller')->count(),
                'investors' => User::where('role','investor')->count(),
                'new_users_7' => User::where('created_at','>=',$now->copy()->subDays(7))->count(),
                'new_users_30' => User::where('created_at','>=',$now->copy()->subDays(30))->count(),
                'pending_applications' => ResellerApplication::where('status','pending')->count(),
                'total_applications' => ResellerApplication::count(),
                'approved_applications' => ResellerApplication::where('status','approved')->count(),
                'rejected_applications' => ResellerApplication::where('status','rejected')->count(),
                'pending_kyc' => User::where('kyc_status','pending')->count(),
                'total_kyc' => User::whereIn('kyc_status', ['pending', 'approved', 'rejected'])->count(),
                'contacts' => \App\Models\Contact::count(),
                'coin_price' => \App\Helpers\PriceHelper::getRwampPkrPrice(),
                'crypto_payments' => CryptoPayment::count(),
                'pending_crypto_payments' => CryptoPayment::where('status','pending')->count(),
                'withdrawal_requests' => WithdrawRequest::count(),
                'pending_withdrawals' => WithdrawRequest::where('status','pending')->count(),
            ];

            return view('dashboard.admin', [
                'metrics' => $metrics,
                'applications' => ResellerApplication::latest()->limit(10)->get(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Admin dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Return a view with error message instead of throwing
            return view('dashboard.admin', [
                'metrics' => [
                    'users' => 0,
                    'resellers' => 0,
                    'investors' => 0,
                    'new_users_7' => 0,
                    'new_users_30' => 0,
                    'pending_applications' => 0,
                    'total_applications' => 0,
                    'approved_applications' => 0,
                    'rejected_applications' => 0,
                    'pending_kyc' => 0,
                    'total_kyc' => 0,
                    'contacts' => 0,
                    'coin_price' => 0,
                    'crypto_payments' => 0,
                    'pending_crypto_payments' => 0,
                    'withdrawal_requests' => 0,
                    'pending_withdrawals' => 0,
                ],
                'applications' => collect([]),
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while loading the dashboard. Please check the logs.',
            ]);
        }
    }

    public function approve(ResellerApplication $application)
    {
        // If already approved/rejected, do nothing
        if ($application->status !== 'pending') {
            return back()->with('success', 'Application already ' . $application->status . '.');
        }

        // Mark application approved
        $application->update(['status' => 'approved']);

        // Default password for new resellers
        $defaultPassword = 'RWAMP@agent';
        $hashedPassword = \Hash::make($defaultPassword);
        
        // Use password from application if exists, otherwise use default
        $passwordToUse = $application->password ? $application->password : $hashedPassword;
        
        $user = User::where('email', $application->email)->first();
        $isNewUser = !$user;
        
        if (!$user) {
            // Generate unique 16-digit wallet address
            $walletAddress = $this->generateUniqueWalletAddress();
            
            $user = User::create([
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'role' => 'reseller',
                'password' => $passwordToUse,
                'company_name' => $application->company,
                'investment_capacity' => $application->investment_capacity,
                'experience' => $application->experience,
                'email_verified_at' => null, // Email verification required on first login
                'wallet_address' => $walletAddress, // Auto-generated wallet address
            ]);
        } else {
            // Update existing user - generate wallet if missing
            if (!$user->wallet_address) {
                $walletAddress = $this->generateUniqueWalletAddress();
                $user->wallet_address = $walletAddress;
            }
            
            $user->update([
                'role' => 'reseller',
                'password' => $passwordToUse,
                'company_name' => $application->company,
                'investment_capacity' => $application->investment_capacity,
                'experience' => $application->experience,
                'email_verified_at' => null, // Require email verification on first login
            ]);
        }

        // Generate referral code for reseller: RSL{user_id}
        if (!$user->referral_code) {
            $user->update([
                'referral_code' => 'RSL' . $user->id,
            ]);
        }

        // Set password reset required flag in cache (expires in 30 days)
        \Illuminate\Support\Facades\Cache::put('password_reset_required_user_' . $user->id, true, now()->addDays(30));

        // Generate secure password reset token instead of sending password in plaintext
        $resetToken = null;
        $resetUrl = null;
        try {
            // Generate password reset token using Laravel's Password broker token repository
            $tokenRepository = \Illuminate\Support\Facades\Password::getRepository();
            $resetToken = $tokenRepository->create($user);
            $resetUrl = route('password.reset', ['token' => $resetToken, 'email' => $user->email]);
        } catch (\Throwable $e) {
            \Log::error('Failed to generate password reset token: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);
            // Continue without reset token - user can use "Forgot Password" instead
        }

        // Send email notification with secure password reset link (NOT the password)
        try {
            if (!empty($user->email)) {
                $loginUrl = route('login');
                \Mail::send('emails.reseller-approved', [
                    'user' => $user,
                    'loginUrl' => $loginUrl,
                    'resetUrl' => $resetUrl, // Secure password reset link
                    'hasResetToken' => !empty($resetToken),
                    'isNewUser' => $isNewUser,
                ], function($m) use ($user) {
                    $m->to($user->email, $user->name)
                      ->subject('RWAMP Reseller Application Approved - Welcome!');
                });
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send reseller approval email: ' . $e->getMessage());
            // Continue even if email fails
        }

        return back()->with('success', 'Application approved and reseller account created. Email sent with secure password setup link.');
    }

    public function reject(ResellerApplication $application)
    {
        // If already approved/rejected, do nothing
        if ($application->status !== 'pending') {
            return back()->with('success', 'Application already ' . $application->status . '.');
        }

        // Mark application rejected
        $application->update(['status' => 'rejected']);

        // Send rejection email notification
        try {
            if (!empty($application->email)) {
                \Mail::send('emails.reseller-rejected', [
                    'application' => $application,
                ], function($m) use ($application) {
                    $m->to($application->email, $application->name)
                      ->subject('RWAMP Reseller Application Status Update');
                });
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send reseller rejection email: ' . $e->getMessage());
            // Continue even if email fails
        }

        return back()->with('success', 'Application rejected and notification sent.');
    }

    public function cryptoPayments(Request $request)
    {
        $query = CryptoPayment::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Filter by network
        if ($request->filled('network') && in_array($request->network, ['TRC20', 'ERC20', 'BEP20', 'BTC', 'BNB'])) {
            $query->where('network', $request->network);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        if (in_array($sortBy, ['created_at', 'token_amount', 'status', 'network'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('dashboard.admin-crypto', compact('payments'));
    }

    public function approveCryptoPayment(Request $request, CryptoPayment $payment)
    {
        if ($payment->status === 'approved') {
            return back()->with('success', 'Payment already approved.');
        }

        $payment->update(['status' => 'approved']);

        // Credit tokens to the user account
        $user = $payment->user;
        if ($user) {
            $user->addTokens((int) $payment->token_amount, 'Crypto purchase approved');

            // Log a transaction record
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'crypto_purchase',
                'amount' => (int) $payment->token_amount,
                'status' => 'completed',
                'reference' => $payment->tx_hash,
            ]);

            // Award commission to reseller if applicable
            if ($user->reseller_id && !$payment->reseller_commission_awarded) {
                $reseller = User::find($user->reseller_id);
                if ($reseller && $reseller->role === 'reseller') {
                    $commissionRate = \App\Helpers\PriceHelper::getResellerCommissionRate();
                    $commission = (float) $payment->token_amount * $commissionRate;
                    
                    // Award commission to reseller
                    $reseller->increment('token_balance', $commission);
                    
                    // Log commission transaction
                    Transaction::create([
                        'user_id' => $reseller->id,
                        'type' => 'commission',
                        'amount' => (int) $commission,
                        'status' => 'completed',
                        'reference' => 'COMM-' . $payment->id,
                    ]);
                    
                    // Mark commission as awarded
                    $payment->update(['reseller_commission_awarded' => true]);
                }
            }
        }

        return back()->with('success', 'Payment approved and tokens credited.');
    }

    public function rejectCryptoPayment(Request $request, CryptoPayment $payment)
    {
        $payment->update(['status' => 'rejected']);
        return back()->with('success', 'Payment rejected.');
    }

    public function updateCryptoPayment(Request $request, CryptoPayment $payment)
    {
        $validated = $request->validate([
            'token_amount' => ['required', 'numeric', 'min:0'],
            'usd_amount' => ['nullable', 'string'],
            'pkr_amount' => ['nullable', 'string'],
            'network' => ['required', 'string', 'in:TRC20,ERC20,BEP20,BTC,BNB'],
            'tx_hash' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldStatus = $payment->status;
        $payment->update($validated);

        // If status changed from pending to approved, credit tokens
        if ($oldStatus !== 'approved' && $validated['status'] === 'approved') {
            $user = $payment->user;
            if ($user) {
                // Only credit if not already credited
                $existingTransaction = Transaction::where('reference', $payment->tx_hash)
                    ->where('type', 'crypto_purchase')
                    ->where('status', 'completed')
                    ->first();

                if (!$existingTransaction) {
                    $user->addTokens((int) $validated['token_amount'], 'Crypto purchase approved');
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'crypto_purchase',
                        'amount' => (int) $validated['token_amount'],
                        'status' => 'completed',
                        'reference' => $payment->tx_hash,
                    ]);
                }
            }
        }

        return back()->with('success', 'Payment updated successfully.');
    }

    public function deleteCryptoPayment(Request $request, CryptoPayment $payment)
    {
        // Delete screenshot if exists
        if ($payment->screenshot) {
            try {
                Storage::disk('local')->delete($payment->screenshot);
            } catch (\Exception $e) {
                // Ignore if file doesn't exist
            }
        }

        $payment->delete();

        return back()->with('success', 'Payment deleted successfully.');
    }

    public function cryptoPaymentDetails(CryptoPayment $payment)
    {
        $payment->load('user');
        return response()->json([
            'payment' => $payment,
            'user' => $payment->user,
        ]);
    }

    public function downloadCryptoPaymentScreenshot(CryptoPayment $payment)
    {
        if (!$payment->screenshot || !Storage::disk('local')->exists($payment->screenshot)) {
            abort(404, 'Screenshot not found');
        }

        $file = Storage::disk('local')->get($payment->screenshot);
        $mimeType = Storage::disk('local')->mimeType($payment->screenshot);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($payment->screenshot) . '"')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    public function history(Request $request)
    {
        $currentCoinPrice = (float) (config('crypto.rates.coin_price_rs') ?? config('app.coin_price_rs') ?? 0.70);

        // Payments query
        $paymentsQuery = CryptoPayment::with('user');
        
        // Search payments
        if ($request->filled('payment_search')) {
            $search = $request->payment_search;
            $paymentsQuery->where(function($q) use ($search) {
                $q->where('tx_hash', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
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
        $transactionsQuery = Transaction::with('user');
        
        // Exclude admin tracking transactions (admin_transfer_debit and admin side of admin_buy_from_user)
        // Only show user-facing transactions to avoid duplicates
        $transactionsQuery->where(function($q) {
            $q->where('type', '!=', 'admin_transfer_debit')
              ->where(function($subQ) {
                  // For admin_buy_from_user, only show user's transaction (negative amount = user sold)
                  // Exclude admin's transaction (positive amount = admin received)
                  $subQ->where('type', '!=', 'admin_buy_from_user')
                       ->orWhere(function($buyQ) {
                           $buyQ->where('type', 'admin_buy_from_user')
                                ->where('amount', '<', 0); // Only show user's debit transaction
                       });
              });
        });
        
        // Search transactions
        if ($request->filled('transaction_search')) {
            $search = $request->transaction_search;
            $transactionsQuery->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
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

        return view('dashboard.admin-history', compact('payments', 'transactions', 'currentCoinPrice'));
    }

    public function kycList(Request $request)
    {
        $valid = ['pending','approved','rejected'];
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('kyc_id_number', 'like', "%{$search}%")
                  ->orWhere('kyc_full_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        $status = $request->input('status');
        if ($status && in_array($status, $valid, true)) {
            $query->where('kyc_status', $status);
        } else {
            $query->whereIn('kyc_status', $valid);
        }

        // Filter by ID type
        if ($request->filled('id_type') && in_array($request->id_type, ['cnic', 'nicop', 'passport'])) {
            $query->where('kyc_id_type', $request->id_type);
        }

        // Only show users with submitted KYC
        $query->whereNotNull('kyc_submitted_at');

        // Sorting
        $sortBy = $request->get('sort', 'kyc_submitted_at');
        $sortDir = $request->get('dir', 'desc');
        if (in_array($sortBy, ['name', 'email', 'kyc_submitted_at', 'kyc_status', 'kyc_id_type'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest('kyc_submitted_at');
        }

        $kycSubmissions = $query->paginate(20)->withQueryString();

        return view('dashboard.admin-kyc', compact('kycSubmissions'));
    }

    public function approveKyc(Request $request, User $user)
    {
        if ($user->kyc_status !== 'pending') {
            return back()->with('error', 'Only pending KYC submissions can be approved.');
        }

        // Prepare update data - NEVER change admin or reseller roles
        $updateData = [
            'kyc_status' => 'approved',
            'kyc_approved_at' => now(),
        ];

        // Only upgrade to investor if current role is 'user'
        // NEVER change admin, reseller, or investor roles - preserve them
        if ($user->role === 'user') {
            $updateData['role'] = 'investor';
            $user->update($updateData);
            return back()->with('success', 'KYC approved. User role updated to investor.');
        } else {
            // User already has a role (admin, reseller, or investor) - preserve it
            // Do NOT include role in updateData to ensure it's never changed
            $user->update($updateData);
            return back()->with('success', 'KYC approved. User role preserved as ' . ucfirst($user->role) . '.');
        }
    }

    public function rejectKyc(Request $request, User $user)
    {
        if ($user->kyc_status !== 'pending') {
            return back()->with('error', 'Only pending KYC submissions can be rejected.');
        }

        $user->update([
            'kyc_status' => 'rejected',
        ]);

        // Optionally delete KYC files on rejection
        // Storage::delete([$user->kyc_id_front_path, $user->kyc_id_back_path, $user->kyc_selfie_path]);

        return back()->with('success', 'KYC rejected.');
    }

    public function updateKyc(Request $request, User $user)
    {
        $validated = $request->validate([
            'kyc_id_type' => ['required', 'in:cnic,nicop,passport'],
            'kyc_id_number' => ['required', 'string', 'max:50'],
            'kyc_full_name' => ['required', 'string', 'max:255'],
            'kyc_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $updateData = [
            'kyc_id_type' => $validated['kyc_id_type'],
            'kyc_id_number' => $validated['kyc_id_number'],
            'kyc_full_name' => $validated['kyc_full_name'],
            'kyc_status' => $validated['kyc_status'],
        ];

        // If status is being changed to approved, set approved_at
        if ($validated['kyc_status'] === 'approved' && $user->kyc_status !== 'approved') {
            $updateData['kyc_approved_at'] = now();
            
            // Only upgrade to investor if current role is 'user'
            // NEVER change admin, reseller, or investor roles
            if ($user->role === 'user') {
                $updateData['role'] = 'investor';
            }
        }

        // If status is being changed from approved, clear approved_at
        if ($validated['kyc_status'] !== 'approved' && $user->kyc_status === 'approved') {
            $updateData['kyc_approved_at'] = null;
        }

        $user->update($updateData);

        return back()->with('success', 'KYC information updated successfully.');
    }

    public function deleteKyc(Request $request, User $user)
    {
        // Delete KYC files if they exist
        if ($user->kyc_id_front_path && Storage::disk('local')->exists($user->kyc_id_front_path)) {
            Storage::disk('local')->delete($user->kyc_id_front_path);
        }
        if ($user->kyc_id_back_path && Storage::disk('local')->exists($user->kyc_id_back_path)) {
            Storage::disk('local')->delete($user->kyc_id_back_path);
        }
        if ($user->kyc_selfie_path && Storage::disk('local')->exists($user->kyc_selfie_path)) {
            Storage::disk('local')->delete($user->kyc_selfie_path);
        }

        // Clear KYC data - set status to 'not_started' (column doesn't allow null)
        $user->update([
            'kyc_status' => 'not_started',
            'kyc_id_type' => null,
            'kyc_id_number' => null,
            'kyc_full_name' => null,
            'kyc_id_front_path' => null,
            'kyc_id_back_path' => null,
            'kyc_selfie_path' => null,
            'kyc_submitted_at' => null,
            'kyc_approved_at' => null,
        ]);

        return back()->with('success', 'KYC submission deleted successfully.');
    }

    public function downloadKycFile(User $user, string $type)
    {
        $filePath = match($type) {
            'front' => $user->kyc_id_front_path,
            'back' => $user->kyc_id_back_path,
            'selfie' => $user->kyc_selfie_path,
            default => null,
        };

        if (!$filePath || !Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found');
        }

        // Serve image for viewing (not download)
        $file = Storage::disk('local')->get($filePath);
        $mimeType = Storage::disk('local')->mimeType($filePath);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Show price management page
     */
    public function priceManagement()
    {
        $usdPkr = PriceHelper::getUsdToPkrRate();
        
        // Get RWAMP prices
        $rwampPkr = PriceHelper::getRwampPkrPrice();
        $rwampUsd = PriceHelper::getRwampUsdPrice();
        
        // Fetch USDT and BTC prices dynamically
        $usdtUsd = $this->fetchUsdtPrice();
        $usdtPkr = $usdtUsd * $usdPkr;
        
        $btcUsd = $this->fetchBtcPrice();
        $btcPkr = $btcUsd * $usdPkr;
        
        // Get reseller rates from cache or config
        $resellerCommissionRate = Cache::get('reseller_commission_rate', config('crypto.reseller_commission_rate', 0.10));
        $resellerMarkupRate = Cache::get('reseller_markup_rate', config('crypto.reseller_markup_rate', 0.05));
        
        $currentPrices = [
            'rwamp_pkr' => $rwampPkr,
            'rwamp_usd' => $rwampUsd,
            'usdt_usd' => $usdtUsd,
            'usdt_pkr' => $usdtPkr,
            'btc_usd' => $btcUsd,
            'btc_pkr' => $btcPkr,
            'usd_pkr' => $usdPkr,
            'reseller_commission_rate' => $resellerCommissionRate,
            'reseller_markup_rate' => $resellerMarkupRate,
        ];

        return view('dashboard.admin-prices', compact('currentPrices'));
    }
    
    /**
     * Fetch current USDT price from CoinGecko API
     */
    private function fetchUsdtPrice(): float
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->get('https://api.coingecko.com/api/v3/simple/price', [
                'query' => [
                    'ids' => 'tether',
                    'vs_currencies' => 'usd'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            if (isset($data['tether']['usd'])) {
                return (float) $data['tether']['usd'];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch USDT price from CoinGecko: ' . $e->getMessage());
        }
        
        // Fallback to cached or config value
        $cached = Cache::get('crypto_price_usdt_usd');
        if ($cached !== null) {
            return (float) $cached;
        }
        return (float) config('crypto.rates.usdt_usd', 1.0);
    }

    /**
     * Fetch current BTC price from CoinGecko API
     */
    /**
     * Generate a unique 16-digit wallet address
     */
    private function generateUniqueWalletAddress(): string
    {
        do {
            $wallet = str_pad(random_int(1000000000000000, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } while (User::where('wallet_address', $wallet)->exists());

        return $wallet;
    }

    private function fetchBtcPrice(): float
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->get('https://api.coingecko.com/api/v3/simple/price', [
                'query' => [
                    'ids' => 'bitcoin',
                    'vs_currencies' => 'usd'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            if (isset($data['bitcoin']['usd'])) {
                return (float) $data['bitcoin']['usd'];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch BTC price from CoinGecko: ' . $e->getMessage());
        }
        
        // Fallback to cached or config value
        $cached = Cache::get('crypto_price_btc_usd');
        if ($cached !== null) {
            return (float) $cached;
        }
        return (float) config('crypto.rates.btc_usd', 60000);
    }

    /**
     * Update RWAMP token price (only PKR required, all other prices auto-calculated)
     */
    public function updatePrices(Request $request)
    {
        $request->validate([
            'rwamp_pkr' => 'required|numeric|min:0.01|max:1000000',
            'reseller_commission_rate' => 'nullable|numeric|min:0|max:100',
            'reseller_markup_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $rwampPkr = (float) $request->rwamp_pkr;
        $usdPkr = PriceHelper::getUsdToPkrRate();
        
        // Auto-calculate RWAMP/USD price from PKR using exchange rate
        // RWAMP/USD = RWAMP/PKR / USD/PKR
        $rwampUsd = $rwampPkr / $usdPkr;
        
        // Fetch USDT and BTC prices dynamically from API
        $usdtUsd = $this->fetchUsdtPrice();
        $usdtPkr = $usdtUsd * $usdPkr;
        
        $btcUsd = $this->fetchBtcPrice();
        $btcPkr = $btcUsd * $usdPkr;

        // Store prices in cache (persistent until updated)
        Cache::forever('crypto_price_rwamp_pkr', $rwampPkr);
        Cache::forever('crypto_price_rwamp_usd', $rwampUsd);
        Cache::forever('crypto_price_usdt_usd', $usdtUsd);
        Cache::forever('crypto_price_usdt_pkr', $usdtPkr);
        Cache::forever('crypto_price_btc_usd', $btcUsd);
        Cache::forever('crypto_price_btc_pkr', $btcPkr);

        // Store reseller rates in cache if provided (convert from percentage to decimal)
        if ($request->has('reseller_commission_rate')) {
            $commissionRate = (float) $request->reseller_commission_rate / 100; // Convert percentage to decimal
            Cache::forever('reseller_commission_rate', $commissionRate);
        }
        
        if ($request->has('reseller_markup_rate')) {
            $markupRate = (float) $request->reseller_markup_rate / 100; // Convert percentage to decimal
            Cache::forever('reseller_markup_rate', $markupRate);
        }

        // Clear config cache to ensure new prices are loaded
        \Artisan::call('config:clear');

        $message = 'Prices updated successfully! RWAMP/USD: $' . number_format($rwampUsd, 4) . ', USDT/USD: $' . number_format($usdtUsd, 4) . ', BTC/USD: $' . number_format($btcUsd, 2);
        
        if ($request->has('reseller_commission_rate') || $request->has('reseller_markup_rate')) {
            $message .= '. Reseller rates updated.';
        }

        return back()->with('success', $message);
    }

    /**
     * Admin User Management - List/Search/Filter users
     */
    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,investor,reseller,admin',
            'password' => 'nullable|string|min:8',
            'coin_quantity' => 'nullable|numeric|min:0',
            'price_per_coin' => 'nullable|numeric|min:0.01',
        ]);
        
        // Validate price_per_coin is required if coin_quantity is provided
        if (!empty($validated['coin_quantity']) && $validated['coin_quantity'] > 0) {
            if (empty($validated['price_per_coin']) || $validated['price_per_coin'] <= 0) {
                return redirect()->route('admin.users')
                    ->withErrors(['price_per_coin' => 'Price per coin is required when assigning coins.'])
                    ->withInput();
            }
        }

        // Use default password if not provided
        $password = $validated['password'] ?? 'RWAMP@agent';
        
        // Get coin assignment details (optional)
        $coinQuantity = $validated['coin_quantity'] ?? 0;
        $pricePerCoin = $validated['price_per_coin'] ?? 0;
        $assignCoins = $coinQuantity > 0 && $pricePerCoin > 0;

        try {
            \DB::beginTransaction();

            // Generate unique 16-digit wallet address
            $walletAddress = $this->generateUniqueWalletAddress();

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Auto-verify email for admin-created users
                'token_balance' => $assignCoins ? $coinQuantity : 0,
                'wallet_address' => $walletAddress, // Auto-generated wallet address
            ]);

            // If coins are assigned, create a transaction record
            if ($assignCoins) {
                $admin = Auth::user();
                $totalPrice = $coinQuantity * $pricePerCoin;
                
                // Create transaction record for the new user (credit)
                Transaction::create([
                    'user_id' => $user->id,
                    'sender_id' => $admin->id,
                    'recipient_id' => $user->id,
                    'type' => 'admin_transfer_credit',
                    'amount' => $coinQuantity,
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                    'sender_type' => 'admin',
                    'status' => 'completed',
                    'reference' => 'ADMIN-CREATE-' . time() . '-' . $user->id,
                    'payment_type' => null,
                    'payment_hash' => null,
                    'payment_receipt' => null,
                    'payment_status' => 'verified', // Admin-assigned coins are automatically verified
                ]);

                // Create transaction record for admin (debit tracking)
                Transaction::create([
                    'user_id' => $admin->id,
                    'sender_id' => $admin->id,
                    'recipient_id' => $user->id,
                    'type' => 'admin_transfer_debit',
                    'amount' => -$coinQuantity,
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                    'sender_type' => 'admin',
                    'status' => 'completed',
                    'reference' => 'ADMIN-CREATE-' . time() . '-' . $user->id,
                    'payment_type' => null,
                    'payment_hash' => null,
                    'payment_receipt' => null,
                    'payment_status' => 'verified',
                ]);

                \Log::info('Admin assigned coins during user creation', [
                    'admin_id' => $admin->id,
                    'user_id' => $user->id,
                    'coin_quantity' => $coinQuantity,
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                ]);
            }

            \DB::commit();

            // Optionally send welcome email with credentials
            // Mail::to($user->email)->send(new WelcomeMail($user, $password));

            $successMessage = 'User created successfully. Default password: ' . $password;
            if ($assignCoins) {
                $successMessage .= ' | Assigned ' . number_format($coinQuantity, 0) . ' RWAMP coins.';
            }

            return redirect()->route('admin.users')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating user: ' . $e->getMessage());
            
            return redirect()->route('admin.users')
                ->withErrors(['error' => 'Failed to create user. Please try again.'])
                ->withInput();
        }
    }

    public function usersIndex(Request $request)
    {
        $defaultPrice = PriceHelper::getRwampPkrPrice();
        $query = User::query();

        // Search
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        // Role filter
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Days filter (new users within last N days)
        if ($days = (int) $request->input('days')) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        // Sort
        $sort = in_array($request->input('sort'), ['name','email','created_at','role','token_balance']) ? $request->input('sort') : 'created_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $users = $query->with('reseller')->paginate(15)->withQueryString();

        return view('dashboard.admin-users', compact('users', 'defaultPrice'));
    }

    /**
     * Get user details with balance and transaction history
     */
    public function userDetails(User $user)
    {
        $user->load(['transactions' => function($query) {
            $query->latest()->limit(50);
        }]);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'token_balance' => $user->token_balance ?? 0,
                'wallet_address' => $user->wallet_address,
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                // KYC Details
                'kyc_status' => $user->kyc_status,
                'kyc_id_type' => $user->kyc_id_type,
                'kyc_id_number' => $user->kyc_id_number,
                'kyc_full_name' => $user->kyc_full_name,
                'kyc_submitted_at' => $user->kyc_submitted_at?->format('Y-m-d H:i:s'),
                'kyc_approved_at' => $user->kyc_approved_at?->format('Y-m-d H:i:s'),
                'kyc_id_front_path' => $user->kyc_id_front_path,
                'kyc_id_back_path' => $user->kyc_id_back_path,
                'kyc_selfie_path' => $user->kyc_selfie_path,
            ],
            'transactions' => $user->transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'price_per_coin' => $transaction->price_per_coin ? (float) $transaction->price_per_coin : null,
                    'total_price' => $transaction->total_price ? (float) $transaction->total_price : null,
                    'status' => $transaction->status,
                    'reference' => $transaction->reference,
                    'created_at' => $transaction->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Admin update a user's basic information
     */
    public function usersUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:investor,reseller,admin,user',
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Admin resets a user's password (optionally specify new password)
     */
    public function assignWalletAddress(Request $request, User $user)
    {
        try {
            \Log::info('Assign wallet address request received', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'has_wallet' => !empty($user->wallet_address)
            ]);

            // Check if user already has a wallet address
            if ($user->wallet_address) {
                \Log::warning('User already has wallet address', [
                    'user_id' => $user->id,
                    'wallet_address' => $user->wallet_address
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'User already has a wallet address: ' . $user->wallet_address
                ], 400);
            }

            // Generate unique wallet address
            $walletAddress = $this->generateUniqueWalletAddress();
            
            \Log::info('Generated wallet address', [
                'wallet_address' => $walletAddress
            ]);

            // Assign wallet address to user
            $updated = $user->update([
                'wallet_address' => $walletAddress
            ]);

            if (!$updated) {
                \Log::error('Failed to update user with wallet address', [
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save wallet address. Please try again.'
                ], 500);
            }

            // Refresh user model to get updated data
            $user->refresh();

            \Log::info('Wallet address assigned successfully', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'wallet_address' => $walletAddress
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet address assigned successfully',
                'wallet_address' => $walletAddress
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error assigning wallet address', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign wallet address: ' . $e->getMessage()
            ], 500);
        }
    }

    public function usersResetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'nullable|string|min:8|max:128',
        ]);

        $newPassword = $request->input('new_password') ?: 'RWAMP@agent';
        $user->update(['password' => Hash::make($newPassword)]);

        // Force password change on next login
        Cache::put('password_reset_required_user_'.$user->id, true, now()->addYear());

        // Best-effort notify the user
        try {
            if (!empty($user->email)) {
                Mail::raw("Your RWAMP password has been reset by an administrator.\n\nTemporary Password: {$newPassword}\n\nPlease log in and change your password immediately.", function($m) use ($user) {
                    $m->to($user->email)->subject('RWAMP Password Reset');
                });
            }
        } catch (\Throwable $e) {}

        return back()->with('success', 'Password reset successfully. User will be prompted to change it on next login.');
    }

    /**
     * Admin deletes a user
     */
    public function usersDelete(Request $request, User $user)
    {
        // Prevent deleting the current admin user
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Prevent deleting the last admin
        $adminCount = User::where('role', 'admin')->count();
        if ($user->role === 'admin' && $adminCount <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last admin user.']);
        }

        $userEmail = $user->email;
        $userName = $user->name;
        
        // Delete the user
        $user->delete();

        return back()->with('success', "User '{$userName}' ({$userEmail}) has been deleted successfully.");
    }

    /**
     * Reseller applications list with search and filters
     */
    public function applicationsIndex(Request $request)
    {
        $query = ResellerApplication::query();

        // Search
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('company', 'like', "%{$q}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            if (in_array($status, ['pending','approved','rejected'], true)) {
                $query->where('status', $status);
            }
        }

        // Investment capacity filter
        if ($capacity = $request->input('capacity')) {
            $query->where('investment_capacity', $capacity);
        }

        // Sort
        $sort = in_array($request->input('sort'), ['name','email','created_at','status']) ? $request->input('sort') : 'created_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $applications = $query->paginate(15)->withQueryString();
        $status = $request->input('status', 'all');

        return view('dashboard.admin-applications', compact('applications', 'status'));
    }

    /**
     * Get application details
     */
    public function applicationDetails(ResellerApplication $application)
    {
        return response()->json([
            'application' => [
                'id' => $application->id,
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'company' => $application->company,
                'investment_capacity' => $application->investment_capacity,
                'investment_capacity_label' => $application->investment_capacity_label,
                'message' => $application->message,
                'status' => $application->status,
                'ip_address' => $application->ip_address,
                'user_agent' => $application->user_agent,
                'created_at' => $application->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $application->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Update application
     */
    public function applicationUpdate(Request $request, ResellerApplication $application)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'investment_capacity' => 'required|in:1-10k,10-50k,50-100k,100k+',
            'message' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $application->update($validated);

        return back()->with('success', 'Application updated successfully.');
    }

    /**
     * Delete application
     */
    public function applicationDelete(Request $request, ResellerApplication $application)
    {
        $applicationName = $application->name;
        $applicationEmail = $application->email;
        
        $application->delete();

        return back()->with('success', "Application from '{$applicationName}' ({$applicationEmail}) has been deleted successfully.");
    }

    /**
     * List withdrawal requests
     */
    public function withdrawals(Request $request)
    {
        $query = \App\Models\WithdrawRequest::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('wallet_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        if (in_array($sortBy, ['created_at', 'token_amount', 'status'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        return view('dashboard.admin-withdrawals', compact('withdrawals'));
    }

    /**
     * Show withdrawal request details
     */
    public function showWithdrawal(\App\Models\WithdrawRequest $withdrawal)
    {
        $withdrawal->load('user');
        return view('dashboard.admin-withdrawal-details', compact('withdrawal'));
    }

    /**
     * Approve withdrawal request (manual transfer by admin)
     */
    public function approveWithdrawal(Request $request, \App\Models\WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Withdrawal already processed.');
        }

        $user = $withdrawal->user;

        // Note: Tokens are already deducted when withdrawal request is submitted
        // We just need to approve the request and admin will transfer manually

        // Update withdrawal status
        $withdrawal->update([
            'status' => 'approved',
            'notes' => $request->input('notes', $withdrawal->notes)
        ]);

        // Log transaction (tokens already deducted on submission)
        try {
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_approved',
                'amount' => (int) $withdrawal->token_amount,
                'status' => 'completed',
                'reference' => 'WDR-APPROVED-' . $withdrawal->id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log withdrawal approval transaction', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawal->id,
            ]);
            // Continue even if transaction logging fails
        }

        // Send email notification
        try {
            // Refresh withdrawal to ensure we have latest data
            $withdrawal->refresh();
            
            // Use Mail facade with explicit configuration - same pattern as working emails (reject, update, delete)
            \Mail::send('emails.withdrawal-approved', [
                'user' => $user,
                'withdrawal' => $withdrawal,
            ], function($m) use ($user) {
                $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                  ->to($user->email, $user->name)
                  ->subject('Withdrawal Request Approved - RWAMP');
            });
            
            \Log::info('Withdrawal approval email sent successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'withdrawal_id' => $withdrawal->id,
                'mail_driver' => config('mail.default'),
                'mail_from' => config('mail.from.address'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to send withdrawal approval email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'withdrawal_id' => $withdrawal->id,
                'mail_driver' => config('mail.default'),
                'mail_from' => config('mail.from.address'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
            // Don't fail the request if email fails, but log it
        }

        return back()->with('success', 'Withdrawal approved. Tokens were already deducted on submission. Admin will transfer manually within 24 hours. User will be notified via email.');
    }

    /**
     * Submit receipt for withdrawal transfer
     */
    public function submitReceipt(Request $request, \App\Models\WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'approved') {
            return back()->with('error', 'Can only submit receipt for approved withdrawals.');
        }

        $validated = $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'transaction_hash' => 'nullable|string|max:255',
        ]);

        $user = $withdrawal->user;

        try {
            // Store receipt file
            $receiptPath = $request->file('receipt')->store('withdrawal-receipts', 'public');

            // Update withdrawal with receipt and transaction hash
            $withdrawal->update([
                'receipt_path' => $receiptPath,
                'transaction_hash' => $validated['transaction_hash'] ?? null,
                'transfer_completed_at' => now(),
            ]);

            // Refresh withdrawal to get updated data
            $withdrawal->refresh();

            // Send email notification to user - inside try block but after update
            try {
                // Use Mail facade with explicit configuration - same pattern as working emails
                \Mail::send('emails.withdrawal-completed', [
                    'user' => $user,
                    'withdrawal' => $withdrawal,
                ], function($m) use ($user) {
                    $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                      ->to($user->email, $user->name)
                      ->subject('Withdrawal Transfer Completed - RWAMP');
                });
                
                \Log::info('Withdrawal completion email sent successfully', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'withdrawal_id' => $withdrawal->id,
                    'mail_driver' => config('mail.default'),
                    'mail_from' => config('mail.from.address'),
                    'mail_host' => config('mail.mailers.smtp.host'),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to send withdrawal completion email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'withdrawal_id' => $withdrawal->id,
                    'mail_driver' => config('mail.default'),
                    'mail_from' => config('mail.from.address'),
                    'mail_host' => config('mail.mailers.smtp.host'),
                ]);
                // Don't fail the request if email fails, but log it
            }

            return back()->with('success', 'Receipt submitted successfully. User has been notified via email.');
        } catch (\Exception $e) {
            \Log::error('Failed to submit withdrawal receipt', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawal->id,
            ]);
            return back()->with('error', 'Failed to submit receipt. Please try again.');
        }
    }

    /**
     * Reject withdrawal request
     */
    public function rejectWithdrawal(Request $request, \App\Models\WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Withdrawal already processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $user = $withdrawal->user;

        // Update withdrawal status
        $withdrawal->update([
            'status' => 'rejected',
            'notes' => $validated['rejection_reason'] ?? 'Withdrawal request rejected by admin.'
        ]);

        // Return tokens to user since withdrawal was rejected
        // Tokens were deducted on submission, so we need to add them back
        $user->addTokens($withdrawal->token_amount, 'Withdrawal request rejected - tokens refunded - WDR-' . $withdrawal->id);

        // Log transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal_refund',
            'amount' => (int) $withdrawal->token_amount,
            'status' => 'completed',
            'reference' => 'WDR-REFUND-' . $withdrawal->id,
        ]);

        // Send email notification
        try {
            // Refresh withdrawal to ensure we have latest data
            $withdrawal->refresh();
            
            // Use Mail facade with explicit configuration
            \Mail::send('emails.withdrawal-rejected', [
                'user' => $user,
                'withdrawal' => $withdrawal,
                'reason' => $validated['rejection_reason'] ?? 'Withdrawal request rejected by admin.',
            ], function($m) use ($user) {
                $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                  ->to($user->email, $user->name)
                  ->subject('Withdrawal Request Rejected - RWAMP');
            });
            
            \Log::info('Withdrawal rejection email sent successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'withdrawal_id' => $withdrawal->id,
                'mail_driver' => config('mail.default'),
                'mail_from' => config('mail.from.address'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to send withdrawal rejection email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'withdrawal_id' => $withdrawal->id,
                'mail_driver' => config('mail.default'),
                'mail_from' => config('mail.from.address'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
            // Don't fail the request if email fails, but log it
        }

        return back()->with('success', 'Withdrawal rejected. User will be notified via email.');
    }

    /**
     * Update withdrawal request
     */
    public function updateWithdrawal(Request $request, \App\Models\WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Cannot edit processed withdrawal.');
        }

        $validated = $request->validate([
            'wallet_address' => 'required|string|max:255',
            'token_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $withdrawal->user;
        $oldAmount = $withdrawal->token_amount;
        $newAmount = $validated['token_amount'];
        $amountDifference = $newAmount - $oldAmount;

        // Use database transaction to ensure atomicity
        \DB::beginTransaction();
        try {
            if ($amountDifference > 0) {
                // Amount increased - deduct additional tokens
                $additionalAmount = $amountDifference;
                
                // Verify user has sufficient balance for the additional amount
                if ($user->token_balance < $additionalAmount) {
                    \DB::rollBack();
                    return back()->with('error', 'User has insufficient balance for the increased amount. User has ' . number_format($user->token_balance, 2) . ' RWAMP tokens available.');
                }

                // Deduct additional tokens
                $user->deductTokens($additionalAmount, 'Withdrawal request amount increased - additional tokens deducted - WDR-' . $withdrawal->id);

                // Log transaction
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal_adjustment',
                    'amount' => (int) $additionalAmount,
                    'status' => 'completed',
                    'reference' => 'WDR-INCREASE-' . $withdrawal->id,
                ]);
            } elseif ($amountDifference < 0) {
                // Amount decreased - refund the difference
                $refundAmount = abs($amountDifference);
                
                // Refund tokens to user
                $user->addTokens($refundAmount, 'Withdrawal request amount decreased - tokens refunded - WDR-' . $withdrawal->id);

                // Log transaction
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal_refund',
                    'amount' => (int) $refundAmount,
                    'status' => 'completed',
                    'reference' => 'WDR-DECREASE-REFUND-' . $withdrawal->id,
                ]);
            }
            // If amountDifference == 0, no token adjustment needed

            // Store old data for email comparison
            $oldWalletAddress = $withdrawal->wallet_address;
            $oldNotes = $withdrawal->notes;

            // Update withdrawal request
            $withdrawal->update($validated);

            // Refresh to get updated data
            $withdrawal->refresh();

            \DB::commit();

            // Send email notification
            try {
                $changes = [];
                if ($oldAmount != $newAmount) {
                    $changes[] = 'Amount changed from ' . number_format($oldAmount, 2) . ' to ' . number_format($newAmount, 2) . ' RWAMP';
                }
                if ($oldWalletAddress != $validated['wallet_address']) {
                    $changes[] = 'Wallet address updated';
                }
                if ($oldNotes != ($validated['notes'] ?? '')) {
                    $changes[] = 'Notes updated';
                }

                // Use Mail facade with explicit configuration
                \Mail::send('emails.withdrawal-updated', [
                    'user' => $user,
                    'withdrawal' => $withdrawal,
                    'oldAmount' => $oldAmount,
                    'newAmount' => $newAmount,
                    'amountDifference' => $amountDifference,
                    'changes' => $changes,
                ], function($m) use ($user) {
                    $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                      ->to($user->email, $user->name)
                      ->subject('Withdrawal Request Updated - RWAMP');
                });
                
                \Log::info('Withdrawal update email sent successfully', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'withdrawal_id' => $withdrawal->id,
                    'mail_driver' => config('mail.default'),
                    'mail_from' => config('mail.from.address'),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to send withdrawal update email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'withdrawal_id' => $withdrawal->id,
                    'mail_driver' => config('mail.default'),
                    'mail_from' => config('mail.from.address'),
                ]);
                // Don't fail the request if email fails, but log it
            }

            $message = 'Withdrawal request updated successfully.';
            if ($amountDifference > 0) {
                $message .= ' ' . number_format($additionalAmount, 2) . ' additional RWAMP tokens have been deducted from the user\'s balance.';
            } elseif ($amountDifference < 0) {
                $message .= ' ' . number_format($refundAmount, 2) . ' RWAMP tokens have been refunded to the user\'s balance.';
            }
            $message .= ' User has been notified via email.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to update withdrawal request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $user->id ?? null,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
            ]);
            return back()->with('error', 'Failed to update withdrawal request. Please try again.');
        }
    }

    /**
     * Delete withdrawal request
     */
    public function deleteWithdrawal(Request $request, \App\Models\WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Cannot delete processed withdrawal.');
        }

        $validated = $request->validate([
            'deletion_reason' => 'nullable|string|max:500',
        ]);

        $user = $withdrawal->user;
        $tokenAmount = $withdrawal->token_amount;
        $withdrawalId = $withdrawal->id;
        $deletionReason = $validated['deletion_reason'] ?? 'Withdrawal request deleted by admin.';

        // Store withdrawal data before deletion for email
        $withdrawalData = [
            'id' => $withdrawal->id,
            'token_amount' => $withdrawal->token_amount,
            'wallet_address' => $withdrawal->wallet_address,
            'created_at' => $withdrawal->created_at,
        ];

        // Use database transaction to ensure atomicity
        \DB::beginTransaction();
        try {
            // Refund tokens to user before deletion
            // Tokens were deducted on submission, so we need to add them back
            $user->addTokens($tokenAmount, 'Withdrawal request deleted - tokens refunded - WDR-' . $withdrawalId);

            // Log transaction for audit trail
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_refund',
                'amount' => (int) $tokenAmount,
                'status' => 'completed',
                'reference' => 'WDR-DELETE-REFUND-' . $withdrawalId,
            ]);

            // Delete the withdrawal request
            $withdrawal->delete();

            \DB::commit();

            // Send email notification
            try {
                // Use Mail facade with explicit configuration
                \Mail::send('emails.withdrawal-deleted', [
                    'user' => $user,
                    'withdrawal' => (object) $withdrawalData, // Convert array to object for template
                    'reason' => $deletionReason,
                ], function($m) use ($user) {
                    $m->from(config('mail.from.address', 'no-reply@rwamp.com'), config('mail.from.name', 'RWAMP'))
                      ->to($user->email, $user->name)
                      ->subject('Withdrawal Request Deleted - RWAMP');
                });
                
                \Log::info('Withdrawal deletion email sent successfully', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'withdrawal_id' => $withdrawalId,
                    'mail_driver' => config('mail.default'),
                    'mail_from' => config('mail.from.address'),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to send withdrawal deletion email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'withdrawal_id' => $withdrawalId,
                    'mail_driver' => config('mail.default'),
                    'mail_from' => config('mail.from.address'),
                ]);
                // Don't fail the request if email fails, but log it
            }

            return back()->with('success', 'Withdrawal request deleted successfully. ' . number_format($tokenAmount, 2) . ' RWAMP tokens have been refunded to the user. User has been notified via email.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to delete withdrawal request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $withdrawalId,
                'user_id' => $user->id ?? null,
            ]);
            return back()->with('error', 'Failed to delete withdrawal request. Please try again.');
        }
    }

    /**
     * Display admin sell coins page
     */
    public function sellPage(Request $request)
    {
        $admin = Auth::user();
        $defaultPrice = PriceHelper::getRwampPkrPrice();
        $userId = $request->query('user_id');
        
        $preSelectedUser = null;
        if ($userId) {
            $preSelectedUser = User::where('id', $userId)
                ->where('id', '!=', $admin->id)
                ->whereIn('role', ['investor', 'reseller', 'user'])
                ->select('id', 'name', 'email', 'token_balance', 'role')
                ->first();
        }
        
        return view('dashboard.admin-sell', compact('defaultPrice', 'preSelectedUser'));
    }

    /**
     * Fetch user's payment proof based on payment type (for admin)
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
     * Search users/resellers for admin sell page
     */
    public function searchUsersForSell(Request $request)
    {
        try {
            $admin = Auth::user();
            $query = trim($request->input('q', ''));

            // Search ALL users and resellers (excluding admin)
            $usersQuery = User::where('id', '!=', $admin->id)
                ->whereIn('role', ['investor', 'reseller', 'user']);

            // Apply search filter if query is provided and has at least 1 character
            if (!empty($query) && strlen($query) >= 1) {
                $usersQuery->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('id', 'like', "%{$query}%");
                });
            }

            $users = $usersQuery
                ->select('id', 'name', 'email', 'token_balance', 'role')
                ->orderBy('name', 'asc')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name ?? 'N/A',
                        'email' => $user->email ?? 'N/A',
                        'token_balance' => (float) ($user->token_balance ?? 0),
                        'role' => $user->role ?? 'investor',
                    ];
                });

            return response()->json($users);
        } catch (\Exception $e) {
            \Log::error('Error in searchUsersForSell: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed. Please try again.'], 500);
        }
    }

    /**
     * Send OTP for admin sell
     */
    public function sendOtpForSell(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            $email = \Illuminate\Support\Str::lower(trim($validated['email']));
            $admin = Auth::user();

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to send OTP.'
                ], 401);
            }

            // Verify email matches admin's email
            if ($email !== \Illuminate\Support\Str::lower(trim($admin->email))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email does not match your account.'
                ], 422);
            }

            $otpController = new \App\Http\Controllers\Auth\EmailVerificationController();
            $otpController->generateAndSendOtp($email);

            \Log::info('Admin OTP sent successfully', ['email' => $email, 'admin_id' => $admin->id]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Admin OTP validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Admin OTP send error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP: ' . ($e->getMessage() ?? 'Unknown error. Please try again.')
            ], 500);
        }
    }

    /**
     * Process admin sell coins transaction
     */
    public function sellCoins(Request $request)
    {
        // Custom validation for OTP to handle spaces
        $request->merge([
            'otp' => preg_replace('/\s+/', '', (string) $request->input('otp', ''))
        ]);
        
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'coin_quantity' => 'required|numeric|min:1',
            'price_per_coin' => 'required|numeric|min:0.01',
            'otp' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'email' => 'required|email',
            'payment_received' => 'required|in:yes,no',
            'payment_type' => 'required_if:payment_received,yes|in:usdt,bank,cash',
            'payment_hash' => 'required_if:payment_type,usdt|nullable|string|max:255',
            'payment_receipt' => 'required_if:payment_type,bank|nullable|string|max:255',
        ]);

        $admin = Auth::user();
        $recipient = User::findOrFail($validated['recipient_id']);

        // Prevent self-transfer
        if ($recipient->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer to yourself.'
            ], 400);
        }

        // Verify OTP
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));
        if ($normalizedEmail !== \Illuminate\Support\Str::lower(trim($admin->email))) {
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
                $cachedOtpRaw = $debugData['otp'];
            }
        }
        
        // Clean and normalize submitted OTP (already cleaned in validation, but ensure it's correct)
        $rawOtp = (string) $validated['otp'];
        $otp = preg_replace('/\s+/', '', $rawOtp); // Remove all spaces (in case validation didn't catch it)
        $otp = preg_replace('/[^0-9]/', '', $otp); // Remove any non-numeric characters
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT); // Pad to 6 digits
        
        // Normalize cached OTP
        $cachedOtp = $cachedOtpRaw ? str_pad((string) $cachedOtpRaw, 6, '0', STR_PAD_LEFT) : null;
        
        // Also check session fallback (even in production) for better compatibility
        if ($cachedOtpRaw === null) {
            $debugData = session()->get("otp_debug_{$normalizedEmail}");
            if ($debugData && isset($debugData['otp'])) {
                $cachedOtpRaw = $debugData['otp'];
                $cachedOtp = str_pad((string) $cachedOtpRaw, 6, '0', STR_PAD_LEFT);
            }
        }

        // Comprehensive logging for debugging
        \Log::info("Admin Sell - OTP Verification DETAILED", [
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
            $debugInfo = config('app.debug') ? [
                'debug' => [
                    'submitted_otp' => $otp,
                    'submitted_otp_length' => strlen($otp),
                    'submitted_otp_raw' => $rawOtp,
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

        // Calculate total price
        $coinQuantity = (float) $validated['coin_quantity'];
        $pricePerCoin = (float) $validated['price_per_coin'];
        $totalPrice = $coinQuantity * $pricePerCoin;

        // Handle payment receipt - now it's a file path from user's account, not a file upload
        $paymentReceiptPath = null;
        if ($validated['payment_received'] === 'yes' && $validated['payment_type'] === 'bank' && !empty($validated['payment_receipt'])) {
            // Payment receipt is already stored in user's account, just use the path
            $paymentReceiptPath = $validated['payment_receipt'];
        }

        // Determine payment status (admin verifies payments from resellers/users)
        $paymentStatus = 'pending';
        if ($validated['payment_received'] === 'yes') {
            $paymentStatus = 'pending'; // Needs admin verification
        }

        // Perform transfer in transaction
        try {
            \DB::beginTransaction();

            // Create transaction record for recipient (credit) FIRST
            Transaction::create([
                'user_id' => $recipient->id,
                'sender_id' => $admin->id,
                'recipient_id' => $recipient->id,
                'type' => 'admin_transfer_credit',
                'amount' => $coinQuantity,
                'price_per_coin' => $pricePerCoin,
                'total_price' => $totalPrice,
                'sender_type' => 'admin',
                'status' => 'completed',
                'reference' => 'ADMIN-SELL-' . time() . '-' . $recipient->id,
                'payment_type' => $validated['payment_received'] === 'yes' ? $validated['payment_type'] : null,
                'payment_hash' => $validated['payment_received'] === 'yes' && $validated['payment_type'] === 'usdt' ? $validated['payment_hash'] : null,
                'payment_receipt' => $paymentReceiptPath,
                'payment_status' => $paymentStatus,
            ]);

            // Create transaction record for admin (debit tracking)
            Transaction::create([
                'user_id' => $admin->id,
                'sender_id' => $admin->id,
                'recipient_id' => $recipient->id,
                'type' => 'admin_transfer_debit',
                'amount' => -$coinQuantity,
                'price_per_coin' => $pricePerCoin,
                'total_price' => $totalPrice,
                'sender_type' => 'admin',
                'status' => 'completed',
                'reference' => 'ADMIN-SELL-' . time() . '-' . $recipient->id,
                'payment_type' => $validated['payment_received'] === 'yes' ? $validated['payment_type'] : null,
                'payment_hash' => $validated['payment_received'] === 'yes' && $validated['payment_type'] === 'usdt' ? $validated['payment_hash'] : null,
                'payment_receipt' => $paymentReceiptPath,
                'payment_status' => $paymentStatus,
            ]);

            // THEN update balance using increment (not direct assignment)
            $recipient->increment('token_balance', $coinQuantity);

            // Clear OTP from cache
            Cache::forget($cacheKey);

            \DB::commit();

            \Log::info('Admin sell coins completed', [
                'admin_id' => $admin->id,
                'recipient_id' => $recipient->id,
                'coin_quantity' => $coinQuantity,
                'price_per_coin' => $pricePerCoin,
                'total_price' => $totalPrice,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coins transferred successfully!',
                'data' => [
                    'recipient' => $recipient->name,
                    'quantity' => $coinQuantity,
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                ]
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Admin sell coins error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer coins. Please try again.'
            ], 500);
        }
    }

    /**
     * Show 2FA setup page with proper error handling
     */
    public function showTwoFactorSetup()
    {
        try {
            $user = Auth::user();
            
            // Check if 2FA secret exists but is corrupted
            $hasCorruptedSecret = false;
            $hasCorruptedRecoveryCodes = false;
            $secretExists = false;
            $recoveryCodesExist = false;
            
            // Safely check if secret exists without triggering decryption
            try {
                $secretValue = $user->getAttribute('two_factor_secret');
                $secretExists = !empty($secretValue);
                
                if ($secretExists) {
                    try {
                        // Try to decrypt the secret to check if it's valid
                        \Laravel\Fortify\Fortify::currentEncrypter()->decrypt($secretValue);
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        $hasCorruptedSecret = true;
                        \Log::warning('Corrupted 2FA secret for user ' . $user->id . ': ' . $e->getMessage());
                    } catch (\Throwable $e) {
                        $hasCorruptedSecret = true;
                        \Log::warning('Error checking 2FA secret for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error accessing two_factor_secret: ' . $e->getMessage());
            }
            
            // Safely check if recovery codes exist without triggering decryption
            try {
                $recoveryCodesValue = $user->getAttribute('two_factor_recovery_codes');
                $recoveryCodesExist = !empty($recoveryCodesValue);
                
                if ($recoveryCodesExist) {
                    try {
                        // Use the overridden method which handles errors gracefully
                        $codes = $user->recoveryCodes();
                        // If it returns empty array, codes might be corrupted
                        if (empty($codes) && !empty($recoveryCodesValue)) {
                            // Try direct decryption to confirm corruption
                            try {
                                \Laravel\Fortify\Fortify::currentEncrypter()->decrypt($recoveryCodesValue);
                            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                                $hasCorruptedRecoveryCodes = true;
                                \Log::warning('Corrupted recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                            }
                        }
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        $hasCorruptedRecoveryCodes = true;
                        \Log::warning('Corrupted recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                    } catch (\Throwable $e) {
                        \Log::warning('Error checking recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error accessing two_factor_recovery_codes: ' . $e->getMessage());
            }
            
            return view('auth.two-factor-setup', [
                'hasCorruptedSecret' => $hasCorruptedSecret,
                'hasCorruptedRecoveryCodes' => $hasCorruptedRecoveryCodes,
                'secretExists' => $secretExists,
                'recoveryCodesExist' => $recoveryCodesExist,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error loading 2FA setup page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return view('auth.two-factor-setup', [
                'error' => 'An error occurred while loading the 2FA setup page. Please try again.',
                'hasCorruptedSecret' => false,
                'hasCorruptedRecoveryCodes' => false,
                'secretExists' => false,
                'recoveryCodesExist' => false,
            ]);
        }
    }

    /**
     * Regenerate two-factor authentication recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        // Check if 2FA is enabled
        if (!$user->two_factor_secret) {
            return back()->with('error', 'Two-factor authentication is not enabled on your account.');
        }

        try {
            // Use Fortify's action to generate new recovery codes
            $generate = new \Laravel\Fortify\Actions\GenerateNewRecoveryCodes();
            $generate($user);

            return back()->with('success', 'Recovery codes have been regenerated successfully. Please save them in a safe place.');
        } catch (\Exception $e) {
            \Log::error('Failed to regenerate recovery codes: ' . $e->getMessage());
            return back()->with('error', 'Failed to regenerate recovery codes. Please try again.');
        }
    }

    /**
     * View all chats (admin read-only access)
     */
    public function chatsIndex(Request $request)
    {
        $query = \App\Models\Chat::with(['participants', 'latestMessage.sender']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('participants', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by type
        if ($request->filled('type') && in_array($request->type, ['private', 'group'])) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $chatIds = \App\Models\ChatParticipant::where('user_id', $request->user_id)->pluck('chat_id');
            $query->whereIn('id', $chatIds);
        }

        // Filter by deleted messages only
        if ($request->filled('show_deleted_only')) {
            $query->whereHas('messages', function($q) {
                $q->where('is_deleted', true);
            });
        }

        $chats = $query->withCount('messages')
            ->orderBy('last_message_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.chat.index', compact('chats'));
    }

    /**
     * View a specific chat (admin read-only)
     */
    public function viewChat(\App\Models\Chat $chat)
    {
        // Admin can view all chats - no permission check needed
        $messages = $chat->messages()
            ->withTrashed() // Include deleted messages
            ->with(['sender', 'deletedBy'])
            ->orderBy('created_at', 'asc')
            ->get();

        $participants = $chat->participants;

        // Log admin access
        \Log::info('Admin viewed chat', [
            'chat_id' => $chat->id,
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
        ]);

        return view('admin.chat.view', [
            'chat' => $chat,
            'messages' => $messages,
            'participants' => $participants,
        ]);
    }

    /**
     * Get chat audit trail
     */
    public function auditTrail(\App\Models\Chat $chat)
    {
        $messages = $chat->messages()
            ->withTrashed()
            ->with(['sender', 'deletedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $auditData = [
            'chat' => $chat,
            'total_messages' => $chat->messages()->count(),
            'deleted_messages' => $chat->messages()->where('is_deleted', true)->count(),
            'messages' => $messages,
        ];

        return response()->json($auditData);
    }
}



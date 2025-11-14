<?php

namespace App\Http\Controllers;

use App\Models\ResellerApplication;
use App\Models\User;
use App\Models\Transaction;
use App\Models\CryptoPayment;
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
        $users = User::query();
        $now = now();
        $metrics = [
            'users' => $users->count(),
            'resellers' => User::where('role','reseller')->count(),
            'investors' => User::where('role','investor')->count(),
            'new_users_7' => User::where('created_at','>=',$now->copy()->subDays(7))->count(),
            'new_users_30' => User::where('created_at','>=',$now->copy()->subDays(30))->count(),
            'pending_applications' => ResellerApplication::where('status','pending')->count(),
            'approved_applications' => ResellerApplication::where('status','approved')->count(),
            'rejected_applications' => ResellerApplication::where('status','rejected')->count(),
            'pending_kyc' => User::where('kyc_status','pending')->count(),
            'contacts' => \App\Models\Contact::count(),
            'coin_price' => \App\Helpers\PriceHelper::getRwampPkrPrice(),
            'crypto_payments' => CryptoPayment::count(),
            'pending_crypto_payments' => CryptoPayment::where('status','pending')->count(),
        ];

        return view('dashboard.admin', [
            'metrics' => $metrics,
            'applications' => ResellerApplication::latest()->limit(10)->get(),
        ]);
    }

    public function approve(ResellerApplication $application)
    {
        // If already approved/rejected, do nothing
        if ($application->status !== 'pending') {
            return back()->with('success', 'Application already ' . $application->status . '.');
        }

        // Mark application approved
        $application->update(['status' => 'approved']);

        // Create or update reseller user with default one-time password
        $defaultPassword = 'RWAMP@agent';
        $user = User::where('email', $application->email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'role' => 'reseller',
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
                'company_name' => $application->company,
                'experience' => null,
            ]);
        } else {
            // Ensure role and default password are set for first login
            $user->update([
                'role' => 'reseller',
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
            ]);
        }

        // Generate referral code for reseller: RSL{user_id}
        if (!$user->referral_code) {
            $user->update([
                'referral_code' => 'RSL' . $user->id,
            ]);
        }

        // Flag user to change password on next login (cache-based flag without migration)
        \Cache::put('password_reset_required_user_'.$user->id, true, now()->addYear());

        // Send email notification with credentials
        try {
            if (!empty($user->email)) {
                $loginUrl = route('login');
                \Mail::send('emails.reseller-approved', [
                    'user' => $user,
                    'defaultPassword' => $defaultPassword,
                    'loginUrl' => $loginUrl,
                ], function($m) use ($user) {
                    $m->to($user->email, $user->name)
                      ->subject('RWAMP Reseller Account Approved - Welcome!');
                });
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send reseller approval email: ' . $e->getMessage());
            // Continue even if email fails
        }

        return back()->with('success', 'Application approved and reseller account created.');
    }

    public function reject(ResellerApplication $application)
    {
        $application->update(['status' => 'rejected']);
        return back()->with('success', 'Application rejected');
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
        $usdPkr = config('crypto.rates.usd_pkr', 278);
        
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
        $usdPkr = config('crypto.rates.usd_pkr', 278);
        
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
        ]);

        // Use default password if not provided
        $password = $validated['password'] ?? 'RWAMP@agent';

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($password),
            'email_verified_at' => now(), // Auto-verify email for admin-created users
        ]);

        // Optionally send welcome email with credentials
        // Mail::to($user->email)->send(new WelcomeMail($user, $password));

        return redirect()->route('admin.users')
            ->with('success', __('User created successfully. Default password: :password', ['password' => $password]));
    }

    public function usersIndex(Request $request)
    {
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
        $sort = in_array($request->input('sort'), ['name','email','created_at','role']) ? $request->input('sort') : 'created_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $users = $query->with('reseller')->paginate(15)->withQueryString();

        return view('dashboard.admin-users', compact('users'));
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
     * Approve withdrawal request (manual transfer by admin)
     */
    public function approveWithdrawal(Request $request, \App\Models\WithdrawRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Withdrawal already processed.');
        }

        $user = $withdrawal->user;

        // Verify user has sufficient balance
        if ($user->token_balance < $withdrawal->token_amount) {
            return back()->with('error', 'User has insufficient balance.');
        }

        // Update withdrawal status
        $withdrawal->update(['status' => 'approved']);

        // Deduct tokens from user (manual transfer means admin handles external transfer)
        $user->deductTokens($withdrawal->token_amount, 'Withdrawal approved - manual transfer');

        // Log transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => (int) $withdrawal->token_amount,
            'status' => 'completed',
            'reference' => 'WDR-' . $withdrawal->id,
        ]);

        return back()->with('success', 'Withdrawal approved. Tokens deducted from user balance.');
    }

    /**
     * Display admin sell coins page
     */
    public function sellPage()
    {
        $admin = Auth::user();
        $defaultPrice = PriceHelper::getRwampPkrPrice();
        
        return view('dashboard.admin-sell', compact('defaultPrice'));
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
        $admin = Auth::user();
        $query = trim($request->input('q', ''));

        // Search ALL users and resellers (excluding admin)
        $usersQuery = User::where('id', '!=', $admin->id)
            ->whereIn('role', ['investor', 'reseller', 'user']);

        // Apply search filter if query is provided
        if (!empty($query)) {
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
                    'name' => $user->name,
                    'email' => $user->email,
                    'token_balance' => (float) ($user->token_balance ?? 0),
                    'role' => $user->role ?? 'investor',
                ];
            });

        return response()->json($users);
    }

    /**
     * Send OTP for admin sell
     */
    public function sendOtpForSell(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = \Illuminate\Support\Str::lower(trim($validated['email']));
        $admin = Auth::user();

        // Verify email matches admin's email
        if ($email !== \Illuminate\Support\Str::lower(trim($admin->email))) {
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
            \Log::error('Admin OTP send error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    /**
     * Process admin sell coins transaction
     */
    public function sellCoins(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'coin_quantity' => 'required|numeric|min:1',
            'price_per_coin' => 'required|numeric|min:0.01',
            'otp' => 'required|string|size:6',
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
        
        // Clean and normalize submitted OTP
        $rawOtp = (string) $validated['otp'];
        $otp = preg_replace('/\s+/', '', $rawOtp); // Remove all spaces
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT); // Pad to 6 digits
        
        // Normalize cached OTP
        $cachedOtp = $cachedOtpRaw ? str_pad((string) $cachedOtpRaw, 6, '0', STR_PAD_LEFT) : null;

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

            // Add tokens to recipient
            $recipient->token_balance = ($recipient->token_balance ?? 0) + $coinQuantity;
            $recipient->save();

            // Create transaction record for recipient (credit)
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
}



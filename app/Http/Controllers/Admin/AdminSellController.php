<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\CryptoPayment;
use App\Helpers\PriceHelper;
use App\Traits\GeneratesWalletAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminSellController extends Controller
{
    use GeneratesWalletAddress;

    /**
     * Display admin sell coins page
     */
    public function index(Request $request)
    {
        $admin = Auth::user();
        $defaultPrice = PriceHelper::getRwampPkrPrice();
        $userId = $request->query('user_id');
        
        $preSelectedUser = null;
        if ($userId) {
            // Handle both ULID and numeric ID
            // ULIDs are 26 characters, numeric IDs are shorter
            $isUlid = strlen($userId) === 26 && ctype_alnum($userId);
            
            $query = User::where('id', '!=', $admin->id)
                ->whereIn('role', ['investor', 'reseller', 'user']);
            
            if ($isUlid) {
                $query->where('ulid', $userId);
            } else {
                // Try as numeric ID
                $query->where('id', $userId);
            }
            
            $preSelectedUser = $query->select('id', 'ulid', 'name', 'email', 'token_balance', 'role', 'wallet_address')
                ->first();
            
            // Log for debugging
            if ($preSelectedUser) {
                Log::info('Pre-selected user found for admin sell', [
                    'user_id' => $preSelectedUser->id,
                    'ulid' => $preSelectedUser->ulid,
                    'name' => $preSelectedUser->name,
                    'email' => $preSelectedUser->email,
                    'has_wallet' => !empty($preSelectedUser->wallet_address),
                    'wallet_address' => $preSelectedUser->wallet_address ? '***' . substr($preSelectedUser->wallet_address, -4) : 'NULL',
                    'wallet_address_length' => $preSelectedUser->wallet_address ? strlen($preSelectedUser->wallet_address) : 0,
                    'requested_identifier' => $userId,
                    'identifier_type' => $isUlid ? 'ULID' : 'ID'
                ]);
            } else {
                Log::warning('Pre-selected user not found for admin sell', [
                    'requested_user_id' => $userId,
                    'identifier_type' => $isUlid ? 'ULID' : 'ID',
                    'admin_id' => $admin->id
                ]);
            }
        }
        
        return view('dashboard.admin-sell', compact('defaultPrice', 'preSelectedUser'));
    }

    /**
     * Search users for admin sell
     */
    public function searchUsers(Request $request)
    {
        try {
            $admin = Auth::user();
            $query = trim($request->input('q', ''));

            $usersQuery = User::where('id', '!=', $admin->id)
                ->whereIn('role', ['investor', 'reseller', 'user']);

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
            Log::error('Error in searchUsers: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed. Please try again.'], 500);
        }
    }

    /**
     * Send OTP for admin sell
     */
    public function sendOtp(Request $request)
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
            $generatedOtp = $otpController->generateAndSendOtp($email);

            // Verify OTP was stored correctly
            $cacheKey = "otp:{$email}";
            $storedOtp = Cache::get($cacheKey);
            $sessionOtp = session()->get("otp_debug_{$email}");
            
            Log::info('Admin OTP sent successfully', [
                'email' => $email,
                'admin_id' => $admin->id,
                'cache_key' => $cacheKey,
                'otp_stored_in_cache' => $storedOtp !== null,
                'otp_stored_in_session' => isset($sessionOtp['otp']),
                'cache_driver' => config('cache.default'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Admin OTP validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin OTP send error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP: ' . ($e->getMessage() ?? 'Unknown error. Please try again.')
            ], 500);
        }
    }

    /**
     * Fetch user's payment proof
     */
    public function fetchPaymentProof(Request $request)
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
            $cryptoPayment = CryptoPayment::where('user_id', $user->id)
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
            $cryptoPayment = CryptoPayment::where('user_id', $user->id)
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
     * Process admin sell coins transaction
     */
    public function store(Request $request)
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
        
        // Clean and normalize submitted OTP (already cleaned in validation, but ensure it's correct)
        $rawOtp = (string) $validated['otp'];
        $otp = preg_replace('/\s+/', '', $rawOtp); // Remove all spaces (in case validation didn't catch it)
        $otp = preg_replace('/[^0-9]/', '', $otp); // Remove any non-numeric characters
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT); // Pad to 6 digits
        
        // Check session fallback if cache is empty (works in both debug and production)
        if ($cachedOtpRaw === null) {
            $debugData = session()->get("otp_debug_{$normalizedEmail}");
            if ($debugData && isset($debugData['otp'])) {
                $cachedOtpRaw = $debugData['otp'];
                Log::info("Admin Sell OTP retrieved from session fallback", [
                    'email' => $normalizedEmail,
                    'cache_key' => $cacheKey,
                ]);
            }
        }
        
        // Normalize cached OTP
        $cachedOtp = $cachedOtpRaw ? str_pad((string) $cachedOtpRaw, 6, '0', STR_PAD_LEFT) : null;

        if (!$cachedOtp || $cachedOtp !== $otp) {
            Log::warning("Admin Sell OTP Verification Failed", [
                'email' => $normalizedEmail,
                'admin_email' => $admin->email,
                'submitted_otp' => $otp,
                'submitted_otp_raw' => $rawOtp,
                'cached_otp' => $cachedOtp,
                'cached_otp_raw' => $cachedOtpRaw,
                'cache_key' => $cacheKey,
                'cache_exists' => $cachedOtpRaw !== null,
                'cache_driver' => config('cache.default'),
                'session_has_otp' => session()->has("otp_debug_{$normalizedEmail}"),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP. Please check the code and try again, or request a new OTP.',
                'debug' => config('app.debug') ? [
                    'submitted_otp' => $otp,
                    'cached_otp' => $cachedOtp,
                    'cache_key' => $cacheKey,
                    'cache_exists' => $cachedOtpRaw !== null,
                ] : null
            ], 422);
        }

        // Calculate total price
        $coinQuantity = (float) $validated['coin_quantity'];
        $pricePerCoin = (float) $validated['price_per_coin'];
        $totalPrice = $coinQuantity * $pricePerCoin;

        // Handle payment receipt
        $paymentReceiptPath = null;
        if ($validated['payment_received'] === 'yes' && $validated['payment_type'] === 'bank' && !empty($validated['payment_receipt'])) {
            $paymentReceiptPath = $validated['payment_receipt'];
        }

        $paymentStatus = 'pending';
        if ($validated['payment_received'] === 'yes') {
            $paymentStatus = 'pending';
        }

        // Perform transfer in transaction
        try {
            DB::beginTransaction();

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

            DB::commit();

            Log::info('Admin sell coins completed', [
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
            DB::rollBack();
            Log::error('Admin sell coins error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer coins. Please try again.'
            ], 500);
        }
    }
}


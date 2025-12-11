<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResellerSellController extends Controller
{
    /**
     * Display sell coins page
     */
    public function index()
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
    public function searchUsers(Request $request)
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
     * Sell coins to user (OTP protected)
     */
    public function store(Request $request)
    {
        // Custom validation for OTP to handle spaces
        $request->merge([
            'otp' => preg_replace('/\s+/', '', (string) $request->input('otp', ''))
        ]);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'price_per_coin' => 'nullable|numeric|min:0.01',
            'otp' => 'required|string|size:6|regex:/^[0-9]{6}$/',
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

        // Verify email matches reseller's email
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));
        if ($normalizedEmail !== \Illuminate\Support\Str::lower(trim($reseller->email))) {
            return response()->json([
                'success' => false,
                'message' => 'Email does not match your account.'
            ], 422);
        }

        // Verify OTP with comprehensive logging
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
                Log::info("Reseller Sell OTP retrieved from session fallback", [
                    'email' => $normalizedEmail,
                    'cache_key' => $cacheKey,
                ]);
            }
        }
        
        // Normalize cached OTP
        $cachedOtp = $cachedOtpRaw ? str_pad((string) $cachedOtpRaw, 6, '0', STR_PAD_LEFT) : null;

        // Log OTP verification attempt
        Log::info("Reseller Sell - OTP Verification", [
            'email' => $normalizedEmail,
            'reseller_email' => $reseller->email,
            'submitted_otp' => $otp,
            'submitted_otp_raw' => $rawOtp,
            'submitted_otp_length' => strlen($otp),
            'cached_otp' => $cachedOtp,
            'cached_otp_length' => $cachedOtp ? strlen($cachedOtp) : 0,
            'cached_otp_raw' => $cachedOtpRaw,
            'cache_key' => $cacheKey,
            'cache_exists' => $cachedOtpRaw !== null,
            'cache_driver' => config('cache.default'),
            'session_has_otp' => session()->has("otp_debug_{$normalizedEmail}"),
            'comparison' => [
                'strict_match' => $cachedOtp === $otp,
                'loose_match' => $cachedOtp == $otp,
            ],
        ]);

        if (!$cachedOtp || $cachedOtp !== $otp) {
            Log::warning("Reseller Sell OTP Verification Failed", [
                'email' => $normalizedEmail,
                'reseller_email' => $reseller->email,
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
}


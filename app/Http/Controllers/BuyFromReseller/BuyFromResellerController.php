<?php

namespace App\Http\Controllers\BuyFromReseller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\BuyFromResellerRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuyFromResellerController extends Controller
{
    /**
     * Show buy from reseller page
     */
    public function index()
    {
        return view('dashboard.buy-from-reseller');
    }

    /**
     * Search resellers API
     */
    public function search(Request $request)
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
    public function sendOtp(Request $request)
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
     * Buy tokens from reseller (OTP protected) - Direct purchase
     */
    public function buy(Request $request)
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
     * Create buy from reseller request (requires reseller approval)
     */
    public function createRequest(Request $request)
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


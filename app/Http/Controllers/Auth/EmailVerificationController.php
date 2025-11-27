<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        $email = $request->query('email') ?? $request->session()->get('verification_email');

        if (!$email) {
            return redirect()->route('register')->with('error', __('Please register first.'));
        }

        // Normalize email
        $email = \Illuminate\Support\Str::lower(trim($email));

        // If OTP doesn't exist in cache, generate and send a new one
        $cacheKey = "otp:{$email}";
        if (!Cache::has($cacheKey)) {
            try {
                $this->generateAndSendOtp($email);
                Log::info("Generated and sent new OTP for email verification page: {$email}");
            } catch (\Exception $e) {
                Log::error("Failed to generate/send OTP on verification page", [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                // Continue anyway - in debug mode, OTP will be shown
            }
        }

        // Get OTP from cache for debugging (only in debug mode)
        $debugOtp = null;
        $cachedOtpForDebug = null;
        if (config('app.debug')) {
            $debugData = session()->get("otp_debug_{$email}");
            $debugOtp = $debugData['otp'] ?? null;
            
            // Also get from cache to compare
            $cachedOtpForDebug = Cache::get("otp:{$email}");
            
            // If debug OTP not in session but exists in cache, get it
            if (!$debugOtp && $cachedOtpForDebug) {
                $debugOtp = $cachedOtpForDebug;
            }
        }

        // Check if email is locked due to too many attempts
        $lockKey = "otp_lock:{$email}";
        if (Cache::has($lockKey)) {
            $remaining = Cache::get($lockKey);
            return view('auth.verify-email', [
                'email' => $email,
                'locked' => true,
                'lockRemaining' => $remaining,
                'debugOtp' => $debugOtp,
                'cachedOtp' => $cachedOtpForDebug,
            ]);
        }

        return view('auth.verify-email', [
            'email' => $email,
            'locked' => false,
            'debugOtp' => $debugOtp,
            'cachedOtp' => $cachedOtpForDebug,
        ]);
    }

    /**
     * Verify the OTP code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        // Log raw request data for debugging
        Log::info("OTP Verification - Raw Request Data", [
            'raw_otp' => $request->input('otp'),
            'raw_email' => $request->input('email'),
            'raw_otp_type' => gettype($request->input('otp')),
            'raw_otp_length' => strlen((string) $request->input('otp')),
            'all_inputs' => $request->all(),
        ]);

        $validated = $request->validate([
            'otp' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $email = \Illuminate\Support\Str::lower(trim($validated['email']));
        // Clean OTP: remove spaces and validate it's 6 digits
        $rawOtp = (string) $validated['otp'];
        $otp = preg_replace('/\s+/', '', $rawOtp);
        
        Log::info("OTP Verification - After Validation", [
            'raw_otp' => $rawOtp,
            'raw_otp_length' => strlen($rawOtp),
            'cleaned_otp' => $otp,
            'cleaned_otp_length' => strlen($otp),
            'email' => $email,
        ]);
        
        if (!preg_match('/^[0-9]{6}$/', $otp)) {
            Log::warning("OTP Validation Failed - Invalid Format", [
                'raw_otp' => $rawOtp,
                'cleaned_otp' => $otp,
                'length' => strlen($otp),
                'pattern_match' => preg_match('/^[0-9]{6}$/', $otp),
            ]);
            
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => __('The verification code must be exactly 6 digits.'),
                    'errors' => [
                        'otp' => [__('The verification code must be exactly 6 digits.')]
                    ]
                ], 422);
            }
            
            throw ValidationException::withMessages([
                'otp' => __('The verification code must be exactly 6 digits.'),
            ]);
        }

        // Normalize email for consistent cache key usage
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($email));
        
        // Check rate limiting
        $lockKey = "otp_lock:{$normalizedEmail}";
        if (Cache::has($lockKey)) {
            $remaining = Cache::get($lockKey);
            
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => __('Too many attempts. Please try again in :minutes minutes.', ['minutes' => ceil($remaining / 60)]),
                    'errors' => [
                        'otp' => [__('Too many attempts. Please try again in :minutes minutes.', ['minutes' => ceil($remaining / 60)])]
                    ]
                ], 429);
            }
            
            throw ValidationException::withMessages([
                'otp' => __('Too many attempts. Please try again in :minutes minutes.', ['minutes' => ceil($remaining / 60)]),
            ]);
        }

        // Check attempt rate limiting
        $attemptKey = "otp_attempts:{$normalizedEmail}";
        $attempts = Cache::get($attemptKey, 0);

        if ($attempts >= 3) {
            // Lock for 15 minutes
            Cache::put($lockKey, 900, now()->addMinutes(15));
            Cache::forget($attemptKey);
            Log::warning("OTP verification locked for email: {$normalizedEmail} after 3 failed attempts");

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => __('Too many failed attempts. Your email is locked for 15 minutes.'),
                    'errors' => [
                        'otp' => [__('Too many failed attempts. Your email is locked for 15 minutes.')]
                    ]
                ], 429);
            }

            throw ValidationException::withMessages([
                'otp' => __('Too many failed attempts. Your email is locked for 15 minutes.'),
            ]);
        }

        // Get OTP from cache - ensure it's a string and normalized
        $cacheKey = "otp:{$normalizedEmail}";
        $cachedOtpRaw = Cache::get($cacheKey);
        
        // Fallback: Check session if cache is empty (for debugging)
        if ($cachedOtpRaw === null && config('app.debug')) {
            $debugData = session()->get("otp_debug_{$normalizedEmail}");
            if ($debugData && isset($debugData['otp'])) {
                Log::warning("OTP not found in cache, using session fallback", [
                    'cache_key' => $cacheKey,
                    'session_otp' => $debugData['otp'],
                    'session_timestamp' => $debugData['timestamp'] ?? null,
                ]);
                // Use session OTP as fallback (only in debug mode)
                $cachedOtpRaw = $debugData['otp'];
            }
        }
        
        Log::info("OTP Verification - Cache Lookup", [
            'cache_key' => $cacheKey,
            'cached_otp_raw' => $cachedOtpRaw,
            'cached_otp_type' => gettype($cachedOtpRaw),
            'cached_otp_exists' => $cachedOtpRaw !== null,
            'cache_driver' => config('cache.default'),
        ]);
        
        // Convert to string and ensure it's exactly 6 digits
        $cachedOtp = $cachedOtpRaw ? str_pad((string) $cachedOtpRaw, 6, '0', STR_PAD_LEFT) : null;
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT);

        // Debug logging with actual values (for debugging only)
        Log::info("OTP Verification Attempt - DETAILED", [
            'email' => $normalizedEmail,
            'submitted_otp' => $otp,
            'submitted_otp_length' => strlen($otp),
            'submitted_otp_type' => gettype($otp),
            'cached_otp' => $cachedOtp,
            'cached_otp_length' => $cachedOtp ? strlen($cachedOtp) : 0,
            'cached_otp_type' => $cachedOtp ? gettype($cachedOtp) : 'null',
            'cached_otp_raw' => $cachedOtpRaw,
            'otp_exists' => $cachedOtp !== null,
            'otp_match' => $cachedOtp === $otp,
            'otp_match_strict' => $cachedOtp === $otp,
            'otp_match_loose' => $cachedOtp == $otp,
            'cache_key' => $cacheKey,
            'byte_comparison' => $cachedOtp && $otp ? [
                'submitted_bytes' => bin2hex($otp),
                'cached_bytes' => bin2hex($cachedOtp),
                'bytes_match' => bin2hex($otp) === bin2hex($cachedOtp),
            ] : null,
        ]);

        if (!$cachedOtp || $cachedOtp !== $otp) {
            // Increment attempts
            Cache::put($attemptKey, $attempts + 1, now()->addMinutes(15));
            $remainingAttempts = 3 - ($attempts + 1);

            Log::warning("Invalid OTP attempt for email: {$normalizedEmail}", [
                'submitted' => $otp,
                'submitted_length' => strlen($otp),
                'expected' => $cachedOtp,
                'expected_length' => $cachedOtp ? strlen($cachedOtp) : 0,
                'cached_raw' => $cachedOtpRaw,
                'remaining_attempts' => $remainingAttempts,
                'cache_key' => $cacheKey,
                'comparison_details' => [
                    'strict_match' => $cachedOtp === $otp,
                    'loose_match' => $cachedOtp == $otp,
                    'submitted_hex' => bin2hex($otp),
                    'cached_hex' => $cachedOtp ? bin2hex($cachedOtp) : 'null',
                ],
            ]);

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                $errorResponse = [
                    'message' => __('Invalid or expired code. :remaining attempts remaining.', ['remaining' => $remainingAttempts]),
                    'errors' => [
                        'otp' => [__('Invalid or expired code. :remaining attempts remaining.', ['remaining' => $remainingAttempts])]
                    ]
                ];
                
                // Add debug info in debug mode
                if (config('app.debug')) {
                    $errorResponse['debug'] = [
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
                    ];
                }
                
                return response()->json($errorResponse, 422);
            }

            throw ValidationException::withMessages([
                'otp' => __('Invalid or expired code. :remaining attempts remaining.', ['remaining' => $remainingAttempts]),
            ]);
        }

        // OTP is valid - verify the user's email
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => __('User not found.'),
                    'errors' => [
                        'otp' => [__('User not found.')]
                    ]
                ], 422);
            }

            throw ValidationException::withMessages([
                'otp' => __('User not found.'),
            ]);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        // Clear OTP and attempts from cache
        Cache::forget("otp:{$normalizedEmail}");
        Cache::forget($attemptKey);
        Cache::forget($lockKey);

        Log::info("Email verified successfully for user: {$normalizedEmail}");

        // Auto-login the user
        Auth::login($user);

        // Check if reseller is using default password (first-time login after approval)
        if ($user->role === 'reseller') {
            $defaultPassword = 'RWAMP@agent';
            // Check if the password matches the default password
            if (\Illuminate\Support\Facades\Hash::check($defaultPassword, $user->password)) {
                // Set password reset required flag in cache
                Cache::put('password_reset_required_user_' . $user->id, true, now()->addDays(30));
                
                // Clear intended action since password reset takes priority
                $request->session()->forget('intended_action');
                
                // Check if this is an AJAX request
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => __('Email verified successfully! Please set your password.'),
                        'redirect' => route('password.change.required'),
                    ]);
                }
                
                return redirect()->route('password.change.required')
                    ->with('success', __('Email verified successfully! Please set your password for security.'));
            }
        }

        // Check if password reset is required (from cache flag)
        if (Cache::get('password_reset_required_user_' . $user->id)) {
            // Clear intended action since password reset takes priority
            $request->session()->forget('intended_action');
            
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Email verified successfully! Please set your password.'),
                    'redirect' => route('password.change.required'),
                ]);
            }
            
            return redirect()->route('password.change.required')
                ->with('success', __('Email verified successfully! Please set your password.'));
        }

        // Redirect to dashboard based on role
        $dashboardRoute = match ($user->role) {
            'admin' => 'dashboard.admin',
            'reseller' => 'dashboard.reseller',
            'investor' => 'dashboard.investor',
            default => 'kyc.show',
        };

        // Check if user intended to open purchase modal (from session)
        $intendedAction = $request->session()->get('intended_action');
        $redirectUrl = route($dashboardRoute);
        
        // Always open purchase modal after email verification (unless KYC is required or user is admin)
        if ($user->role === 'admin') {
            // Admin users should not see purchase modal
            if ($intendedAction === 'purchase') {
                $request->session()->forget('intended_action');
            }
            $redirectUrl = route($dashboardRoute);
        } elseif ($user->role !== 'user' || $dashboardRoute !== 'kyc.show') {
            if ($intendedAction === 'purchase') {
                $request->session()->forget('intended_action');
            }
            $redirectUrl = route($dashboardRoute) . '?open=purchase';
        } elseif ($intendedAction === 'purchase') {
            $request->session()->forget('intended_action');
            $redirectUrl = route($dashboardRoute) . '?open=purchase';
        }

        // Check if this is an AJAX request
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Email verified successfully!'),
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', __('Email verified successfully!'));
    }

    /**
     * Resend the OTP code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));

        // Check if email is locked
        $lockKey = "otp_lock:{$normalizedEmail}";
        if (Cache::has($lockKey)) {
            $remaining = Cache::get($lockKey);
            return back()->withErrors([
                'email' => __('Too many attempts. Please try again in :minutes minutes.', ['minutes' => ceil($remaining / 60)]),
            ]);
        }

        // Check resend rate limiting (max 1 resend per 60 seconds)
        $resendKey = "otp_resend:{$normalizedEmail}";
        if (Cache::has($resendKey)) {
            $remaining = Cache::get($resendKey);
            return back()->withErrors([
                'email' => __('Please wait :seconds seconds before requesting a new code.', ['seconds' => $remaining]),
            ]);
        }

        // Generate and send OTP
        $this->generateAndSendOtp($normalizedEmail);

        // Set resend cooldown (60 seconds)
        Cache::put($resendKey, 60, now()->addSeconds(60));

        return back()->with('success', __('A new verification code has been sent to your email.'));
    }

    /**
     * Generate and send OTP to email (helper method)
     *
     * @param  string  $email
     * @return string The generated OTP
     */
    public function generateAndSendOtp(string $email): string
    {
        // Normalize email
        $normalizedEmail = \Illuminate\Support\Str::lower(trim($email));

        // Generate new 6-digit OTP (ensure it's always a string)
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 10 minutes
        $cacheKey = "otp:{$normalizedEmail}";
        
        // Store in cache with explicit expiration
        $expiresAt = now()->addMinutes(10);
        Cache::put($cacheKey, $otp, $expiresAt);
        
        // Also store in session as backup (only in debug mode)
        if (config('app.debug')) {
            session()->put("otp_debug_{$normalizedEmail}", [
                'otp' => $otp,
                'timestamp' => now()->toIso8601String(),
                'email' => $normalizedEmail,
                'expires_at' => $expiresAt->toIso8601String(),
            ]);
        }
        
        // Verify it was stored correctly - try multiple times to ensure cache is working
        $storedOtp = Cache::get($cacheKey);
        $cacheExists = Cache::has($cacheKey);
        
        Log::info("OTP generated and stored - DETAILED", [
            'email' => $normalizedEmail,
            'otp' => $otp,
            'otp_length' => strlen($otp),
            'otp_type' => gettype($otp),
            'cache_key' => $cacheKey,
            'cache_driver' => config('cache.default'),
            'expires_at' => $expiresAt->toIso8601String(),
            'stored_otp' => $storedOtp,
            'stored_otp_type' => gettype($storedOtp),
            'cache_exists' => $cacheExists,
            'storage_verified' => $storedOtp === $otp,
            'storage_verified_loose' => $storedOtp == $otp,
        ]);
        
        // If cache storage failed, log warning
        if (!$cacheExists || $storedOtp !== $otp) {
            Log::error("OTP cache storage verification failed!", [
                'cache_key' => $cacheKey,
                'expected_otp' => $otp,
                'stored_otp' => $storedOtp,
                'cache_exists' => $cacheExists,
                'cache_driver' => config('cache.default'),
            ]);
        }

        // Send OTP email
        try {
            Mail::to($normalizedEmail)->send(new VerifyOtpMail($otp));
            Log::info("OTP sent to email: {$normalizedEmail}", [
                'otp' => $otp,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_from' => config('mail.from.address'),
            ]);
            
            // Store OTP in session for debugging (only in debug mode)
            if (config('app.debug')) {
                session()->put("otp_debug_{$normalizedEmail}", [
                    'otp' => $otp,
                    'timestamp' => now()->toIso8601String(),
                    'email' => $normalizedEmail,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email to {$normalizedEmail}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_from' => config('mail.from.address'),
            ]);
            
            // If in debug mode, still allow the process to continue and show OTP on page
            if (config('app.debug')) {
                session()->put("otp_debug_{$normalizedEmail}", [
                    'otp' => $otp,
                    'timestamp' => now()->toIso8601String(),
                    'email' => $normalizedEmail,
                    'email_send_failed' => true,
                    'email_error' => $e->getMessage(),
                ]);
                Log::warning("Email sending failed but continuing in debug mode. OTP: {$otp}");
            } else {
                // In production, throw the error
                throw $e;
            }
        }

        return $otp;
    }
}


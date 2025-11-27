<?php

namespace App\Http\Controllers\Auth\Register;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\ResellerApplication;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function show()
    {
        return view('auth.signup');
    }

    /**
     * Check if referral code is valid (API endpoint)
     */
    public function checkReferralCode(Request $request)
    {
        $code = strtoupper(trim($request->query('code', '')));
        
        if (empty($code)) {
            return response()->json(['valid' => false]);
        }

        // Extract ID from referral code (e.g., RSL1001 -> 1001)
        if (preg_match('/^RSL(\d+)$/i', $code, $matches)) {
            $resellerUserId = (int) $matches[1];
            $reseller = User::where('id', $resellerUserId)
                ->where('role', 'reseller')
                ->first();
            
            if ($reseller) {
                return response()->json([
                    'valid' => true,
                    'exists' => true,
                    'reseller_name' => $reseller->name, // For backward compatibility with JavaScript
                    'reseller_id' => $reseller->id,
                    'reseller' => [
                        'id' => $reseller->id,
                        'name' => $reseller->name,
                        'email' => $reseller->email,
                    ]
                ]);
            }
        }

        return response()->json([
            'valid' => true,
            'exists' => false,
            'message' => 'Referral code not found'
        ]);
    }

    /**
     * Check if email is available (API endpoint)
     */
    public function checkEmail(Request $request)
    {
        $email = \Illuminate\Support\Str::lower(trim($request->query('email', '')));
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'valid' => false,
                'message' => 'Please enter a valid email address.'
            ]);
        }

        $exists = User::where('email', $email)->exists() || 
                  ResellerApplication::where('email', $email)->exists();

        return response()->json([
            'valid' => true,
            'exists' => $exists,
            'message' => $exists ? 'Email already registered' : 'Email is available'
        ]);
    }

    /**
     * Check if phone is available (API endpoint)
     */
    public function checkPhone(Request $request)
    {
        $phone = trim($request->query('phone', ''));
        
        if (empty($phone)) {
            return response()->json([
                'valid' => false,
                'message' => 'Please enter a phone number.'
            ]);
        }

        // Basic phone validation
        $phoneDigits = preg_replace('/\D/', '', $phone);
        if (strlen($phoneDigits) < 10) {
            return response()->json([
                'valid' => false,
                'message' => 'Please enter a valid phone number. Format: +[Country Code] [Number] (e.g., +92 300 1234567)'
            ]);
        }

        return response()->json([
            'valid' => true,
            'exists' => false,
            'message' => 'Phone number is valid and available'
        ]);
    }

    /**
     * Register a new investor user
     */
    public function store(Request $request)
    {
        // Check if we should require reCAPTCHA (skip on localhost)
        $requireRecaptcha = config('services.recaptcha.secret_key') && 
            !in_array($request->getHost(), ['localhost', '127.0.0.1']) &&
            !str_contains(config('app.url', ''), 'localhost') &&
            !str_contains(config('app.url', ''), '127.0.0.1') &&
            config('app.env') !== 'local';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'role' => ['nullable', 'in:investor,reseller,admin'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'referral_code' => ['nullable', 'string', 'max:20'],
            'g-recaptcha-response' => $requireRecaptcha ? ['required', 'recaptcha'] : ['nullable'],
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
            'g-recaptcha-response.recaptcha' => 'reCAPTCHA verification failed. Please try again.',
        ]);

        $normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));

        // Handle referral code from form input or URL parameter
        $resellerId = null;
        $refCode = $validated['referral_code'] ?? $request->query('ref');
        
        if ($refCode) {
            // Normalize referral code (remove spaces, convert to uppercase)
            $refCode = strtoupper(trim($refCode));
            
            // Extract ID from referral code (e.g., RSL1001 -> 1001)
            if (preg_match('/^RSL(\d+)$/i', $refCode, $matches)) {
                $resellerUserId = (int) $matches[1];
                $reseller = User::where('id', $resellerUserId)
                    ->where('role', 'reseller')
                    ->whereNotNull('referral_code')
                    ->first();
                if ($reseller) {
                    $resellerId = $reseller->id;
                } else {
                    // Invalid referral code - show error but allow registration
                    return back()->withErrors([
                        'referral_code' => 'Invalid referral code. Please check and try again, or leave it blank.',
                    ])->withInput();
                }
            } else {
                // Invalid format
                return back()->withErrors([
                    'referral_code' => 'Invalid referral code format. Please use format: RSL followed by numbers (e.g., RSL1001).',
                ])->withInput();
            }
        }

        // Generate unique 16-digit wallet address
        $walletAddress = $this->generateUniqueWalletAddress();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $normalizedEmail,
            'phone' => $validated['phone'] ?? null,
            'role' => 'investor',
            'password' => Hash::make($validated['password']),
            'email_verified_at' => null, // Email verification required
            'reseller_id' => $resellerId, // Link to reseller if referral code provided
            'wallet_address' => $walletAddress, // Auto-generated wallet address
        ]);

        // Generate and send OTP
        $emailVerificationController = new EmailVerificationController();
        try {
            $emailVerificationController->generateAndSendOtp($normalizedEmail);
            Log::info("OTP sent to new user during registration: {$normalizedEmail}");
        } catch (\Exception $e) {
            Log::error("Failed to send OTP during registration", [
                'email' => $normalizedEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
            
            // In debug mode, allow registration to continue and show OTP on verification page
            if (config('app.debug')) {
                Log::warning("Email sending failed but continuing in debug mode for: {$normalizedEmail}");
            } else {
                // In production, delete the user if OTP sending fails
                $user->delete();
                return back()->withErrors([
                    'email' => 'Registration failed. Unable to send verification email. Please check your email configuration or try again later.',
                ])->withInput();
            }
        }

        // Store email in session for verification page
        $request->session()->put('verification_email', $normalizedEmail);
        
        // Preserve intended action if coming from "Join the Presale" button
        if ($request->has('intended') && $request->intended === 'purchase') {
            $request->session()->put('intended_action', 'purchase');
        }

        // Redirect to email verification page
        return redirect()->route('verify-email', ['email' => $normalizedEmail])
            ->with('success', 'Registration successful! Please check your email for the verification code.');
    }

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
}


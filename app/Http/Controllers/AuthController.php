<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\User;
use App\Services\TabAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
	public function showLogin(Request $request)
	{
		// Store intended action in session if provided
		if ($request->has('intended') && $request->intended === 'purchase') {
			$request->session()->put('intended_action', 'purchase');
		}
		return view('auth.login');
	}

	public function showForgotPassword()
	{
		return view('auth.passwords.email');
	}

	public function login(Request $request)
	{
		// Check if we should require reCAPTCHA (skip on localhost)
		$requireRecaptcha = config('services.recaptcha.secret_key') && 
			!in_array($request->getHost(), ['localhost', '127.0.0.1']) &&
			!str_contains(config('app.url', ''), 'localhost') &&
			!str_contains(config('app.url', ''), '127.0.0.1') &&
			config('app.env') !== 'local';

		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
			'role' => ['nullable', 'in:investor,reseller,admin'],
			'remember' => ['nullable', 'boolean'],
			'g-recaptcha-response' => $requireRecaptcha ? ['required', 'recaptcha'] : ['nullable'],
		], [
			'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
			'g-recaptcha-response.recaptcha' => 'reCAPTCHA verification failed. Please try again.',
		]);

		// Check if user exists first to determine if role selection is required
		$user = User::where('email', $credentials['email'])->first();
		
		// Validate role selection for non-admin users
		if ($user && $user->role !== 'admin') {
			if (empty($credentials['role']) || !in_array($credentials['role'], ['investor', 'reseller'])) {
				return back()->withErrors([
					'role' => 'Please select your role (Investor or Reseller) before logging in.',
				])->onlyInput('email');
			}
			
			// Validate that selected role matches user's actual role
			if ($credentials['role'] !== $user->role) {
				return back()->withErrors([
					'role' => 'The selected role does not match your account type. Please select the correct role.',
				])->onlyInput('email');
			}
		}

		// Attempt login with remember me functionality
		$remember = $request->has('remember') && $request->input('remember') == '1';
		if (Auth::attempt($request->only('email','password'), $remember)) {
			$request->session()->regenerate();
			$user = Auth::user();
			
			// Store tab-specific authentication if tab session ID exists
			$tabId = $request->cookie('tab_session_id');
			if ($tabId) {
				TabAuthService::setTabUser($tabId, $user->id, 24);
			}
			
			// Check if email is verified
			if (!$user->email_verified_at) {
				Auth::logout();
				$request->session()->invalidate();
				$request->session()->regenerateToken();
				
				// Generate and send OTP
				$emailVerificationController = new EmailVerificationController();
				try {
					$normalizedEmail = \Illuminate\Support\Str::lower(trim($user->email));
					$emailVerificationController->generateAndSendOtp($normalizedEmail);
					$request->session()->put('verification_email', $normalizedEmail);
					Log::info("OTP sent to unverified user during login: {$normalizedEmail}");
				} catch (\Exception $e) {
					Log::error("Failed to send OTP during login: " . $e->getMessage());
					return back()->withErrors([
						'email' => 'Your email is not verified. We tried to send a verification code but failed. Please contact support.',
					])->onlyInput('email');
				}
				
				// Preserve intended action if present
				$intendedAction = $request->session()->get('intended_action');
				if (!$intendedAction && $request->has('intended') && $request->intended === 'purchase') {
					$request->session()->put('intended_action', 'purchase');
				}
				
				return redirect()->route('verify-email', ['email' => $normalizedEmail])
					->with('error', 'Please verify your email address before logging in. A verification code has been sent to your email.');
			}
			
			// Check if reseller is using default password (first-time login)
			if ($user->role === 'reseller') {
				$defaultPassword = 'RWAMP@agent';
				// Check if the password matches the default password
				if (Hash::check($defaultPassword, $user->password)) {
					// Set password reset required flag in cache
					Cache::put('password_reset_required_user_' . $user->id, true, now()->addDays(30));
					return redirect()->route('password.change.required')
						->with('warning', 'Please set your own password for security. This is your first login.');
				}
			}
			
			// If one-time password is set, force password change by redirecting to dedicated change password page
			if (Cache::get('password_reset_required_user_'.$user->id)) {
				return redirect()->route('password.change.required');
			}
			return $this->redirectByRole($user, $credentials['role'] ?? null, $request);
		}

		return back()->withErrors([
			'email' => 'The provided credentials do not match our records.',
		])->onlyInput('email');
	}

	public function showRegister()
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

		// Validate format
		if (!preg_match('/^RSL(\d+)$/i', $code, $matches)) {
			return response()->json(['valid' => false]);
		}

		$resellerUserId = (int) $matches[1];
		$reseller = User::where('id', $resellerUserId)
			->where('role', 'reseller')
			->first();

		if ($reseller) {
			return response()->json([
				'valid' => true,
				'reseller_name' => $reseller->name,
				'reseller_id' => $reseller->id,
				'reseller_email' => $reseller->email,
			]);
		}

		return response()->json(['valid' => false, 'message' => 'Referral code not found']);
	}

	/**
	 * Check if email is valid and exists (API endpoint)
	 */
	public function checkEmail(Request $request)
	{
		$email = strtolower(trim($request->query('email', '')));
		
		if (empty($email)) {
			return response()->json(['valid' => false, 'message' => 'Email is required']);
		}

		// Validate email format
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return response()->json([
				'valid' => false,
				'exists' => false,
				'message' => 'Please enter a valid email address format'
			]);
		}

		// Check if email already exists in users or reseller_applications
		$existsInUsers = User::where('email', $email)->exists();
		$existsInApplications = \App\Models\ResellerApplication::where('email', $email)->exists();

		if ($existsInUsers || $existsInApplications) {
			return response()->json([
				'valid' => true,
				'exists' => true,
				'message' => 'This email address is already registered'
			]);
		}

		// Check if email domain exists (DNS check)
		$domain = substr(strrchr($email, "@"), 1);
		$dnsValid = checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');

		if (!$dnsValid) {
			return response()->json([
				'valid' => false,
				'exists' => false,
				'message' => 'Please enter a valid email address. The domain does not exist.'
			]);
		}

		return response()->json([
			'valid' => true,
			'exists' => false,
			'message' => 'Email address is valid and available'
		]);
	}

	/**
	 * Check if phone number is valid (API endpoint)
	 */
	public function checkPhone(Request $request)
	{
		$phone = trim($request->query('phone', ''));
		
		if (empty($phone)) {
			return response()->json(['valid' => false, 'message' => 'Phone number is required']);
		}

		// Normalize phone: remove all spaces and non-digit characters except +
		$normalized = preg_replace('/[^\d+]/', '', $phone);
		
		// Validate phone format: should start with + and contain digits
		// Format: +[country code][number] - allows flexible spacing in input
		// After normalization, should be: +[1-4 digits][4-14 digits]
		// General pattern: +[1-9][0-9]{0,3}[0-9]{4,14} (country code 1-4 digits, number 4-14 digits)
		$phonePattern = '/^\+[1-9]\d{0,3}\d{4,14}$/';
		
		// Also check Pakistan-specific format: +92 followed by 10 digits
		$pakistanPattern = '/^\+92\d{10}$/';
		
		if (!preg_match($phonePattern, $normalized) && !preg_match($pakistanPattern, $normalized)) {
			return response()->json([
				'valid' => false,
				'message' => 'Please enter a valid phone number. Format: +[Country Code] [Number] (e.g., +92 300 1234567)'
			]);
		}

		// Check if phone already exists (check both original and normalized formats)
		$existsInUsers = User::where('phone', $phone)
			->orWhere('phone', $normalized)
			->exists();
		$existsInApplications = \App\Models\ResellerApplication::where('phone', $phone)
			->orWhere('phone', $normalized)
			->exists();

		if ($existsInUsers || $existsInApplications) {
			return response()->json([
				'valid' => true,
				'exists' => true,
				'message' => 'This phone number is already registered'
			]);
		}

		return response()->json([
			'valid' => true,
			'exists' => false,
			'message' => 'Phone number is valid and available'
		]);
	}

	public function register(Request $request)
	{
		$role = $request->input('role', 'investor');
		
		// Handle reseller applications differently
		if ($role === 'reseller') {
			return $this->registerResellerApplication($request);
		}
		
		// Check if we should require reCAPTCHA (skip on localhost)
		$requireRecaptcha = config('services.recaptcha.secret_key') && 
			!in_array($request->getHost(), ['localhost', '127.0.0.1']) &&
			!str_contains(config('app.url', ''), 'localhost') &&
			!str_contains(config('app.url', ''), '127.0.0.1') &&
			config('app.env') !== 'local';

		// Handle investor registration (existing flow)
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
	 * Handle reseller application registration
	 */
	private function registerResellerApplication(Request $request)
	{
		try {
			// Check if we should require reCAPTCHA (skip on localhost)
			$requireRecaptcha = config('services.recaptcha.secret_key') && 
				!in_array($request->getHost(), ['localhost', '127.0.0.1']) &&
				!str_contains(config('app.url', ''), 'localhost') &&
				!str_contains(config('app.url', ''), '127.0.0.1') &&
				config('app.env') !== 'local';

			$validated = $request->validate([
				'name' => ['required', 'string', 'max:255'],
				'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'unique:reseller_applications,email'],
				'phone' => ['required', 'string', 'max:30'],
				'password' => ['required', 'confirmed', Password::min(8)],
				'company_name' => ['nullable', 'string', 'max:255'],
				'investment_capacity' => ['required', 'string', 'max:50'],
				'experience' => ['nullable', 'string', 'max:2000'],
				'g-recaptcha-response' => $requireRecaptcha ? ['required', 'recaptcha'] : ['nullable'],
			], [
				'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
				'g-recaptcha-response.recaptcha' => 'reCAPTCHA verification failed. Please try again.',
			]);

			$normalizedEmail = \Illuminate\Support\Str::lower(trim($validated['email']));

			// Create reseller application (not a user account)
			try {
				$application = \App\Models\ResellerApplication::create([
					'name' => $validated['name'],
					'email' => $normalizedEmail,
					'phone' => $validated['phone'],
					'password' => Hash::make($validated['password']), // Store hashed password
					'company' => $validated['company_name'] ?? null,
					'investment_capacity' => $validated['investment_capacity'],
					'experience' => $validated['experience'] ?? null,
					'status' => 'pending',
					'ip_address' => $request->ip(),
					'user_agent' => $request->userAgent(),
				]);

				Log::info("Reseller application created successfully", [
					'application_id' => $application->id,
					'email' => $normalizedEmail,
				]);
			} catch (\Illuminate\Database\QueryException $e) {
				Log::error("Database error creating reseller application: " . $e->getMessage(), [
					'error_code' => $e->getCode(),
					'sql_state' => $e->errorInfo[0] ?? null,
					'email' => $normalizedEmail,
				]);
				
				// Check if it's a table missing error
				if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Unknown table")) {
					return back()->withErrors([
						'email' => 'Database configuration error. Please contact support. Error: Table missing.',
					])->withInput();
				}
				
				throw $e; // Re-throw if it's a different database error
			}

			// Send notification emails (non-blocking)
			try {
				$emailService = new \App\Services\EmailService();
				$emailService->sendResellerNotification($application);
				Log::info("Reseller application notification email sent", [
					'application_id' => $application->id,
				]);
			} catch (\Exception $e) {
				Log::error("Failed to send reseller application notification: " . $e->getMessage(), [
					'application_id' => $application->id ?? null,
					'error' => $e->getMessage(),
					'trace' => $e->getTraceAsString(),
				]);
				// Continue even if email fails - application is already saved
			}

			// Show success message and redirect back
			return redirect()->route('register')
				->with('success', 'Your reseller application has been submitted successfully! Our admin team will review your application and notify you via email once a decision has been made. Please check your email for updates.');
				
		} catch (\Illuminate\Validation\ValidationException $e) {
			// Re-throw validation exceptions to show form errors
			throw $e;
		} catch (\Exception $e) {
			Log::error("Unexpected error in registerResellerApplication: " . $e->getMessage(), [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trace' => $e->getTraceAsString(),
				'request_data' => $request->except(['password', 'password_confirmation']),
			]);
			
			return back()->withErrors([
				'email' => 'An unexpected error occurred. Please try again later or contact support if the problem persists.',
			])->withInput();
		}
	}

	public function logout(Request $request)
	{
		$tabId = $request->cookie('tab_session_id');

		// If this request is tied to a tab session, clear only that tab's mapping by default.
		if ($tabId) {
			TabAuthService::clearTabUser($tabId);

			// If caller explicitly wants a global logout, clear the main session as well.
			if ($request->boolean('clear_all')) {
				Auth::guard('web')->logout();
				$request->session()->invalidate();
				$request->session()->regenerateToken();
			}
		} else {
			// Legacy behaviour (no per-tab cookie): log out completely.
			Auth::guard('web')->logout();
			$request->session()->invalidate();
			$request->session()->regenerateToken();
		}

		return redirect()->route('home');
	}

	public function showChangePasswordRequired()
	{
		$user = Auth::user();
		if (!$user) {
			return redirect()->route('login');
		}
		
		// Check if reseller is using default password
		$requiresPasswordChange = false;
		if ($user->role === 'reseller') {
			$defaultPassword = 'RWAMP@agent';
			if (Hash::check($defaultPassword, $user->password)) {
				$requiresPasswordChange = true;
				// Set cache flag
				Cache::put('password_reset_required_user_' . $user->id, true, now()->addDays(30));
			}
		}
		
		// Also check cache flag
		if (!$requiresPasswordChange && !Cache::get('password_reset_required_user_'.$user->id)) {
			// Redirect based on role
			$dashboardRoute = match ($user->role) {
				'admin' => 'dashboard.admin',
				'reseller' => 'dashboard.reseller',
				'investor' => 'dashboard.investor',
				default => 'home',
			};
			return redirect()->route($dashboardRoute);
		}
		
		return view('auth.change-password-required');
	}

	public function changePasswordRequired(Request $request)
	{
		$user = Auth::user();
		
		if (!$user) {
			return redirect()->route('login');
		}
		
		// Check if reseller is using default password
		$requiresPasswordChange = false;
		if ($user->role === 'reseller') {
			$defaultPassword = 'RWAMP@agent';
			if (Hash::check($defaultPassword, $user->password)) {
				$requiresPasswordChange = true;
				// Set cache flag
				Cache::put('password_reset_required_user_' . $user->id, true, now()->addDays(30));
			}
		}
		
		// Also check cache flag
		if (!$requiresPasswordChange && !Cache::get('password_reset_required_user_'.$user->id)) {
			// Redirect based on role
			$dashboardRoute = match ($user->role) {
				'admin' => 'dashboard.admin',
				'reseller' => 'dashboard.reseller',
				'investor' => 'dashboard.investor',
				default => 'home',
			};
			return redirect()->route($dashboardRoute);
		}

		$validated = $request->validate([
			'current_password' => ['required'],
			'password' => ['required', 'confirmed', Password::min(8)],
		]);

		// Verify current password (temporary password)
		if (!Hash::check($validated['current_password'], $user->password)) {
			return back()->withErrors(['current_password' => 'The current password is incorrect.']);
		}

		// Update password
		$user->update([
			'password' => Hash::make($validated['password']),
		]);

		// Clear the password reset required flag
		\Illuminate\Support\Facades\Cache::forget('password_reset_required_user_'.$user->id);

		return redirect()->route('dashboard.reseller')->with('success', 'Password changed successfully! You can now access your dashboard.');
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

	private function redirectByRole(User $user, ?string $intendedRole = null, ?Request $request = null)
	{
		$role = $intendedRole ?? $user->role;

		// When a guest visits an auth-protected URL (e.g. /open-purchase),
		// Laravel stores the intended URL in session. After login/signup,
		// we should prefer redirecting to that intended URL so the original
		// action can continue (which will then route to the dashboard and open the modal).
		$defaultDashboard = match ($role) {
			'admin' => route('dashboard.admin'),
			'reseller' => route('dashboard.reseller'),
			'investor' => route('dashboard.investor'),
			default => route('kyc.show'), // New users start as 'user' and must complete KYC
		};

		// Check if user intended to open purchase modal
		$intendedAction = $request ? $request->session()->get('intended_action') : null;
		if ($intendedAction === 'purchase') {
			$request->session()->forget('intended_action');
			// Don't open purchase modal for admin users
			if ($role !== 'admin') {
				return redirect()->to($defaultDashboard . '?open=purchase');
			}
			return redirect()->to($defaultDashboard);
		}

		// Check if there's an intended URL from Laravel's auth system
		$intendedUrl = null;
		if ($request && $request->session()->has('url.intended')) {
			$intendedUrl = $request->session()->get('url.intended');
		}
		
		// If there's no specific intended URL (user logged in directly),
		// automatically open the purchase modal (except for admin users)
		if (!$intendedUrl || $intendedUrl === $defaultDashboard) {
			if ($role !== 'admin') {
				return redirect()->to($defaultDashboard . '?open=purchase');
			}
			return redirect()->to($defaultDashboard);
		}

		// If there's a specific intended URL, use it
		return redirect()->intended($defaultDashboard);
	}
}



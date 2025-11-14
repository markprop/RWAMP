<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
			'role' => ['nullable', 'in:investor,reseller,admin'],
		]);

		if (Auth::attempt($request->only('email','password'), $request->boolean('remember'))) {
			$request->session()->regenerate();
			$user = Auth::user();
			
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
			->whereNotNull('referral_code')
			->first();

		if ($reseller) {
			return response()->json([
				'valid' => true,
				'reseller_name' => $reseller->name,
				'reseller_id' => $reseller->id
			]);
		}

		return response()->json(['valid' => false]);
	}

	public function register(Request $request)
	{
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
			'phone' => ['nullable', 'string', 'max:30'],
			'role' => ['nullable', 'in:investor,reseller,admin'],
			'company_name' => ['nullable', 'string', 'max:255'],
			'investment_capacity' => ['nullable', 'string', 'max:50'],
			'experience' => ['nullable', 'string', 'max:2000'],
			'password' => ['required', 'confirmed', Password::min(8)],
			'referral_code' => ['nullable', 'string', 'max:20'],
		]);

		$role = $validated['role'] ?? 'user';
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

		$user = User::create([
			'name' => $validated['name'],
			'email' => $normalizedEmail,
			'phone' => $validated['phone'] ?? null,
			'role' => $role,
			'company_name' => $role === 'reseller' ? ($validated['company_name'] ?? null) : null,
			'investment_capacity' => $role === 'reseller' ? ($validated['investment_capacity'] ?? null) : null,
			'experience' => $role === 'reseller' ? ($validated['experience'] ?? null) : null,
			'password' => Hash::make($validated['password']),
			'email_verified_at' => null, // Email verification required
			'reseller_id' => $resellerId, // Link to reseller if referral code provided
		]);

		// Generate and send OTP
		$emailVerificationController = new EmailVerificationController();
		try {
			$emailVerificationController->generateAndSendOtp($normalizedEmail);
			Log::info("OTP sent to new user during registration: {$normalizedEmail}");
		} catch (\Exception $e) {
			Log::error("Failed to send OTP during registration: " . $e->getMessage());
			// Delete the user if OTP sending fails
			$user->delete();
			return back()->withErrors([
				'email' => 'Registration failed. Unable to send verification email. Please try again later.',
			])->withInput();
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

	public function logout(Request $request)
	{
		Auth::guard('web')->logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();
		return redirect()->route('home');
	}

	public function showChangePasswordRequired()
	{
		$user = Auth::user();
		if (!$user || !\Illuminate\Support\Facades\Cache::get('password_reset_required_user_'.$user->id)) {
			return redirect()->route('dashboard.reseller');
		}
		return view('auth.change-password-required');
	}

	public function changePasswordRequired(Request $request)
	{
		$user = Auth::user();
		
		if (!$user || !\Illuminate\Support\Facades\Cache::get('password_reset_required_user_'.$user->id)) {
			return redirect()->route('dashboard.reseller');
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
			return redirect()->to($defaultDashboard . '?open=purchase');
		}

		// Check if there's an intended URL from Laravel's auth system
		$intendedUrl = null;
		if ($request && $request->session()->has('url.intended')) {
			$intendedUrl = $request->session()->get('url.intended');
		}
		
		// If there's no specific intended URL (user logged in directly),
		// automatically open the purchase modal
		if (!$intendedUrl || $intendedUrl === $defaultDashboard) {
			return redirect()->to($defaultDashboard . '?open=purchase');
		}

		// If there's a specific intended URL, use it
		return redirect()->intended($defaultDashboard);
	}
}



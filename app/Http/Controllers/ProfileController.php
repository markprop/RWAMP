<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
	public function show()
	{
		$user = Auth::user()->load('reseller');
		$transactions = method_exists($user, 'transactions') ? $user->transactions()->latest()->limit(20)->get() : collect();
		
		// Get official coin price
		$officialPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();
		
		return view('auth.profile', [
			'user' => $user,
			'transactions' => $transactions,
			'officialPrice' => $officialPrice,
		]);
	}

	public function updateAccount(Request $request)
	{
		$user = $request->user();
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
		]);

		$user->update($validated);

		return back()->with('status', 'Account updated successfully.');
	}

	public function updatePassword(Request $request)
	{
		$validated = $request->validate([
			'current_password' => ['required'],
			'password' => ['required', 'confirmed', Password::min(8)],
		]);

		$user = $request->user();
		if (! Hash::check($validated['current_password'], $user->password)) {
			return back()->withErrors(['current_password' => 'Current password is incorrect.']);
		}

		$user->update([
			'password' => Hash::make($validated['password']),
		]);

		return back()->with('status', 'Password updated successfully.');
	}

	public function updateWallet(Request $request)
	{
		$validated = $request->validate([
			'wallet_address' => ['required', 'string', 'max:255'],
		]);

		$user = $request->user();
		$user->update([
			'wallet_address' => $validated['wallet_address'],
		]);

		return back()->with('status', 'Wallet address updated successfully.');
	}

	public function resendEmailVerification(Request $request)
	{
		$user = $request->user();

		// If already verified, don't send
		if ($user->email_verified_at) {
			return back()->with('status', 'Your email is already verified.');
		}

		// Generate and send OTP
		$emailVerificationController = new \App\Http\Controllers\Auth\EmailVerificationController();
		try {
			$normalizedEmail = \Illuminate\Support\Str::lower(trim($user->email));
			$emailVerificationController->generateAndSendOtp($normalizedEmail);
			$request->session()->put('verification_email', $normalizedEmail);
			
			return redirect()->route('verify-email', ['email' => $normalizedEmail])
				->with('success', 'A verification code has been sent to your email address.');
		} catch (\Exception $e) {
			return back()->withErrors(['email' => 'Failed to send verification email. Please try again later.']);
		}
	}
}



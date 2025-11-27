<?php

namespace App\Http\Controllers\Auth\Password;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Show change password required page
     */
    public function show()
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

    /**
     * Handle password change for required password reset
     */
    public function update(Request $request)
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
        Cache::forget('password_reset_required_user_'.$user->id);

        return redirect()->route('dashboard.reseller')->with('success', 'Password changed successfully! You can now access your dashboard.');
    }
}


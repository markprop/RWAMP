<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class Admin2FAController extends Controller
{
    /**
     * Show 2FA setup page with proper error handling
     */
    public function show()
    {
        try {
            $user = Auth::user();
            
            // Check if 2FA secret exists but is corrupted
            $hasCorruptedSecret = false;
            $hasCorruptedRecoveryCodes = false;
            $secretExists = false;
            $recoveryCodesExist = false;
            
            // Safely check if secret exists without triggering decryption
            try {
                $secretValue = $user->getAttribute('two_factor_secret');
                $secretExists = !empty($secretValue);
                
                if ($secretExists) {
                    try {
                        \Laravel\Fortify\Fortify::currentEncrypter()->decrypt($secretValue);
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        $hasCorruptedSecret = true;
                        Log::warning('Corrupted 2FA secret for user ' . $user->id . ': ' . $e->getMessage());
                    } catch (\Throwable $e) {
                        $hasCorruptedSecret = true;
                        Log::warning('Error checking 2FA secret for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error accessing two_factor_secret: ' . $e->getMessage());
            }
            
            // Safely check if recovery codes exist without triggering decryption
            try {
                $recoveryCodesValue = $user->getAttribute('two_factor_recovery_codes');
                $recoveryCodesExist = !empty($recoveryCodesValue);
                
                if ($recoveryCodesExist) {
                    try {
                        $codes = $user->recoveryCodes();
                        if (empty($codes) && !empty($recoveryCodesValue)) {
                            try {
                                \Laravel\Fortify\Fortify::currentEncrypter()->decrypt($recoveryCodesValue);
                            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                                $hasCorruptedRecoveryCodes = true;
                                Log::warning('Corrupted recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                            }
                        }
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        $hasCorruptedRecoveryCodes = true;
                        Log::warning('Corrupted recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                    } catch (\Throwable $e) {
                        Log::warning('Error checking recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error accessing two_factor_recovery_codes: ' . $e->getMessage());
            }
            
            return view('auth.two-factor-setup', [
                'hasCorruptedSecret' => $hasCorruptedSecret,
                'hasCorruptedRecoveryCodes' => $hasCorruptedRecoveryCodes,
                'secretExists' => $secretExists,
                'recoveryCodesExist' => $recoveryCodesExist,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error loading 2FA setup page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return view('auth.two-factor-setup', [
                'error' => 'An error occurred while loading the 2FA setup page. Please try again.',
                'hasCorruptedSecret' => false,
                'hasCorruptedRecoveryCodes' => false,
                'secretExists' => false,
                'recoveryCodesExist' => false,
            ]);
        }
    }

    /**
     * Regenerate two-factor authentication recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        // Check if 2FA is enabled
        if (!$user->two_factor_secret) {
            return back()->with('error', 'Two-factor authentication is not enabled on your account.');
        }

        try {
            // Use Fortify's action to generate new recovery codes
            $generate = new GenerateNewRecoveryCodes();
            $generate($user);

            return back()->with('success', 'Recovery codes have been regenerated successfully. Please save them in a safe place.');
        } catch (\Exception $e) {
            Log::error('Failed to regenerate recovery codes: ' . $e->getMessage());
            return back()->with('error', 'Failed to regenerate recovery codes. Please try again.');
        }
    }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class WalletConnectController extends Controller
{
    /**
     * Handle wallet connection return from mobile wallet apps
     * This route is called when user returns from MetaMask/Trust Wallet after connecting
     */
    public function handleReturn(Request $request)
    {
        try {
            // Get callback URL from request
            $callback = $request->query('callback');
            $walletAddress = $request->query('address');
            $error = $request->query('error');
            
            // Validate callback URL
            if (!$callback) {
                return redirect()->route('open.purchase')->with('wallet_error', 'No callback URL provided');
            }
            
            // Validate callback is from same origin
            $callbackHost = parse_url($callback, PHP_URL_HOST);
            $currentHost = $request->getHost();
            
            if ($callbackHost !== $currentHost) {
                Log::warning('Wallet connect callback host mismatch', [
                    'callback_host' => $callbackHost,
                    'current_host' => $currentHost
                ]);
                return redirect()->route('open.purchase')->with('wallet_error', 'Invalid callback URL');
            }
            
            // If there's an error, redirect back with error
            if ($error) {
                return redirect($callback . (strpos($callback, '?') !== false ? '&' : '?') . 'wallet_error=' . urlencode($error));
            }
            
            // If wallet address is provided, save it for authenticated users
            if ($walletAddress && Auth::check()) {
                try {
                    $user = Auth::user();
                    $user->update(['wallet_address' => $walletAddress]);
                    Log::info('Wallet address saved from mobile connection', [
                        'user_id' => $user->id,
                        'wallet_address' => $walletAddress
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to save wallet address from mobile connection', [
                        'user_id' => Auth::id(),
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Store connection state in session for frontend polling
            Session::put('wallet_connect_pending', true);
            Session::put('wallet_connect_timestamp', now()->timestamp);
            
            // Redirect back to original page with success flag
            $separator = strpos($callback, '?') !== false ? '&' : '?';
            return redirect($callback . $separator . 'wallet_connected=1&timestamp=' . time());
            
        } catch (\Exception $e) {
            Log::error('Wallet connect return handler error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('open.purchase')->with('wallet_error', 'Connection failed. Please try again.');
        }
    }
    
    /**
     * Check wallet connection status (for polling)
     */
    public function checkStatus(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'connected' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            // Check if wallet address exists
            $hasWallet = !empty($user->wallet_address);
            
            // Check session flag
            $pending = Session::get('wallet_connect_pending', false);
            $timestamp = Session::get('wallet_connect_timestamp', 0);
            
            // Clear session flag if it's older than 5 minutes
            if ($timestamp && (now()->timestamp - $timestamp) > 300) {
                Session::forget('wallet_connect_pending');
                Session::forget('wallet_connect_timestamp');
                $pending = false;
            }
            
            return response()->json([
                'connected' => $hasWallet,
                'wallet_address' => $user->wallet_address,
                'pending' => $pending,
                'timestamp' => $timestamp
            ]);
            
        } catch (\Exception $e) {
            Log::error('Wallet connect status check error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'connected' => false,
                'error' => 'Failed to check status'
            ], 500);
        }
    }
}


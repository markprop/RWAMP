<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Lookup user by wallet address (admin/reseller only)
     */
    public function lookupByWallet(Request $request)
    {
        // Force JSON response
        $request->headers->set('Accept', 'application/json');
        
        try {
            // Check authentication
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'Authentication required'
                ], 401)->header('Content-Type', 'application/json');
            }

            // Check authorization (admin or reseller)
            $user = auth()->user();
            if (!in_array($user->role, ['admin', 'reseller'])) {
                return response()->json([
                    'error' => 'You do not have permission to lookup wallet addresses'
                ], 403)->header('Content-Type', 'application/json');
            }

            $validated = $request->validate([
                'wallet' => 'required|digits:16',
            ]);

            $wallet = $validated['wallet'];
            $foundUser = User::where('wallet_address', $wallet)->first();

            // Log the lookup for security/audit purposes
            Log::info('Wallet lookup', [
                'by' => auth()->id(),
                'by_role' => auth()->user()->role ?? 'unknown',
                'query' => $wallet,
                'found' => $foundUser !== null,
                'user_id' => $foundUser->id ?? null,
            ]);

            if (!$foundUser) {
                return response()->json([
                    'error' => 'Wallet address not found'
                ], 404)->header('Content-Type', 'application/json');
            }

            // Return name, id, and receipt_screenshot (for chat receipt linking)
            return response()->json([
                'name' => $foundUser->name,
                'id' => $foundUser->id,
                'receipt_screenshot' => $foundUser->receipt_screenshot, // Linked receipt from chat
            ])->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Invalid wallet address format. Must be exactly 16 digits.',
                'errors' => $e->errors()
            ], 422)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Wallet lookup error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred while looking up the wallet address'
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}


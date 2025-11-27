<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Helpers\PriceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    /**
     * Display list of users with search and filters
     */
    public function index(Request $request)
    {
        $defaultPrice = PriceHelper::getRwampPkrPrice();
        $query = User::query();

        // Search
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        // Role filter
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Days filter (new users within last N days)
        if ($days = (int) $request->input('days')) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        // Sort
        $sort = in_array($request->input('sort'), ['name','email','created_at','role','token_balance']) ? $request->input('sort') : 'created_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $users = $query->with('reseller')->paginate(15)->withQueryString();

        return view('dashboard.admin-users', compact('users', 'defaultPrice'));
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,investor,reseller,admin',
            'password' => 'nullable|string|min:8',
            'coin_quantity' => 'nullable|numeric|min:0',
            'price_per_coin' => 'nullable|numeric|min:0.01',
        ]);
        
        // Validate price_per_coin is required if coin_quantity is provided
        if (!empty($validated['coin_quantity']) && $validated['coin_quantity'] > 0) {
            if (empty($validated['price_per_coin']) || $validated['price_per_coin'] <= 0) {
                return redirect()->route('admin.users')
                    ->withErrors(['price_per_coin' => 'Price per coin is required when assigning coins.'])
                    ->withInput();
            }
        }

        // Use default password if not provided
        $password = $validated['password'] ?? 'RWAMP@agent';
        
        // Get coin assignment details (optional)
        $coinQuantity = $validated['coin_quantity'] ?? 0;
        $pricePerCoin = $validated['price_per_coin'] ?? 0;
        $assignCoins = $coinQuantity > 0 && $pricePerCoin > 0;

        try {
            DB::beginTransaction();

            // Generate unique 16-digit wallet address
            $walletAddress = $this->generateUniqueWalletAddress();

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'token_balance' => $assignCoins ? $coinQuantity : 0,
                'wallet_address' => $walletAddress,
            ]);

            // If coins are assigned, create transaction records
            if ($assignCoins) {
                $admin = Auth::user();
                $totalPrice = $coinQuantity * $pricePerCoin;
                
                Transaction::create([
                    'user_id' => $user->id,
                    'sender_id' => $admin->id,
                    'recipient_id' => $user->id,
                    'type' => 'admin_transfer_credit',
                    'amount' => $coinQuantity,
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                    'sender_type' => 'admin',
                    'status' => 'completed',
                    'reference' => 'ADMIN-CREATE-' . time() . '-' . $user->id,
                    'payment_status' => 'verified',
                ]);

                Transaction::create([
                    'user_id' => $admin->id,
                    'sender_id' => $admin->id,
                    'recipient_id' => $user->id,
                    'type' => 'admin_transfer_debit',
                    'amount' => -$coinQuantity,
                    'price_per_coin' => $pricePerCoin,
                    'total_price' => $totalPrice,
                    'sender_type' => 'admin',
                    'status' => 'completed',
                    'reference' => 'ADMIN-CREATE-' . time() . '-' . $user->id,
                    'payment_status' => 'verified',
                ]);
            }

            DB::commit();

            $successMessage = 'User created successfully. Default password: ' . $password;
            if ($assignCoins) {
                $successMessage .= ' | Assigned ' . number_format($coinQuantity, 0) . ' RWAMP coins.';
            }

            return redirect()->route('admin.users')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());
            
            return redirect()->route('admin.users')
                ->withErrors(['error' => 'Failed to create user. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Get user details with balance and transaction history
     */
    public function show(User $user)
    {
        $user->load(['transactions' => function($query) {
            $query->latest()->limit(50);
        }]);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'token_balance' => $user->token_balance ?? 0,
                'wallet_address' => $user->wallet_address,
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'kyc_status' => $user->kyc_status,
                'kyc_id_type' => $user->kyc_id_type,
                'kyc_id_number' => $user->kyc_id_number,
                'kyc_full_name' => $user->kyc_full_name,
                'kyc_submitted_at' => $user->kyc_submitted_at?->format('Y-m-d H:i:s'),
                'kyc_approved_at' => $user->kyc_approved_at?->format('Y-m-d H:i:s'),
            ],
            'transactions' => $user->transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'price_per_coin' => $transaction->price_per_coin ? (float) $transaction->price_per_coin : null,
                    'total_price' => $transaction->total_price ? (float) $transaction->total_price : null,
                    'status' => $transaction->status,
                    'reference' => $transaction->reference,
                    'created_at' => $transaction->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Update user information
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:investor,reseller,admin,user',
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'nullable|string|min:8|max:128',
        ]);

        $newPassword = $request->input('new_password') ?: 'RWAMP@agent';
        $user->update(['password' => Hash::make($newPassword)]);

        Cache::put('password_reset_required_user_'.$user->id, true, now()->addYear());

        try {
            if (!empty($user->email)) {
                Mail::raw("Your RWAMP password has been reset by an administrator.\n\nTemporary Password: {$newPassword}\n\nPlease log in and change your password immediately.", function($m) use ($user) {
                    $m->to($user->email)->subject('RWAMP Password Reset');
                });
            }
        } catch (\Throwable $e) {}

        return back()->with('success', 'Password reset successfully. User will be prompted to change it on next login.');
    }

    /**
     * Assign wallet address to user
     */
    public function assignWalletAddress(Request $request, User $user)
    {
        try {
            if ($user->wallet_address) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has a wallet address: ' . $user->wallet_address
                ], 400);
            }

            $walletAddress = $this->generateUniqueWalletAddress();
            
            $user->update(['wallet_address' => $walletAddress]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet address assigned successfully',
                'wallet_address' => $walletAddress
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error assigning wallet address', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign wallet address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $adminCount = User::where('role', 'admin')->count();
        if ($user->role === 'admin' && $adminCount <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last admin user.']);
        }

        $userEmail = $user->email;
        $userName = $user->name;
        
        $user->delete();

        return back()->with('success', "User '{$userName}' ({$userEmail}) has been deleted successfully.");
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


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerApplication;
use App\Models\User;
use App\Traits\GeneratesWalletAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminResellerApplicationController extends Controller
{
    use GeneratesWalletAddress;

    /**
     * Display list of reseller applications with search and filters
     */
    public function index(Request $request)
    {
        $query = ResellerApplication::query();

        // Search
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('company', 'like', "%{$q}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            if (in_array($status, ['pending','approved','rejected'], true)) {
                $query->where('status', $status);
            }
        }

        // Investment capacity filter
        if ($capacity = $request->input('capacity')) {
            $query->where('investment_capacity', $capacity);
        }

        // Sort
        $sort = in_array($request->input('sort'), ['name','email','created_at','status']) ? $request->input('sort') : 'created_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $applications = $query->paginate(15)->withQueryString();
        $status = $request->input('status', 'all');

        return view('dashboard.admin-applications', compact('applications', 'status'));
    }

    /**
     * Show application details
     */
    public function show(ResellerApplication $application)
    {
        return response()->json([
            'application' => [
                'id' => $application->id,
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'company' => $application->company,
                'investment_capacity' => $application->investment_capacity,
                'investment_capacity_label' => $application->investment_capacity_label,
                'message' => $application->message,
                'status' => $application->status,
                'ip_address' => $application->ip_address,
                'user_agent' => $application->user_agent,
                'created_at' => $application->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $application->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Approve reseller application
     */
    public function approve(Request $request, ResellerApplication $application)
    {
        // If already approved/rejected, do nothing
        if ($application->status !== 'pending') {
            return back()->with('success', 'Application already ' . $application->status . '.');
        }

        // Mark application approved
        $application->update(['status' => 'approved']);

        // Default password for new resellers
        $defaultPassword = 'RWAMP@agent';
        $hashedPassword = Hash::make($defaultPassword);
        
        // Use password from application if exists, otherwise use default
        $passwordToUse = $application->password ? $application->password : $hashedPassword;
        
        $user = User::where('email', $application->email)->first();
        $isNewUser = !$user;
        
        if (!$user) {
            // Generate unique 16-digit wallet address
            $walletAddress = $this->generateUniqueWalletAddress();
            
            $user = User::create([
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'role' => 'reseller',
                'password' => $passwordToUse,
                'company_name' => $application->company,
                'investment_capacity' => $application->investment_capacity,
                'experience' => $application->experience,
                'email_verified_at' => null,
                'wallet_address' => $walletAddress,
            ]);
        } else {
            // Update existing user - generate wallet if missing
            if (!$user->wallet_address) {
                $walletAddress = $this->generateUniqueWalletAddress();
                $user->wallet_address = $walletAddress;
            }
            
            $user->update([
                'role' => 'reseller',
                'password' => $passwordToUse,
                'company_name' => $application->company,
                'investment_capacity' => $application->investment_capacity,
                'experience' => $application->experience,
                'email_verified_at' => null,
            ]);
        }

        // Generate referral code for reseller: RSL{user_id}
        if (!$user->referral_code) {
            $user->update([
                'referral_code' => 'RSL' . $user->id,
            ]);
        }

        // Set password reset required flag in cache
        Cache::put('password_reset_required_user_' . $user->id, true, now()->addDays(30));

        // Generate secure password reset token
        $resetToken = null;
        $resetUrl = null;
        try {
            $tokenRepository = \Illuminate\Support\Facades\Password::getRepository();
            $resetToken = $tokenRepository->create($user);
            $resetUrl = route('password.reset', ['token' => $resetToken, 'email' => $user->email]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate password reset token: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);
        }

        // Send email notification
        try {
            if (!empty($user->email)) {
                $loginUrl = route('login');
                Mail::send('emails.reseller-approved', [
                    'user' => $user,
                    'loginUrl' => $loginUrl,
                    'resetUrl' => $resetUrl,
                    'hasResetToken' => !empty($resetToken),
                    'isNewUser' => $isNewUser,
                ], function($m) use ($user) {
                    $m->to($user->email, $user->name)
                      ->subject('RWAMP Reseller Application Approved - Welcome!');
                });
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send reseller approval email: ' . $e->getMessage());
        }

        return back()->with('success', 'Application approved and reseller account created. Email sent with secure password setup link.');
    }

    /**
     * Reject reseller application
     */
    public function reject(Request $request, ResellerApplication $application)
    {
        // If already approved/rejected, do nothing
        if ($application->status !== 'pending') {
            return back()->with('success', 'Application already ' . $application->status . '.');
        }

        // Mark application rejected
        $application->update(['status' => 'rejected']);

        // Send rejection email notification
        try {
            if (!empty($application->email)) {
                Mail::send('emails.reseller-rejected', [
                    'application' => $application,
                ], function($m) use ($application) {
                    $m->to($application->email, $application->name)
                      ->subject('RWAMP Reseller Application Status Update');
                });
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send reseller rejection email: ' . $e->getMessage());
        }

        return back()->with('success', 'Application rejected and notification sent.');
    }

    /**
     * Update application
     */
    public function update(Request $request, ResellerApplication $application)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'investment_capacity' => 'required|in:1-10k,10-50k,50-100k,100k+',
            'message' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $application->update($validated);

        return back()->with('success', 'Application updated successfully.');
    }

    /**
     * Delete application
     */
    public function destroy(Request $request, ResellerApplication $application)
    {
        $applicationName = $application->name;
        $applicationEmail = $application->email;
        
        $application->delete();

        return back()->with('success', "Application from '{$applicationName}' ({$applicationEmail}) has been deleted successfully.");
    }
}


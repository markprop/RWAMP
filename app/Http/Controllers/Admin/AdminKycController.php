<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminKycController extends Controller
{
    /**
     * Display list of KYC submissions with search and filters
     */
    public function index(Request $request)
    {
        $valid = ['pending','approved','rejected'];
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('kyc_id_number', 'like', "%{$search}%")
                  ->orWhere('kyc_full_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        $status = $request->input('status');
        if ($status && in_array($status, $valid, true)) {
            $query->where('kyc_status', $status);
        } else {
            $query->whereIn('kyc_status', $valid);
        }

        // Filter by ID type
        if ($request->filled('id_type') && in_array($request->id_type, ['cnic', 'nicop', 'passport'])) {
            $query->where('kyc_id_type', $request->id_type);
        }

        // Only show users with submitted KYC
        $query->whereNotNull('kyc_submitted_at');

        // Sorting
        $sortBy = $request->get('sort', 'kyc_submitted_at');
        $sortDir = $request->get('dir', 'desc');
        if (in_array($sortBy, ['name', 'email', 'kyc_submitted_at', 'kyc_status', 'kyc_id_type'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest('kyc_submitted_at');
        }

        $kycSubmissions = $query->paginate(20)->withQueryString();

        return view('dashboard.admin-kyc', compact('kycSubmissions'));
    }

    /**
     * Approve KYC submission
     */
    public function approve(Request $request, User $user)
    {
        if ($user->kyc_status !== 'pending') {
            return back()->with('error', 'Only pending KYC submissions can be approved.');
        }

        // Prepare update data - NEVER change admin or reseller roles
        $updateData = [
            'kyc_status' => 'approved',
            'kyc_approved_at' => now(),
        ];

        // Only upgrade to investor if current role is 'user'
        // NEVER change admin, reseller, or investor roles - preserve them
        if ($user->role === 'user') {
            $updateData['role'] = 'investor';
            $user->update($updateData);
            return back()->with('success', 'KYC approved. User role updated to investor.');
        } else {
            // User already has a role (admin, reseller, or investor) - preserve it
            // Do NOT include role in updateData to ensure it's never changed
            $user->update($updateData);
            return back()->with('success', 'KYC approved. User role preserved as ' . ucfirst($user->role) . '.');
        }
    }

    /**
     * Reject KYC submission
     */
    public function reject(Request $request, User $user)
    {
        if ($user->kyc_status !== 'pending') {
            return back()->with('error', 'Only pending KYC submissions can be rejected.');
        }

        $user->update([
            'kyc_status' => 'rejected',
        ]);

        return back()->with('success', 'KYC rejected.');
    }

    /**
     * Update KYC information
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'kyc_id_type' => ['required', 'in:cnic,nicop,passport'],
            'kyc_id_number' => ['required', 'string', 'max:50'],
            'kyc_full_name' => ['required', 'string', 'max:255'],
            'kyc_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $updateData = [
            'kyc_id_type' => $validated['kyc_id_type'],
            'kyc_id_number' => $validated['kyc_id_number'],
            'kyc_full_name' => $validated['kyc_full_name'],
            'kyc_status' => $validated['kyc_status'],
        ];

        // If status is being changed to approved, set approved_at
        if ($validated['kyc_status'] === 'approved' && $user->kyc_status !== 'approved') {
            $updateData['kyc_approved_at'] = now();
            
            // Only upgrade to investor if current role is 'user'
            // NEVER change admin, reseller, or investor roles
            if ($user->role === 'user') {
                $updateData['role'] = 'investor';
            }
        }

        // If status is being changed from approved, clear approved_at
        if ($validated['kyc_status'] !== 'approved' && $user->kyc_status === 'approved') {
            $updateData['kyc_approved_at'] = null;
        }

        $user->update($updateData);

        return back()->with('success', 'KYC information updated successfully.');
    }

    /**
     * Delete KYC submission
     */
    public function destroy(Request $request, User $user)
    {
        // Delete KYC files if they exist
        if ($user->kyc_id_front_path && Storage::disk('local')->exists($user->kyc_id_front_path)) {
            Storage::disk('local')->delete($user->kyc_id_front_path);
        }
        if ($user->kyc_id_back_path && Storage::disk('local')->exists($user->kyc_id_back_path)) {
            Storage::disk('local')->delete($user->kyc_id_back_path);
        }
        if ($user->kyc_selfie_path && Storage::disk('local')->exists($user->kyc_selfie_path)) {
            Storage::disk('local')->delete($user->kyc_selfie_path);
        }

        // Clear KYC data - set status to 'not_started' (column doesn't allow null)
        $user->update([
            'kyc_status' => 'not_started',
            'kyc_id_type' => null,
            'kyc_id_number' => null,
            'kyc_full_name' => null,
            'kyc_id_front_path' => null,
            'kyc_id_back_path' => null,
            'kyc_selfie_path' => null,
            'kyc_submitted_at' => null,
            'kyc_approved_at' => null,
        ]);

        return back()->with('success', 'KYC submission deleted successfully.');
    }

    /**
     * Download KYC file
     */
    public function downloadFile(User $user, string $type)
    {
        $filePath = match($type) {
            'front' => $user->kyc_id_front_path,
            'back' => $user->kyc_id_back_path,
            'selfie' => $user->kyc_selfie_path,
            default => null,
        };

        if (!$filePath || !Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found');
        }

        // Serve image for viewing (not download)
        $file = Storage::disk('local')->get($filePath);
        $mimeType = Storage::disk('local')->mimeType($filePath);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->header('Cache-Control', 'private, max-age=3600');
    }
}


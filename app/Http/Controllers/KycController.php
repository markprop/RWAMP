<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KycController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        // If already approved, redirect to dashboard
        if ($user->kyc_status === 'approved' && $user->role === 'investor') {
            return redirect()->route('dashboard.investor');
        }

        return view('auth.kyc', [
            'user' => $user,
        ]);
    }

    public function submit(Request $request)
    {
        $user = Auth::user();

        // If already approved, don't allow resubmission
        if ($user->kyc_status === 'approved') {
            return back()->with('error', 'Your KYC is already approved.');
        }

        // If rejected, clear old KYC data to allow resubmission
        if ($user->kyc_status === 'rejected') {
            // Optionally delete old files
            // Storage::delete([$user->kyc_id_front_path, $user->kyc_id_back_path, $user->kyc_selfie_path]);
            
            // Clear old KYC data
            $user->update([
                'kyc_id_type' => null,
                'kyc_id_number' => null,
                'kyc_full_name' => null,
                'kyc_id_front_path' => null,
                'kyc_id_back_path' => null,
                'kyc_selfie_path' => null,
            ]);
        }

        // Build validation rules
        $rules = [
            'kyc_id_type' => ['required', 'in:cnic,nicop,passport'],
            'kyc_id_number' => ['required', 'string', 'max:50'],
            'kyc_full_name' => ['required', 'string', 'max:255'],
            'kyc_id_front' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // 5MB max
            'kyc_selfie' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];

        // ID back is required for CNIC and NICOP
        if (in_array($request->kyc_id_type, ['cnic', 'nicop'])) {
            $rules['kyc_id_back'] = ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'];
        } else {
            $rules['kyc_id_back'] = ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'];
        }

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors with input
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            // Create storage directory for user's KYC files
            $kycDir = 'kyc/' . $user->id;
            
            // Ensure directory exists
            if (!Storage::disk('local')->exists($kycDir)) {
                Storage::disk('local')->makeDirectory($kycDir);
            }
            
            // Store ID front image
            if (!$request->hasFile('kyc_id_front')) {
                throw new \Exception('ID front image is required');
            }
            $idFrontPath = $request->file('kyc_id_front')->store($kycDir, 'local');
            
            // Store ID back image (if provided)
            $idBackPath = null;
            if ($request->hasFile('kyc_id_back')) {
                $idBackPath = $request->file('kyc_id_back')->store($kycDir, 'local');
            }
            
            // Store selfie image
            if (!$request->hasFile('kyc_selfie')) {
                throw new \Exception('Selfie image is required');
            }
            $selfiePath = $request->file('kyc_selfie')->store($kycDir, 'local');

            // Update user with KYC data
            $user->update([
                'kyc_status' => 'pending',
                'kyc_id_type' => $validated['kyc_id_type'],
                'kyc_id_number' => $validated['kyc_id_number'],
                'kyc_full_name' => $validated['kyc_full_name'],
                'kyc_id_front_path' => $idFrontPath,
                'kyc_id_back_path' => $idBackPath,
                'kyc_selfie_path' => $selfiePath,
                'kyc_submitted_at' => now(),
            ]);

            return redirect()->route('profile.show')->with('success', 'Your KYC submission is under review. Admins will notify you once it\'s processed.');
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('KYC submission failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to submit KYC: ' . $e->getMessage())->withInput();
        }
    }
}

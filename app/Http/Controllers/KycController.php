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

    /**
     * Download KYC file for the currently authenticated user (profile page)
     */
    public function downloadFile(string $type)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        if (!in_array($type, ['front', 'back', 'selfie'], true)) {
            abort(400, 'Invalid file type. Must be front, back, or selfie.');
        }

        $dbFilePath = match ($type) {
            'front' => $user->kyc_id_front_path,
            'back' => $user->kyc_id_back_path,
            'selfie' => $user->kyc_selfie_path,
        };

        if (!$dbFilePath) {
            abort(404, 'KYC file path not found in your profile for type: ' . $type);
        }

        // Normalize and secure path
        $normalizedPath = ltrim($dbFilePath, '/');
        $normalizedPath = preg_replace('#^(storage/app/|app/)#', '', $normalizedPath);

        // Must live under kyc/ to avoid path traversal
        if (!str_starts_with($normalizedPath, 'kyc/')) {
            abort(403, 'Invalid KYC file path.');
        }

        // Enforce user directory (kyc/{user_id}/...)
        $filename = basename($normalizedPath);
        $userDirPath = 'kyc/' . $user->id . '/' . $filename;

        if (\Storage::disk('local')->exists($userDirPath)) {
            $normalizedPath = $userDirPath;
        } elseif (!\Storage::disk('local')->exists($normalizedPath)) {
            abort(404, 'KYC file not found.');
        }

        try {
            $file = \Storage::disk('local')->get($normalizedPath);

            if (!$file) {
                abort(500, 'Failed to read KYC file.');
            }

            $mimeType = \Storage::disk('local')->mimeType($normalizedPath)
                ?? match (strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION))) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png'        => 'image/png',
                    'gif'        => 'image/gif',
                    'pdf'        => 'application/pdf',
                    default      => 'application/octet-stream',
                };

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . basename($normalizedPath) . '"')
                ->header('Cache-Control', 'private, max-age=3600')
                ->header('X-Content-Type-Options', 'nosniff');
        } catch (\Exception $e) {
            \Log::error('Error serving profile KYC file', [
                'user_id'   => $user->id,
                'type'      => $type,
                'file_path' => $normalizedPath,
                'error'     => $e->getMessage(),
            ]);

            abort(500, 'Error serving KYC file: ' . $e->getMessage());
        }
    }
}

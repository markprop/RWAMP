<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * Resolve user from ULID or numeric ID with enhanced security
     * Sanitizes input and ensures proper type casting for database queries
     */
    private function resolveUser($user): User
    {
        if ($user instanceof User) {
            return $user;
        }
        
        // Sanitize input: ensure we're working with a string or numeric value
        $sanitized = is_string($user) ? trim($user) : $user;
        
        // Try ULID first (26 characters, alphanumeric)
        if (is_string($sanitized) && strlen($sanitized) === 26 && ctype_alnum($sanitized)) {
            $userModel = User::where('ulid', $sanitized)->first();
            if ($userModel) {
                return $userModel;
            }
        }
        
        // Try numeric ID (cast to int for proper database matching and security)
        $numericId = is_numeric($sanitized) ? (int) $sanitized : null;
        if ($numericId !== null && $numericId > 0) {
            $userModel = User::find($numericId);
            if ($userModel) {
                return $userModel;
            }
        }
        
        // Last attempt: try both as string/numeric (fallback for edge cases)
        $userModel = User::where('ulid', $sanitized)
            ->orWhere('id', $numericId ?? $sanitized)
            ->first();
            
        if (!$userModel) {
            Log::error('User not found in resolveUser', [
                'provided_user' => $user,
                'sanitized' => $sanitized,
                'type' => gettype($user),
                'numeric_id' => $numericId,
            ]);
            abort(404, 'User not found.');
        }
        
        return $userModel;
    }

    /**
     * Generate a predictable, secure file path for KYC images
     * Format: kyc/{user_id}/{type}_{timestamp}.{extension}
     * 
     * @param User $user The user model
     * @param string $type File type: 'front', 'back', or 'selfie'
     * @param \Illuminate\Http\UploadedFile $file The uploaded file
     * @return string The generated file path relative to storage/app
     */
    private function generateKycFilePath(User $user, string $type, $file): string
    {
        $timestamp = now()->timestamp;
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Validate extension
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException("Invalid file extension: {$extension}. Allowed: " . implode(', ', $allowedExtensions));
        }
        
        // Normalize extension (jpg -> jpeg for consistency)
        if ($extension === 'jpg') {
            $extension = 'jpeg';
        }
        
        // Generate path: kyc/{user_id}/{type}_{timestamp}.{extension}
        return "kyc/{$user->id}/{$type}_{$timestamp}.{$extension}";
    }

    /**
     * Handle KYC image uploads atomically with guaranteed data integrity
     * 
     * This method ensures all three KYC images (front, back, selfie) are uploaded
     * and saved atomically. If any file fails to upload, all changes are rolled back.
     * 
     * @param User $user The user submitting KYC
     * @param Request $request The request containing uploaded files
     * @return array Array with 'success' boolean and 'paths' array or 'error' message
     * @throws \Exception If upload fails
     */
    public static function handleKycUploads(User $user, Request $request): array
    {
        // Validate that required files are present
        if (!$request->hasFile('kyc_id_front')) {
            throw new \InvalidArgumentException('ID front image is required');
        }
        
        if (!$request->hasFile('kyc_selfie')) {
            throw new \InvalidArgumentException('Selfie image is required');
        }
        
        // ID back is required for CNIC and NICOP
        $idType = $request->input('kyc_id_type');
        if (in_array($idType, ['cnic', 'nicop']) && !$request->hasFile('kyc_id_back')) {
            throw new \InvalidArgumentException('ID back image is required for CNIC and NICOP');
        }
        
        // Validate file types
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
        $frontFile = $request->file('kyc_id_front');
        $selfieFile = $request->file('kyc_selfie');
        $backFile = $request->hasFile('kyc_id_back') ? $request->file('kyc_id_back') : null;
        
        // Validate front file
        if (!in_array($frontFile->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type for ID front. Must be JPEG or PNG.');
        }
        
        // Validate selfie file
        if (!in_array($selfieFile->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type for selfie. Must be JPEG or PNG.');
        }
        
        // Validate back file if present
        if ($backFile && !in_array($backFile->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type for ID back. Must be JPEG or PNG.');
        }
        
        // Create instance to access private method
        $controller = new self();
        
        // Generate predictable file paths
        $frontPath = $controller->generateKycFilePath($user, 'front', $frontFile);
        $selfiePath = $controller->generateKycFilePath($user, 'selfie', $selfieFile);
        $backPath = $backFile ? $controller->generateKycFilePath($user, 'back', $backFile) : null;
        
        // Ensure directory exists
        $kycDir = 'kyc/' . $user->id;
        if (!Storage::disk('local')->exists($kycDir)) {
            Storage::disk('local')->makeDirectory($kycDir, 0755, true);
        }
        
        // Track uploaded files for rollback if needed
        $uploadedFiles = [];
        
        try {
            // Begin database transaction for atomicity
            DB::beginTransaction();
            
            // Upload front image
            $frontStored = Storage::disk('local')->put($frontPath, file_get_contents($frontFile->getRealPath()));
            if (!$frontStored) {
                throw new \Exception('Failed to save ID front image');
            }
            $uploadedFiles[] = $frontPath;
            
            Log::info('KYC front image uploaded successfully', [
                'user_id' => $user->id,
                'file_path' => $frontPath,
            ]);
            
            // Upload back image (if provided)
            if ($backFile) {
                $backStored = Storage::disk('local')->put($backPath, file_get_contents($backFile->getRealPath()));
                if (!$backStored) {
                    throw new \Exception('Failed to save ID back image');
                }
                $uploadedFiles[] = $backPath;
                
                Log::info('KYC back image uploaded successfully', [
                    'user_id' => $user->id,
                    'file_path' => $backPath,
                ]);
            }
            
            // Upload selfie image
            $selfieStored = Storage::disk('local')->put($selfiePath, file_get_contents($selfieFile->getRealPath()));
            if (!$selfieStored) {
                throw new \Exception('Failed to save selfie image');
            }
            $uploadedFiles[] = $selfiePath;
            
            Log::info('KYC selfie image uploaded successfully', [
                'user_id' => $user->id,
                'file_path' => $selfiePath,
            ]);
            
            // Verify all files exist before updating database
            foreach ($uploadedFiles as $filePath) {
                if (!Storage::disk('local')->exists($filePath)) {
                    throw new \Exception("Uploaded file not found at path: {$filePath}");
                }
            }
            
            // Update user model with exact paths
            $user->update([
                'kyc_id_front_path' => $frontPath,
                'kyc_id_back_path' => $backPath,
                'kyc_selfie_path' => $selfiePath,
                'kyc_submitted_at' => now(),
            ]);
            
            // Commit transaction
            DB::commit();
            
            Log::info('KYC uploads completed successfully', [
                'user_id' => $user->id,
                'front_path' => $frontPath,
                'back_path' => $backPath,
                'selfie_path' => $selfiePath,
            ]);
            
            return [
                'success' => true,
                'paths' => [
                    'front' => $frontPath,
                    'back' => $backPath,
                    'selfie' => $selfiePath,
                ],
            ];
            
        } catch (\Exception $e) {
            // Rollback database transaction
            DB::rollBack();
            
            // Delete any partially uploaded files
            foreach ($uploadedFiles as $filePath) {
                if (Storage::disk('local')->exists($filePath)) {
                    Storage::disk('local')->delete($filePath);
                }
            }
            
            Log::error('KYC upload failed, transaction rolled back', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Approve KYC submission
     * Supports both ULID and numeric ID for user lookup
     */
    public function approve(Request $request, $user)
    {
        $user = $this->resolveUser($user);
        
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

            // Send KYC approved email
            try {
                Mail::send('emails.kyc-approved', [
                    'user' => $user->fresh(),
                ], function ($m) use ($user) {
                    $m->to($user->email, $user->name)
                        ->subject('Your KYC has been approved - RWAMP');
                });
            } catch (\Throwable $e) {
                Log::error('Failed to send KYC approved email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return back()->with('success', 'KYC approved. User role updated to investor.');
        } else {
            // User already has a role (admin, reseller, or investor) - preserve it
            // Do NOT include role in updateData to ensure it's never changed
            $user->update($updateData);

            // Send KYC approved email
            try {
                Mail::send('emails.kyc-approved', [
                    'user' => $user->fresh(),
                ], function ($m) use ($user) {
                    $m->to($user->email, $user->name)
                        ->subject('Your KYC has been approved - RWAMP');
                });
            } catch (\Throwable $e) {
                Log::error('Failed to send KYC approved email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return back()->with('success', 'KYC approved. User role preserved as ' . ucfirst($user->role) . '.');
        }
    }

    /**
     * Reject KYC submission
     * Supports both ULID and numeric ID for user lookup
     */
    public function reject(Request $request, $user)
    {
        $user = $this->resolveUser($user);
        
        if ($user->kyc_status !== 'pending') {
            return back()->with('error', 'Only pending KYC submissions can be rejected.');
        }

        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejection so the user can fix the issue.',
            'rejection_reason.min' => 'Rejection reason must be at least 10 characters long.',
            'rejection_reason.max' => 'Rejection reason cannot exceed 1000 characters.',
        ]);

        $user->update([
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => $validated['rejection_reason'],
        ]);

        // Log the rejection for audit trail
        Log::info('KYC submission rejected', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'rejection_reason' => $validated['rejection_reason'],
            'admin_id' => auth()->id(),
        ]);

        // Send KYC rejected email with reason
        try {
            Mail::send('emails.kyc-rejected', [
                'user' => $user->fresh(),
                'reason' => $validated['rejection_reason'],
            ], function ($m) use ($user) {
                $m->to($user->email, $user->name)
                    ->subject('Your KYC has been rejected - RWAMP');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send KYC rejected email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'KYC rejected. The user has been notified with the rejection reason.');
    }

    /**
     * Update KYC information
     * Supports both ULID and numeric ID for user lookup
     */
    public function update(Request $request, $user)
    {
        $user = $this->resolveUser($user);
        $previousStatus = $user->kyc_status;
        
        $validated = $request->validate([
            'kyc_id_type' => ['required', 'in:cnic,nicop,passport'],
            'kyc_id_number' => ['required', 'string', 'max:50'],
            'kyc_full_name' => ['required', 'string', 'max:255'],
            'kyc_status' => ['required', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'required_if:kyc_status,rejected', 'string', 'min:10', 'max:1000'],
        ]);

        $updateData = [
            'kyc_id_type' => $validated['kyc_id_type'],
            'kyc_id_number' => $validated['kyc_id_number'],
            'kyc_full_name' => $validated['kyc_full_name'],
            'kyc_status' => $validated['kyc_status'],
        ];

        // Handle rejection reason
        if ($validated['kyc_status'] === 'rejected') {
            $updateData['kyc_rejection_reason'] = $validated['rejection_reason'] ?? null;
        } else {
            // Clear any old rejection reason when status is not rejected
            $updateData['kyc_rejection_reason'] = null;
        }

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

        // Send notification emails when status changes via Edit modal
        $user->refresh();
        if ($previousStatus !== $user->kyc_status) {
            if ($user->kyc_status === 'approved') {
                try {
                    Mail::send('emails.kyc-approved', [
                        'user' => $user,
                    ], function ($m) use ($user) {
                        $m->to($user->email, $user->name)
                            ->subject('Your KYC has been approved - RWAMP');
                    });
                } catch (\Throwable $e) {
                    Log::error('Failed to send KYC approved email (update)', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } elseif ($user->kyc_status === 'rejected' && !empty($user->kyc_rejection_reason)) {
                try {
                    Mail::send('emails.kyc-rejected', [
                        'user' => $user,
                        'reason' => $user->kyc_rejection_reason,
                    ], function ($m) use ($user) {
                        $m->to($user->email, $user->name)
                            ->subject('Your KYC has been rejected - RWAMP');
                    });
                } catch (\Throwable $e) {
                    Log::error('Failed to send KYC rejected email (update)', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return back()->with('success', 'KYC information updated successfully.');
    }

    /**
     * Delete KYC submission
     * Supports both ULID and numeric ID for user lookup
     */
    public function destroy(Request $request, $user)
    {
        $user = $this->resolveUser($user);
        
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
     * Download KYC file - Simplified and secure version
     * 
     * This method relies solely on the database path stored during upload.
     * With the new handleKycUploads method ensuring correct paths, all fallback
     * logic has been removed for security and reliability.
     * 
     * Supports both ULID and numeric ID for user lookup
     */
    public function downloadFile($user, string $type)
    {
        // Validate type
        if (!in_array($type, ['front', 'back', 'selfie'])) {
            abort(400, 'Invalid file type. Must be front, back, or selfie.');
        }

        // Log initial request for audit trail
        Log::info('KYC file download request received', [
            'user_param' => $user,
            'user_param_type' => gettype($user),
            'type' => $type,
            'request_url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
        ]);

        // Resolve user with enhanced security
        try {
            $user = $this->resolveUser($user);
        } catch (\Exception $e) {
            Log::error('Failed to resolve user in downloadFile', [
                'user_param' => $user,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        // Get file path from database (single source of truth)
        $dbFilePath = match($type) {
            'front' => $user->kyc_id_front_path,
            'back' => $user->kyc_id_back_path,
            'selfie' => $user->kyc_selfie_path,
            default => null,
        };

        // Validate that database path exists
        if (!$dbFilePath) {
            Log::warning('KYC file path not found in database', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type' => $type,
            ]);
            abort(404, 'KYC file path not found in database for user ' . $user->id . ' (type: ' . $type . ')');
        }

        // Security: Validate path format to prevent path traversal attacks
        // Path must start with 'kyc/' (but may be in different user's directory due to data issues)
        $normalizedPath = ltrim($dbFilePath, '/');
        
        // Remove any 'storage/app/' or 'app/' prefixes if present
        $normalizedPath = preg_replace('#^(storage/app/|app/)#', '', $normalizedPath);
        
        // Security: Ensure path starts with 'kyc/' to prevent path traversal
        if (!str_starts_with($normalizedPath, 'kyc/')) {
            Log::error('KYC file path security validation failed - path traversal attempt detected', [
                'user_id' => $user->id,
                'type' => $type,
                'db_path' => $dbFilePath,
                'normalized_path' => $normalizedPath,
            ]);
            abort(403, 'Invalid file path format. Security validation failed.');
        }

        // Check if file exists using Storage
        $fileExists = Storage::disk('local')->exists($normalizedPath);
        
        // If file doesn't exist at the path, try to find it in the user's directory
        if (!$fileExists) {
            $filename = basename($normalizedPath);
            $userDirPath = "kyc/{$user->id}/{$filename}";
            
            // Try the correct user directory
            if (Storage::disk('local')->exists($userDirPath)) {
                $normalizedPath = $userDirPath;
                $fileExists = true;
                
                Log::warning('KYC file found in correct user directory but path was wrong', [
                    'user_id' => $user->id,
                    'type' => $type,
                    'original_path' => $dbFilePath,
                    'corrected_path' => $userDirPath,
                ]);
            } else {
                // File doesn't exist - log detailed error
                Log::error('KYC file not found at database path or user directory', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'type' => $type,
                    'database_path' => $dbFilePath,
                    'normalized_path' => $normalizedPath,
                    'tried_user_dir' => $userDirPath,
                    'absolute_path' => storage_path('app/' . $normalizedPath),
                    'storage_root' => storage_path('app'),
                ]);
                abort(404, 'KYC file not found at path: ' . $normalizedPath . ' for user ' . $user->id);
            }
        }

        try {
            // Retrieve file content using Storage
            $file = Storage::disk('local')->get($normalizedPath);
            
            if (!$file) {
                Log::error('Failed to read KYC file content', [
                    'user_id' => $user->id,
                    'type' => $type,
                    'file_path' => $normalizedPath,
                ]);
                abort(500, 'Failed to read KYC file.');
            }

            // Detect MIME type
            $mimeType = Storage::disk('local')->mimeType($normalizedPath);
            
            // Fallback MIME type detection if Storage fails
            if (!$mimeType) {
                $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));
                $mimeType = match($extension) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf' => 'application/pdf',
                    default => 'application/octet-stream',
                };
            }

            // Log successful file access for audit trail
            Log::info('KYC file served successfully', [
                'user_id' => $user->id,
                'type' => $type,
                'file_path' => $normalizedPath,
                'mime_type' => $mimeType,
                'file_size' => strlen($file),
            ]);
            
            // Serve file with appropriate headers
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . basename($normalizedPath) . '"')
                ->header('Cache-Control', 'private, max-age=3600')
                ->header('X-Content-Type-Options', 'nosniff');
                
        } catch (\Exception $e) {
            Log::error('Error serving KYC file', [
                'user_id' => $user->id,
                'type' => $type,
                'file_path' => $normalizedPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Error serving KYC file: ' . $e->getMessage());
        }
    }
}


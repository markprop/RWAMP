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
     * Resolve user from ULID or numeric ID
     */
    private function resolveUser($user): User
    {
        if ($user instanceof User) {
            return $user;
        }
        
        $userModel = User::where('ulid', $user)->orWhere('id', $user)->first();
        if (!$userModel) {
            abort(404, 'User not found.');
        }
        
        return $userModel;
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
     * Supports both ULID and numeric ID for user lookup
     */
    public function reject(Request $request, $user)
    {
        $user = $this->resolveUser($user);
        
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
     * Supports both ULID and numeric ID for user lookup
     */
    public function update(Request $request, $user)
    {
        $user = $this->resolveUser($user);
        
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
     * Download KYC file
     * Supports both ULID and numeric ID for user lookup
     */
    public function downloadFile($user, string $type)
    {
        // Validate type
        if (!in_array($type, ['front', 'back', 'selfie'])) {
            abort(400, 'Invalid file type. Must be front, back, or selfie.');
        }

        // Resolve user - try ULID first, then numeric ID
        $user = $this->resolveUser($user);

        // Get file path from database
        $dbFilePath = match($type) {
            'front' => $user->kyc_id_front_path,
            'back' => $user->kyc_id_back_path,
            'selfie' => $user->kyc_selfie_path,
            default => null,
        };

        // Log for debugging - this will help us see if the method is being called
        \Log::info('KYC file download request - METHOD CALLED', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'type' => $type,
            'db_file_path' => $dbFilePath,
            'kyc_id_front_path' => $user->kyc_id_front_path,
            'kyc_id_back_path' => $user->kyc_id_back_path,
            'kyc_selfie_path' => $user->kyc_selfie_path,
            'request_url' => request()->fullUrl(),
        ]);

        // Build KYC directory path
        $kycDir = 'kyc/' . $user->id;
        $kycDirectory = storage_path('app/' . $kycDir);
        
        $foundPath = null;
        
        // First, try to use database path if it exists
        if ($dbFilePath) {
            // Normalize file path - remove any leading/trailing slashes and storage/app prefix
            $normalizedPath = ltrim($dbFilePath, '/');
            $normalizedPath = str_replace('storage/app/', '', $normalizedPath);
            $normalizedPath = str_replace('app/', '', $normalizedPath);
            
            // Check absolute path first (most reliable)
            $absolutePath = storage_path('app/' . $normalizedPath);
            if (file_exists($absolutePath)) {
                $foundPath = $normalizedPath;
                \Log::info('Found KYC file using database path (absolute check)', [
                    'user_id' => $user->id,
                    'type' => $type,
                    'db_path' => $dbFilePath,
                    'normalized_path' => $normalizedPath,
                    'absolute_path' => $absolutePath,
                    'found_path' => $foundPath,
                ]);
            } else {
                // Try Storage check
                if (Storage::disk('local')->exists($normalizedPath)) {
                    $foundPath = $normalizedPath;
                    \Log::info('Found KYC file using database path (Storage check)', [
                        'user_id' => $user->id,
                        'type' => $type,
                        'db_path' => $dbFilePath,
                        'normalized_path' => $normalizedPath,
                        'found_path' => $foundPath,
                    ]);
                } else {
                    // Try alternative path formats
                    $pathsToTry = [
                        $kycDir . '/' . basename($normalizedPath),
                        'kyc/' . $user->id . '/' . basename($normalizedPath),
                    ];
                    
                    foreach ($pathsToTry as $path) {
                        $altAbsolutePath = storage_path('app/' . $path);
                        if (file_exists($altAbsolutePath)) {
                            $foundPath = $path;
                            \Log::info('Found KYC file using alternative path', [
                                'user_id' => $user->id,
                                'type' => $type,
                                'db_path' => $dbFilePath,
                                'alternative_path' => $path,
                                'found_path' => $foundPath,
                            ]);
                            break;
                        }
                    }
                }
            }
            
            // Log if database path didn't work
            if (!$foundPath) {
                \Log::warning('Database path exists but file not found', [
                    'user_id' => $user->id,
                    'type' => $type,
                    'db_path' => $dbFilePath,
                    'normalized_path' => $normalizedPath,
                    'absolute_path_checked' => $absolutePath,
                    'file_exists' => file_exists($absolutePath),
                    'storage_root' => storage_path('app'),
                ]);
            }
        }

        // If still not found, search in the user's KYC directory and use files by order
        if (!$foundPath && is_dir($kycDirectory)) {
                $filesInDir = array_diff(scandir($kycDirectory), ['.', '..']);
                
                if (!empty($filesInDir)) {
                    // Sort files by modification time (oldest first) to maintain consistent order
                    $filesWithTime = [];
                    foreach ($filesInDir as $file) {
                        $filePathFull = $kycDirectory . '/' . $file;
                        $filesWithTime[] = [
                            'name' => $file,
                            'time' => filemtime($filePathFull),
                            'path' => $kycDir . '/' . $file,
                        ];
                    }
                    usort($filesWithTime, function($a, $b) {
                        return $a['time'] <=> $b['time'];
                    });
                    $sortedFiles = array_column($filesWithTime, 'name');
                    $sortedPaths = array_column($filesWithTime, 'path');
                    
                    // Try to find file by matching filename if database path exists
                    if ($dbFilePath) {
                        $searchFilename = basename($dbFilePath);
                        foreach ($sortedFiles as $index => $file) {
                            if (strtolower($file) === strtolower($searchFilename) || 
                                strtolower(pathinfo($file, PATHINFO_FILENAME)) === strtolower(pathinfo($searchFilename, PATHINFO_FILENAME))) {
                                $foundPath = $sortedPaths[$index];
                                \Log::info('Found KYC file by filename match', [
                                    'user_id' => $user->id,
                                    'type' => $type,
                                    'searched_filename' => $searchFilename,
                                    'found_file' => $file,
                                    'found_path' => $foundPath,
                                ]);
                                break;
                            }
                        }
                    }
                    
                    // If still not found, use files in order based on type
                    // This handles cases where database paths don't match actual files
                    if (!$foundPath) {
                        $fileCount = count($sortedFiles);
                        
                        // Determine file index based on type and available files
                        // For CNIC/NICOP: front (0), back (1), selfie (2)
                        // For passport: front (0), selfie (1) - no back
                        $fileIndex = match($type) {
                            'front' => 0, // Always first
                            'back' => ($fileCount >= 3) ? 1 : null, // Second if 3 files, otherwise might not exist
                            'selfie' => ($fileCount >= 3) ? 2 : ($fileCount >= 2 ? 1 : 0), // Last if 3 files, second if 2 files, first if only 1
                            default => 0,
                        };
                        
                        if ($fileIndex !== null && isset($sortedPaths[$fileIndex])) {
                            $foundPath = $sortedPaths[$fileIndex];
                            \Log::info('Found KYC file by order (database path mismatch or null)', [
                                'user_id' => $user->id,
                                'type' => $type,
                                'file_index' => $fileIndex,
                                'file_count' => $fileCount,
                                'selected_file' => $sortedFiles[$fileIndex],
                                'found_path' => $foundPath,
                                'all_files' => $sortedFiles,
                                'database_path' => $dbFilePath,
                            ]);
                        } elseif ($type === 'selfie' && $fileCount >= 1) {
                            // For selfie, use the last file
                            $lastIndex = $fileCount - 1;
                            $foundPath = $sortedPaths[$lastIndex];
                            \Log::info('Found KYC selfie file (using last file)', [
                                'user_id' => $user->id,
                                'type' => $type,
                                'file_index' => $lastIndex,
                                'file_count' => $fileCount,
                                'selected_file' => $sortedFiles[$lastIndex],
                                'found_path' => $foundPath,
                            ]);
                        } elseif ($type === 'front' && $fileCount >= 1) {
                            // For front, use the first file
                            $foundPath = $sortedPaths[0];
                            \Log::info('Found KYC front file (using first file)', [
                                'user_id' => $user->id,
                                'type' => $type,
                                'file_count' => $fileCount,
                                'selected_file' => $sortedFiles[0],
                                'found_path' => $foundPath,
                            ]);
                        } elseif ($type === 'back' && $fileCount >= 2) {
                            // For back, use the middle file (if 3 files) or second file
                            $foundPath = $sortedPaths[1];
                            \Log::info('Found KYC back file (using middle file)', [
                                'user_id' => $user->id,
                                'type' => $type,
                                'file_count' => $fileCount,
                                'selected_file' => $sortedFiles[1],
                                'found_path' => $foundPath,
                            ]);
                        }
                    }
                }
        }

        // If file still not found, abort with detailed error
        if (!$foundPath) {
            $filesInDir = [];
            if (is_dir($kycDirectory)) {
                $filesInDir = array_diff(scandir($kycDirectory), ['.', '..']);
            }
            
            \Log::error('KYC file not found after all attempts', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type' => $type,
                'database_path' => $dbFilePath,
                'kyc_directory' => $kycDirectory,
                'kyc_directory_exists' => is_dir($kycDirectory),
                'files_in_kyc_directory' => $filesInDir,
                'storage_root' => storage_path('app'),
            ]);
            
            abort(404, 'KYC file not found. Please check if the file exists in storage/app/kyc/' . $user->id . '/');
        }

        // Use the found path
        $filePath = $foundPath;

        // Final verification - check if file actually exists
        $absoluteFilePath = storage_path('app/' . $filePath);
        if (!file_exists($absoluteFilePath)) {
            \Log::error('KYC file path found but file does not exist at absolute path', [
                'user_id' => $user->id,
                'type' => $type,
                'file_path' => $filePath,
                'absolute_path' => $absoluteFilePath,
                'file_exists' => file_exists($absoluteFilePath),
            ]);
            abort(404, 'KYC file not found at: ' . $absoluteFilePath);
        }

        try {
            // Serve image for viewing (not download)
            // Try Storage first, fallback to file_get_contents if needed
            $file = Storage::disk('local')->get($filePath);
            
            if (!$file) {
                // Fallback to direct file read
                $file = file_get_contents($absoluteFilePath);
                if (!$file) {
                    \Log::error('Failed to read KYC file using both Storage and file_get_contents', [
                        'user_id' => $user->id,
                        'type' => $type,
                        'file_path' => $filePath,
                        'absolute_path' => $absoluteFilePath,
                    ]);
                    abort(404, 'KYC file could not be read.');
                }
                \Log::warning('KYC file read using file_get_contents fallback', [
                    'user_id' => $user->id,
                    'type' => $type,
                    'file_path' => $filePath,
                ]);
            }

            $mimeType = Storage::disk('local')->mimeType($filePath);
            
            // Fallback mime type if detection fails
            if (!$mimeType) {
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeType = match($extension) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf' => 'application/pdf',
                    default => 'application/octet-stream',
                };
            }
            
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
                ->header('Cache-Control', 'private, max-age=3600');
        } catch (\Exception $e) {
            \Log::error('Error serving KYC file', [
                'user_id' => $user->id,
                'type' => $type,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Error serving KYC file: ' . $e->getMessage());
        }
    }
}


<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuditKycImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:audit-images 
                            {--dry-run : Run in audit mode without making changes}
                            {--base-path= : Custom base path for KYC images (default: auto-detect)}
                            {--fix-missing : Automatically fix NULL paths if files exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and repair KYC image paths in the database to match actual file locations';

    /**
     * Statistics for summary report
     */
    private $stats = [
        'total_processed' => 0,
        'correct_paths' => 0,
        'fixed_paths' => 0,
        'missing_directories' => 0,
        'unresolved_errors' => 0,
        'status_changed' => 0,
        'errors' => [],
    ];

    /**
     * Base path for KYC images
     */
    private $basePath;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $customBasePath = $this->option('base-path');
        $fixMissing = $this->option('fix-missing');

        // Determine base path
        if ($customBasePath) {
            $this->basePath = rtrim($customBasePath, '/');
        } else {
            // Default: check public/kyc first, then storage/app/kyc
            $publicPath = public_path('kyc');
            $storagePath = storage_path('app/kyc');
            
            if (is_dir($publicPath)) {
                $this->basePath = 'public/kyc';
                $this->info("Using public directory: {$publicPath}");
            } elseif (is_dir($storagePath)) {
                $this->basePath = 'storage/app/kyc';
                $this->info("Using storage directory: {$storagePath}");
            } else {
                $this->error("KYC base directory not found. Checked:");
                $this->error("  - {$publicPath}");
                $this->error("  - {$storagePath}");
                return 1;
            }
        }

        if ($dryRun) {
            $this->warn("=== RUNNING IN DRY-RUN MODE (NO CHANGES WILL BE MADE) ===");
        } else {
            $this->warn("=== RUNNING IN REPAIR MODE (DATABASE WILL BE UPDATED) ===");
        }

        $this->newLine();

        // Get target users
        $users = User::whereIn('kyc_status', ['pending', 'approved'])
            ->whereNotNull('kyc_submitted_at')
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users found with kyc_status "pending" or "approved".');
            return 0;
        }

        $this->info("Found {$users->count()} users to process.");
        $this->newLine();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                $this->processUser($user, $dryRun, $fixMissing);
            } catch (\Exception $e) {
                $this->stats['errors'][] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ];
                Log::error('KYC audit error for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            $this->stats['total_processed']++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display summary report
        $this->displaySummary($dryRun);

        return 0;
    }

    /**
     * Process a single user's KYC images
     */
    private function processUser(User $user, bool $dryRun, bool $fixMissing)
    {
        // Validate user
        if (!$user->id || !in_array($user->kyc_status, ['pending', 'approved'])) {
            return;
        }

        $verbose = $this->getOutput()->isVerbose();
        $userDir = $this->getUserDirectory($user->id);
        $userDirExists = is_dir($userDir);
        
        if ($verbose) {
            $this->line("Processing User ID: {$user->id} ({$user->name})");
        }

        // Check if directory exists
        if (!$userDirExists) {
            $this->stats['missing_directories']++;
            
            // Check if paths exist in database but directory is missing
            $hasPaths = !empty($user->kyc_id_front_path) || 
                       !empty($user->kyc_id_back_path) || 
                       !empty($user->kyc_selfie_path);
            
            if ($verbose) {
                $this->warn("  ⚠ Directory missing: {$userDir}");
                if ($hasPaths) {
                    $this->line("     Front path: " . ($user->kyc_id_front_path ?: 'NULL'));
                    $this->line("     Back path: " . ($user->kyc_id_back_path ?: 'NULL'));
                    $this->line("     Selfie path: " . ($user->kyc_selfie_path ?: 'NULL'));
                    $this->error("     ⚠ Files referenced in database but directory doesn't exist!");
                } else {
                    $this->line("     All paths are NULL - no files in database");
                }
            }
            
            Log::warning('KYC directory missing for user', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'directory' => $userDir,
                'kyc_status' => $user->kyc_status,
                'has_paths_in_db' => $hasPaths,
                'front_path' => $user->kyc_id_front_path,
                'back_path' => $user->kyc_id_back_path,
                'selfie_path' => $user->kyc_selfie_path,
            ]);

            // Try alternative locations
            $alternativePaths = $this->checkAlternativeLocations($user->id);
            if (!empty($alternativePaths)) {
                if ($verbose) {
                    $this->info("     Found files in alternative locations:");
                    foreach ($alternativePaths as $location => $files) {
                        $this->line("       {$location}: " . implode(', ', $files));
                    }
                }
                Log::info('Found files in alternative locations', [
                    'user_id' => $user->id,
                    'alternative_paths' => $alternativePaths,
                ]);
            }

            // If all paths are NULL and directory doesn't exist, consider status change
            if (!$user->kyc_id_front_path && !$user->kyc_id_back_path && !$user->kyc_selfie_path) {
                if (!$dryRun) {
                    // Business logic: If no files exist and directory is missing, 
                    // set status to 'not_started' to allow resubmission
                    $user->update(['kyc_status' => 'not_started']);
                    $this->stats['status_changed']++;
                    if ($verbose) {
                        $this->warn("     → Status changed to 'not_started' (no files found)");
                    }
                    Log::info('KYC status changed to not_started (no files found)', [
                        'user_id' => $user->id,
                    ]);
                } elseif ($verbose) {
                    $this->line("     → Would change status to 'not_started' (dry-run)");
                }
            } elseif ($hasPaths && !$dryRun) {
                // If paths exist but directory is missing, this is a critical issue
                // Don't change status automatically - requires manual intervention
                if ($verbose) {
                    $this->error("     ⚠ CRITICAL: Database has paths but directory missing - manual intervention required!");
                }
            }

            return;
        }

        // Process each image type
        $verbose = $this->getOutput()->isVerbose();
        $frontResult = $this->verifyAndFixPath($user, 'front', $user->kyc_id_front_path, $userDir, $dryRun, $fixMissing, $verbose);
        $backResult = $this->verifyAndFixPath($user, 'back', $user->kyc_id_back_path, $userDir, $dryRun, $fixMissing, $verbose);
        $selfieResult = $this->verifyAndFixPath($user, 'selfie', $user->kyc_selfie_path, $userDir, $dryRun, $fixMissing, $verbose);

        // Determine if all paths are correct
        $allCorrect = $frontResult['status'] === 'correct' 
                   && $backResult['status'] === 'correct' 
                   && $selfieResult['status'] === 'correct';

        if ($allCorrect) {
            $this->stats['correct_paths']++;
        }

        // Check if all paths are NULL or missing
        $allMissing = ($frontResult['status'] === 'missing' || $frontResult['status'] === 'null')
                   && ($backResult['status'] === 'missing' || $backResult['status'] === 'null' || !$user->kyc_id_back_path)
                   && ($selfieResult['status'] === 'missing' || $selfieResult['status'] === 'null');

        if ($allMissing && !$dryRun) {
            // Business logic: If all files are missing, set status to 'not_started'
            $user->update(['kyc_status' => 'not_started']);
            $this->stats['status_changed']++;
            Log::info('KYC status changed to not_started (all files missing)', [
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Verify and fix a single image path
     */
    private function verifyAndFixPath(User $user, string $type, ?string $dbPath, string $userDir, bool $dryRun, bool $fixMissing, bool $verbose = false): array
    {
        $result = [
            'status' => 'unknown',
            'action' => 'none',
            'old_path' => $dbPath,
            'new_path' => null,
        ];

        // Case 1: Path is NULL or empty
        if (empty($dbPath)) {
            $result['status'] = 'null';
            
            // Try to find file in directory
            if ($fixMissing && is_dir($userDir)) {
                $foundFile = $this->findFileInDirectory($userDir, $type);
                
                if ($foundFile) {
                    $relativePath = $this->getRelativePath($user->id, $foundFile);
                    
                    if (!$dryRun) {
                        $updateField = "kyc_id_{$type}_path";
                        $user->update([$updateField => $relativePath]);
                        $this->stats['fixed_paths']++;
                        
                        if ($verbose) {
                            $this->warn("  → {$type}: Fixed NULL path → {$relativePath}");
                        }
                        
                        Log::info("KYC {$type} path fixed (was NULL)", [
                            'user_id' => $user->id,
                            'new_path' => $relativePath,
                        ]);
                    } elseif ($verbose) {
                        $this->warn("  → {$type}: Would fix NULL path → {$relativePath} (dry-run)");
                    }
                    
                    $result['status'] = 'fixed';
                    $result['action'] = 'updated_from_null';
                    $result['new_path'] = $relativePath;
                } else {
                    $result['action'] = 'logged_null';
                    Log::info("KYC {$type} path is NULL and no file found", [
                        'user_id' => $user->id,
                        'type' => $type,
                    ]);
                }
            }
            
            return $result;
        }

        // Case 2: Path exists in database - verify file exists
        $absolutePath = $this->getAbsolutePath($dbPath);
        
        if (file_exists($absolutePath) && is_file($absolutePath)) {
            $result['status'] = 'correct';
            $result['action'] = 'verified';
            if ($verbose) {
                $this->info("  ✓ {$type}: File exists at {$dbPath}");
            }
            return $result;
        }

        // Case 3: Path points to non-existent file
        $result['status'] = 'missing';
        $result['action'] = 'error_logged';
        $this->stats['unresolved_errors']++;

        if ($verbose) {
            $this->error("  ✗ {$type}: File NOT found at {$dbPath}");
            $this->line("     Expected: {$absolutePath}");
        }

        Log::error("KYC {$type} file not found at database path", [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => $type,
            'database_path' => $dbPath,
            'absolute_path' => $absolutePath,
            'user_directory' => $userDir,
            'suggestion' => 'Manual intervention required - file may have been moved or deleted',
        ]);

        // Try to find file in directory as a suggestion
        if (is_dir($userDir)) {
            $foundFile = $this->findFileInDirectory($userDir, $type);
            if ($foundFile) {
                $suggestedPath = $this->getRelativePath($user->id, $foundFile);
                Log::info("Potential match found for KYC {$type}", [
                    'user_id' => $user->id,
                    'suggested_path' => $suggestedPath,
                    'old_path' => $dbPath,
                ]);
            }
        }

        return $result;
    }

    /**
     * Get user's KYC directory path
     */
    private function getUserDirectory(int $userId): string
    {
        if ($this->basePath === 'public/kyc') {
            return public_path("kyc/{$userId}");
        } elseif ($this->basePath === 'storage/app/kyc') {
            return storage_path("app/kyc/{$userId}");
        } else {
            // Custom path
            return rtrim($this->basePath, '/') . "/{$userId}";
        }
    }

    /**
     * Get absolute path from relative database path
     */
    private function getAbsolutePath(string $relativePath): string
    {
        // Normalize path
        $path = ltrim($relativePath, '/');
        
        // Remove common prefixes if present
        $path = preg_replace('#^(storage/app/|app/|public/)#', '', $path);
        
        // Try public first
        if ($this->basePath === 'public/kyc' || str_starts_with($path, 'kyc/')) {
            $publicPath = public_path($path);
            if (file_exists($publicPath)) {
                return $publicPath;
            }
        }
        
        // Try storage
        $storagePath = storage_path('app/' . $path);
        if (file_exists($storagePath)) {
            return $storagePath;
        }
        
        // Return the most likely path based on base path
        if ($this->basePath === 'public/kyc') {
            return public_path($path);
        } else {
            return storage_path('app/' . $path);
        }
    }

    /**
     * Get relative path for database storage
     */
    private function getRelativePath(int $userId, string $filename): string
    {
        return "kyc/{$userId}/{$filename}";
    }

    /**
     * Find a file in directory that might match the type
     */
    private function findFileInDirectory(string $directory, string $type): ?string
    {
        if (!is_dir($directory)) {
            return null;
        }

        try {
            $files = array_diff(scandir($directory), ['.', '..']);
            
            // Look for files that might match the type
            // Priority: files with type in name, then by order
            $typeMatches = [];
            $otherFiles = [];
            
            foreach ($files as $file) {
                $filePath = $directory . '/' . $file;
                if (is_file($filePath)) {
                    $lowerFile = strtolower($file);
                    if (str_contains($lowerFile, $type)) {
                        $typeMatches[] = $file;
                    } else {
                        $otherFiles[] = $file;
                    }
                }
            }
            
            // Return first type match, or first file if no match
            if (!empty($typeMatches)) {
                return $typeMatches[0];
            }
            
            // For type-based selection if no name match
            if (!empty($otherFiles)) {
                // Sort by modification time (oldest first)
                usort($otherFiles, function($a, $b) use ($directory) {
                    return filemtime($directory . '/' . $a) <=> filemtime($directory . '/' . $b);
                });
                
                // Return based on type order: front (0), back (1), selfie (2 or last)
                $fileCount = count($otherFiles);
                if ($type === 'front' && $fileCount >= 1) {
                    return $otherFiles[0];
                } elseif ($type === 'back' && $fileCount >= 2) {
                    return $otherFiles[1];
                } elseif ($type === 'selfie' && $fileCount >= 1) {
                    return $otherFiles[$fileCount - 1]; // Last file
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error scanning KYC directory', [
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check alternative locations for user's KYC files
     */
    private function checkAlternativeLocations(int $userId): array
    {
        $alternatives = [];
        
        // Check public/kyc if we're using storage
        if ($this->basePath === 'storage/app/kyc') {
            $publicDir = public_path("kyc/{$userId}");
            if (is_dir($publicDir)) {
                $files = array_diff(scandir($publicDir), ['.', '..']);
                if (!empty($files)) {
                    $alternatives['public/kyc'] = $files;
                }
            }
        }
        
        // Check storage/app/kyc if we're using public
        if ($this->basePath === 'public/kyc') {
            $storageDir = storage_path("app/kyc/{$userId}");
            if (is_dir($storageDir)) {
                $files = array_diff(scandir($storageDir), ['.', '..']);
                if (!empty($files)) {
                    $alternatives['storage/app/kyc'] = $files;
                }
            }
        }
        
        return $alternatives;
    }

    /**
     * Display summary report
     */
    private function displaySummary(bool $dryRun)
    {
        $this->info('=== AUDIT SUMMARY REPORT ===');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users Processed', $this->stats['total_processed']],
                ['Users with Correct Paths', $this->stats['correct_paths']],
                ['Paths Fixed', $this->stats['fixed_paths']],
                ['Missing Directories', $this->stats['missing_directories']],
                ['Unresolved Path Errors', $this->stats['unresolved_errors']],
                ['Status Changes', $this->stats['status_changed']],
                ['Errors Encountered', count($this->stats['errors'])],
            ]
        );

        if (!empty($this->stats['errors'])) {
            $this->newLine();
            $this->warn('Errors encountered:');
            foreach ($this->stats['errors'] as $error) {
                $this->error("  User ID {$error['user_id']}: {$error['error']}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('This was a dry-run. No changes were made to the database.');
            $this->info('Run without --dry-run to apply fixes.');
        } else {
            $this->newLine();
            $this->info('Database updates have been applied.');
        }

        $this->newLine();
        $this->info("Base path used: {$this->basePath}");
        $this->info("Log file: " . storage_path('logs/laravel.log'));
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class Reencrypt2FACodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '2fa:reencrypt 
                            {--user-id= : Re-encrypt/regenerate codes for specific user ID}
                            {--all : Process all users with 2FA enabled}
                            {--regenerate : Regenerate codes if re-encryption fails}
                            {--force-regenerate : Force regeneration for all selected users (ignores existing codes)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-encrypt 2FA recovery codes for users (useful after APP_KEY changes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $all = $this->option('all');
        $regenerate = $this->option('regenerate');
        $forceRegenerate = $this->option('force-regenerate');

        if (!$userId && !$all) {
            $this->error('Please specify --user-id=<id> or --all');
            return 1;
        }

        // Build query: include users with 2FA enabled (two_factor_secret) OR recovery codes
        $query = User::where(function($q) use ($forceRegenerate) {
            // If force-regenerate, include all users with 2FA enabled (even if no recovery codes)
            if ($forceRegenerate) {
                $q->whereNotNull('two_factor_secret');
            } else {
                // Otherwise, only process users who have recovery codes (to re-encrypt or regenerate)
                $q->whereNotNull('two_factor_recovery_codes');
            }
        });
        
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        
        if ($users->isEmpty()) {
            $this->info('No users with 2FA enabled found.');
            return 0;
        }

        $this->info("Found {$users->count()} user(s) with 2FA enabled.");
        
        $successCount = 0;
        $failedCount = 0;
        $regeneratedCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            $this->line("Processing user {$user->id} ({$user->email})...");
            
            // Force regenerate: skip re-encryption, directly regenerate
            if ($forceRegenerate) {
                if (!$user->two_factor_secret) {
                    $this->warn("  ⚠ User {$user->id} has no 2FA secret (2FA not enabled)");
                    $skippedCount++;
                    continue;
                }

                try {
                    $generate = new GenerateNewRecoveryCodes();
                    $generate($user);
                    $this->info("  ✓ Regenerated recovery codes for user {$user->id}");
                    $regeneratedCount++;
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to regenerate codes for user {$user->id}: " . $e->getMessage());
                    Log::error("Failed to regenerate 2FA codes for user {$user->id}", [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failedCount++;
                }
                continue;
            }

            // Check if codes are corrupted or missing
            $hasCodes = !empty($user->two_factor_recovery_codes);
            $isCorrupted = $hasCodes && $user->hasCorruptedRecoveryCodes();

            if ($isCorrupted) {
                $this->warn("  Recovery codes are corrupted for user {$user->id}");
                
                if ($regenerate) {
                    try {
                        if ($user->two_factor_secret) {
                            $generate = new GenerateNewRecoveryCodes();
                            $generate($user);
                            $this->info("  ✓ Regenerated recovery codes for user {$user->id}");
                            $regeneratedCount++;
                        } else {
                            $user->two_factor_recovery_codes = null;
                            $user->saveQuietly();
                            $this->info("  ✓ Cleared corrupted codes (2FA disabled) for user {$user->id}");
                        }
                    } catch (\Exception $e) {
                        $this->error("  ✗ Failed to regenerate codes for user {$user->id}: " . $e->getMessage());
                        $failedCount++;
                    }
                } else {
                    $this->warn("  Use --regenerate to create new codes");
                    $failedCount++;
                }
            } elseif (!$hasCodes && $user->two_factor_secret) {
                // User has 2FA enabled but no recovery codes - offer to generate
                $this->warn("  User {$user->id} has 2FA enabled but no recovery codes");
                if ($regenerate) {
                    try {
                        $generate = new GenerateNewRecoveryCodes();
                        $generate($user);
                        $this->info("  ✓ Generated new recovery codes for user {$user->id}");
                        $regeneratedCount++;
                    } catch (\Exception $e) {
                        $this->error("  ✗ Failed to generate codes for user {$user->id}: " . $e->getMessage());
                        $failedCount++;
                    }
                } else {
                    $this->warn("  Use --regenerate to generate new codes");
                    $skippedCount++;
                }
            } else {
                // Try to re-encrypt (useful if APP_KEY changed but codes are still valid)
                if ($user->reencryptRecoveryCodes()) {
                    $this->info("  ✓ Re-encrypted recovery codes for user {$user->id}");
                    $successCount++;
                } else {
                    $this->warn("  ⚠ Could not re-encrypt codes for user {$user->id}");
                    $failedCount++;
                }
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Successfully re-encrypted: {$successCount}");
        $this->info("  Regenerated: {$regeneratedCount}");
        $this->info("  Skipped: {$skippedCount}");
        $this->info("  Failed: {$failedCount}");

        return 0;
    }
}

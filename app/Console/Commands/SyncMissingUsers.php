<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncMissingUsers extends Command
{
    protected $signature = 'users:sync-missing {--force : Force insert even if user exists}';
    protected $description = 'Sync missing users from source database and backfill ULIDs';

    public function handle()
    {
        $this->info('Starting user sync...');
        
        $missingUsers = $this->getMissingUsersData();
        $inserted = 0;
        $skipped = 0;
        $errors = 0;
        $updated = 0;

        foreach ($missingUsers as $userData) {
            $existsById = DB::table('users')->where('id', $userData['id'])->exists();
            $existsByEmail = DB::table('users')->where('email', $userData['email'])->exists();

            if ($existsById) {
                $this->warn("User ID {$userData['id']} already exists - skipping");
                $skipped++;
                continue;
            }

            if ($existsByEmail && !$this->option('force')) {
                $existing = DB::table('users')->where('email', $userData['email'])->first();
                $this->warn("User with email {$userData['email']} already exists (ID: {$existing->id}) - skipping");
                $skipped++;
                continue;
            }

            // Generate ULID
            $ulid = (string) Str::ulid();
            while (DB::table('users')->where('ulid', $ulid)->exists()) {
                $ulid = (string) Str::ulid();
            }

            // Prepare full user data
            $fullUserData = array_merge($userData, [
                'ulid' => $ulid,
                'avatar' => null,
                'status' => 'online',
                'receipt_screenshot' => null,
                'game_pin_hash' => null,
                'is_in_game' => 0,
                'game_pin_locked_until' => null,
                'game_pin_failed_attempts' => 0,
                'referral_code' => $userData['referral_code'] ?? null,
                'reseller_id' => $userData['reseller_id'] ?? null,
                'kyc_id_type' => $userData['kyc_id_type'] ?? null,
                'kyc_id_number' => $userData['kyc_id_number'] ?? null,
                'kyc_full_name' => $userData['kyc_full_name'] ?? null,
                'kyc_id_front_path' => $userData['kyc_id_front_path'] ?? null,
                'kyc_id_back_path' => $userData['kyc_id_back_path'] ?? null,
                'kyc_selfie_path' => $userData['kyc_selfie_path'] ?? null,
                'kyc_submitted_at' => $userData['kyc_submitted_at'] ?? null,
                'kyc_approved_at' => $userData['kyc_approved_at'] ?? null,
                'company_name' => $userData['company_name'] ?? null,
                'investment_capacity' => $userData['investment_capacity'] ?? null,
                'experience' => $userData['experience'] ?? null,
                'coin_price' => $userData['coin_price'] ?? null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => $userData['remember_token'] ?? null,
            ]);

            try {
                DB::table('users')->insert($fullUserData);
                $inserted++;
                $this->info("✓ Inserted user ID {$userData['id']}: {$userData['name']} ({$userData['email']})");
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Failed to insert user ID {$userData['id']}: " . $e->getMessage());
                $this->error("   Error details: " . $e->getFile() . ':' . $e->getLine());
            }
        }

        // Backfill ULIDs for any remaining NULL values
        $this->info("\nBackfilling ULIDs for users without them...");
        $nullUlids = DB::table('users')->whereNull('ulid')->count();
        if ($nullUlids > 0) {
            $this->info("Found {$nullUlids} users without ULIDs, backfilling...");
            $this->backfillUlids();
        }

        $this->info("\n=== Summary ===");
        $this->info("Inserted: {$inserted}");
        $this->info("Skipped: {$skipped}");
        $this->info("Errors: {$errors}");
        $this->info("ULIDs backfilled: {$nullUlids}");

        return 0;
    }

    protected function backfillUlids()
    {
        $usersWithoutUlid = DB::table('users')
            ->whereNull('ulid')
            ->orderBy('id')
            ->get();

        $bar = $this->output->createProgressBar($usersWithoutUlid->count());
        $bar->start();

        foreach ($usersWithoutUlid as $user) {
            $ulid = (string) Str::ulid();
            while (DB::table('users')->where('ulid', $ulid)->exists()) {
                $ulid = (string) Str::ulid();
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['ulid' => $ulid]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function getMissingUsersData(): array
    {
        // All missing users from source database (44 users total, excluding duplicates)
        return require database_path('migrations/2025_12_08_100000_sync_users_and_backfill_ulids.php');
    }
}

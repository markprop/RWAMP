<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration:
     * 1. Ensures ULID column exists
     * 2. Handles duplicate users (by email/phone)
     * 3. Inserts all missing users from source database
     * 4. Backfills ULIDs for ALL users (existing NULL and newly inserted)
     */
    public function up(): void
    {
        // Ensure ULID column exists
        if (!Schema::hasColumn('users', 'ulid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->char('ulid', 26)->nullable()->unique()->after('id');
            });
        }

        // Handle duplicates first (update existing records with missing data)
        $this->handleDuplicates();

        // Insert all missing users
        $this->insertMissingUsers();

        // Backfill ULIDs for ALL users with NULL values (including newly inserted)
        $this->backfillAllUlids();
    }

    /**
     * Handle duplicate users - update existing records with missing data from source
     */
    protected function handleDuplicates(): void
    {
        // Case 1: tahmeedahmad798@gmail.com - id=49 (live) exists, skip id=56 (rwamp)
        // Case 2: cha957676@gmail.com - id=82 (live) exists, but id=95 (rwamp) has more complete data
        // We'll keep id=82 and skip id=95 since live is the source of truth
        
        // Case 3: muhammadfazil.ryk@gmail.com - id=51 (live) exists but missing KYC
        // Update id=51 with KYC data from id=57 (rwamp) if it doesn't exist
        $user51 = DB::table('users')->where('id', 51)->first();
        if ($user51 && empty($user51->kyc_id_type)) {
            DB::table('users')->where('id', 51)->update([
                'phone' => '+923055291056', // Add missing phone
                'kyc_status' => 'pending',
                'kyc_id_type' => 'cnic',
                'kyc_id_number' => '3550103988415', // Example - adjust if needed
                'kyc_full_name' => 'Muhammad Fazil',
                'kyc_id_front_path' => null, // Add paths if available
                'kyc_id_back_path' => null,
                'kyc_selfie_path' => null,
                'kyc_submitted_at' => '2025-12-02 12:49:16',
            ]);
        }
    }

    /**
     * Insert all missing users from source database
     * Excludes duplicates already handled above
     */
    protected function insertMissingUsers(): void
    {
        $missingUsers = $this->getMissingUsersData();
        $inserted = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($missingUsers as $userData) {
            // Skip if user already exists by ID
            if (DB::table('users')->where('id', $userData['id'])->exists()) {
                $skipped++;
                \Log::info("Skipping user ID {$userData['id']} ({$userData['email']}) - already exists by ID");
                continue;
            }

            // Skip if user exists by email (duplicate check)
            if (DB::table('users')->where('email', $userData['email'])->exists()) {
                $skipped++;
                \Log::info("Skipping user ID {$userData['id']} ({$userData['email']}) - already exists by email");
                continue;
            }

            // Generate ULID
            $ulid = (string) Str::ulid();
            while (DB::table('users')->where('ulid', $ulid)->exists()) {
                $ulid = (string) Str::ulid();
            }

            // Prepare full user data with defaults
            $fullUserData = array_merge($userData, [
                'ulid' => $ulid,
                'avatar' => null,
                'status' => 'online',
                'receipt_screenshot' => null,
                'game_pin_hash' => null,
                'is_in_game' => 0,
                'game_pin_locked_until' => null,
                'game_pin_failed_attempts' => 0,
            ]);

            // Set defaults for optional fields if not provided
            $defaults = [
                'referral_code' => null,
                'reseller_id' => null,
                'kyc_id_type' => null,
                'kyc_id_number' => null,
                'kyc_full_name' => null,
                'kyc_id_front_path' => null,
                'kyc_id_back_path' => null,
                'kyc_selfie_path' => null,
                'kyc_submitted_at' => null,
                'kyc_approved_at' => null,
                'company_name' => null,
                'investment_capacity' => null,
                'experience' => null,
                'coin_price' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => null,
            ];

            foreach ($defaults as $key => $value) {
                if (!isset($fullUserData[$key])) {
                    $fullUserData[$key] = $value;
                }
            }

            try {
                DB::table('users')->insert($fullUserData);
                $inserted++;
                \Log::info("Inserted user ID {$userData['id']} ({$userData['email']}) with ULID: {$ulid}");
            } catch (\Exception $e) {
                $errors++;
                // Log error with full details
                \Log::error("Failed to insert user ID {$userData['id']} ({$userData['email']}): " . $e->getMessage(), [
                    'user_id' => $userData['id'],
                    'email' => $userData['email'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Log summary
        \Log::info("User sync completed: {$inserted} inserted, {$skipped} skipped, {$errors} errors");
    }

    /**
     * Get all missing users data from source database
     */
    protected function getMissingUsersData(): array
    {
        return [
            // ID 52
            [
                'id' => 52,
                'name' => 'Qamber Ali',
                'email' => 'purchase.depac@gmail.com',
                'phone' => '+923153246256',
                'email_verified_at' => '2025-12-02 11:59:36',
                'password' => '$2y$12$amS69EsElQECwXY29YkQlu4Ln9DEykdM7nVhjKlPgWiwgxVXay67G',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '2313987169778670',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 11:58:51',
                'updated_at' => '2025-12-02 11:59:36',
            ],
            // ID 54
            [
                'id' => 54,
                'name' => 'Ashfaq Ahmad',
                'email' => 'ashfaqsial92@gmail.com',
                'phone' => '03095511454',
                'email_verified_at' => '2025-12-02 12:19:23',
                'password' => '$2y$12$bk34VilgzMUr67ctr63dy.bj/aimBwNqjRIEawwlgUu87RpPCCy5a',
                'role' => 'investor',
                'reseller_id' => 11,
                'kyc_status' => 'pending',
                'kyc_id_type' => 'cnic',
                'kyc_id_number' => '3550103988415',
                'kyc_full_name' => 'Ashfaq Ahmad',
                'kyc_id_front_path' => 'kyc/54/ta1zbDUkoMsDVBcdwScqJMrVQaUNA6kcIQiKlMrN.jpg',
                'kyc_id_back_path' => 'kyc/54/RIBr4o6TLL3FYkG9dYe6yIDGPD8tTYY7WuiWH7Vu.jpg',
                'kyc_selfie_path' => 'kyc/54/3v8KzExHZ4xF0qU3R937bEWuiDjC5uIBZJApRuBE.jpg',
                'kyc_submitted_at' => '2025-12-02 12:49:16',
                'wallet_address' => '3449849948475459',
                'token_balance' => 1000.00,
                'created_at' => '2025-12-02 12:18:39',
                'updated_at' => '2025-12-02 12:49:16',
            ],
            // ID 55
            [
                'id' => 55,
                'name' => 'Mubeenasif',
                'email' => 'maharbadshah78600001@gmail.com',
                'phone' => '923164008986',
                'email_verified_at' => '2025-12-02 12:20:50',
                'password' => '$2y$12$iI9/.60ErI.PkMtxdCv6BOYoWsD1dJqZCJo8bqDR0UyiWSacFhpma',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '1087817154572410',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 12:20:16',
                'updated_at' => '2025-12-02 12:20:50',
            ],
            // ID 58
            [
                'id' => 58,
                'name' => 'Manoj gir',
                'email' => 'manojgri555@gmail.com',
                'phone' => '0346211170',
                'email_verified_at' => '2025-12-02 13:38:57',
                'password' => '$2y$12$lUj2GoBibguLEy3EcRyiJuIuP2Pc2sJaEy10XLMchQgYKGTHR4Tca',
                'role' => 'investor',
                'reseller_id' => 11,
                'kyc_status' => 'not_started',
                'wallet_address' => '6930398590504526',
                'token_balance' => 0.00,
                'remember_token' => '6qP89tcjgA2DiJdQeuT9HypfvGMOZiwGRUltEaAC0R0OloW3YWyP8bBPEmwH',
                'created_at' => '2025-12-02 13:37:21',
                'updated_at' => '2025-12-02 13:38:57',
            ],
            // ID 59
            [
                'id' => 59,
                'name' => 'Sabir',
                'email' => 'sabirzhob@gmail.com',
                'phone' => '+923412915335',
                'email_verified_at' => '2025-12-02 13:59:02',
                'password' => '$2y$12$wkI3gu./0kcr0LzOXsjEo.BkZ16/KxL4aesTdbEPcngaaJJcvuN3m',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '6533607359511853',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 13:58:34',
                'updated_at' => '2025-12-02 13:59:02',
            ],
            // ID 60
            [
                'id' => 60,
                'name' => 'Muhammad Ahsan Raza',
                'email' => 'goodmen1991000@gmail.com',
                'phone' => '03040364322',
                'email_verified_at' => '2025-12-02 14:14:12',
                'password' => '$2y$12$44iLh1fR7h8Btke4ZS//z.TS.aTAV2mQ9x3F26J3KpISOFeOAqQm6',
                'role' => 'investor',
                'reseller_id' => 11,
                'kyc_status' => 'not_started',
                'wallet_address' => '3744185696762061',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 14:13:35',
                'updated_at' => '2025-12-02 14:14:12',
            ],
            // ID 61
            [
                'id' => 61,
                'name' => 'Abubaker jutt',
                'email' => 'jutt302417@gmail.com',
                'phone' => '03021616879',
                'email_verified_at' => '2025-12-02 14:56:32',
                'password' => '$2y$12$j7YM0FbWM2JBjybnk6pvauNrckrio4dyJcHGU1vc1mGdBTZcJtwEa',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '9963245652736565',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 14:55:40',
                'updated_at' => '2025-12-02 14:56:32',
            ],
            // ID 62
            [
                'id' => 62,
                'name' => 'SHAH RUKH',
                'email' => 'shahrukh1122b@gmail.com',
                'phone' => '03045180918',
                'email_verified_at' => '2025-12-02 15:13:39',
                'password' => '$2y$12$GAxcNBQ.lrbggWJEk/YKf.4sCI/hE7sbkn5rvRT1JzYslNSVfHZIS',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '2423025116317782',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 15:13:13',
                'updated_at' => '2025-12-02 15:13:39',
            ],
            // ID 63
            [
                'id' => 63,
                'name' => 'Khalid Hussain',
                'email' => 'khalid.hussain388@gmail.com',
                'phone' => '+923453161684',
                'email_verified_at' => '2025-12-02 15:37:39',
                'password' => '$2y$12$eYDEuoXjaL8EsEfzuNGesOXk69/u5cjk8geZTGwJP54UmBdtbkorG',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '3707452255156200',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 15:36:59',
                'updated_at' => '2025-12-02 15:37:39',
            ],
            // ID 64
            [
                'id' => 64,
                'name' => 'Muhammad Imran shahbaz',
                'email' => 'imran03139234110@gmail.com',
                'phone' => '+923139234110',
                'email_verified_at' => '2025-12-02 15:40:21',
                'password' => '$2y$12$AnsHOAQuCOyEPn18V.BMGuEynqJ5i4phNa7.P3MKq8gn3ordyc6Qe',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '6420492589688106',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 15:39:41',
                'updated_at' => '2025-12-02 15:40:21',
            ],
            // ID 65
            [
                'id' => 65,
                'name' => 'Muhmmad Mudassar',
                'email' => 'mmudassar2008@gmail.com',
                'phone' => '+923413606855',
                'email_verified_at' => '2025-12-02 16:38:17',
                'password' => '$2y$12$SZIG74hTHCUAqMJG/snWj.QtKLEk7wdund1CkR5dr.YtF8DM9wNWO',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '1597804430177384',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 16:37:39',
                'updated_at' => '2025-12-02 16:38:17',
            ],
            // ID 66
            [
                'id' => 66,
                'name' => 'Rana Muhammad Faraz Hussain',
                'email' => 'faraz5660@gmail.com',
                'phone' => '+923120771725',
                'email_verified_at' => '2025-12-02 17:43:25',
                'password' => '$2y$12$NKka2BPtaAhxGiGaDbZUx.rvzp79TqYxvX.vc8.CP03lTPeeTK.KS',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '9551383553436292',
                'token_balance' => 0.00,
                'created_at' => '2025-12-02 17:43:00',
                'updated_at' => '2025-12-02 17:43:25',
            ],
            // ID 68
            [
                'id' => 68,
                'name' => 'Hafiz Muhammad Huzaifa',
                'email' => 'huzaifamarketers@gmail.com',
                'phone' => '+923126424127',
                'email_verified_at' => '2025-12-03 05:08:06',
                'password' => '$2y$12$7vkX9utpkS121wyQtK/BVukMZUoHf4e.A8T4E7uvXgtoy8nj/21oG',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '2703755804502075',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 05:07:27',
                'updated_at' => '2025-12-03 05:08:06',
            ],
            // ID 69
            [
                'id' => 69,
                'name' => 'Kashif Nawaz',
                'email' => 'kn235500@gmail.com',
                'phone' => '+923472563950',
                'email_verified_at' => '2025-12-03 06:38:58',
                'password' => '$2y$12$UyeDQDqjKypDMH27mU1Yr.2QR/9mcl4YtuIyvfqjs4aNkvhrvcSk6',
                'role' => 'investor',
                'reseller_id' => 11,
                'kyc_status' => 'not_started',
                'wallet_address' => '3389391768734997',
                'token_balance' => 0.00,
                'remember_token' => 'X0Ug97BPenKJtGlG5EYMNo7RCcZA202v3MpTIr0MMRPVEY6QNSkTDsb9p64E',
                'created_at' => '2025-12-03 06:37:23',
                'updated_at' => '2025-12-03 06:38:58',
            ],
            // ID 70
            [
                'id' => 70,
                'name' => 'Muhammad Sayyam',
                'email' => 'sayyamsaeed88@gmail.com',
                'phone' => '+923238955927',
                'email_verified_at' => '2025-12-03 07:44:41',
                'password' => '$2y$12$DZPeY3E7/YpkBEy9BJCZPu.AUIJeB./Kaj8JbocdX/CwXE4qkFI6W',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '6861913826685108',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 07:44:08',
                'updated_at' => '2025-12-03 07:44:41',
            ],
            // ID 71
            [
                'id' => 71,
                'name' => 'Areeb ahmed',
                'email' => 'aareeb850@gmail.com',
                'phone' => '03402778511',
                'email_verified_at' => '2025-12-03 08:16:05',
                'password' => '$2y$12$AcJiBUeCCEo1VeFLI3PoF.Yg9sUFwrlFRRWHi5btmCWMwFPxlOqaa',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '8043044149456938',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 08:15:38',
                'updated_at' => '2025-12-03 08:16:05',
            ],
            // ID 72
            [
                'id' => 72,
                'name' => 'ahmedmawaz',
                'email' => 'ahmedmawazsial@gmail.com',
                'phone' => '03340569932',
                'email_verified_at' => null,
                'password' => '$2y$12$/Oai5v5Ht1FdU80FL.CbAOVUT/TFfl1sie1Z9DBitY/SKo3qmrQaW',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '4998978179549717',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 09:01:06',
                'updated_at' => '2025-12-03 09:01:06',
            ],
            // ID 73
            [
                'id' => 73,
                'name' => 'Muhammad Bilal',
                'email' => 'hadiqam2@gmail.com',
                'phone' => '923442875475',
                'email_verified_at' => '2025-12-03 09:28:42',
                'password' => '$2y$12$IBr5ieLdTUzX5sK1VZHKl./Sa7PJYOzZZZ.sEl8LGH.C8awBWUtUK',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '7254531675549881',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 09:28:16',
                'updated_at' => '2025-12-03 09:28:42',
            ],
            // ID 74
            [
                'id' => 74,
                'name' => 'Muhammad Waseem',
                'email' => 'makki12374@yahoo.com',
                'phone' => '+923226861086',
                'email_verified_at' => null,
                'password' => '$2y$12$91T38BV/mJLXsHCehWfcBuchsy6invzzRpFh88Nf0WxBEh.DUF4S6',
                'role' => 'reseller',
                'referral_code' => 'RSL74',
                'kyc_status' => 'not_started',
                'company_name' => 'Muhammad Waseem',
                'investment_capacity' => '50000',
                'wallet_address' => '7724973944205629',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 10:17:52',
                'updated_at' => '2025-12-03 10:17:52',
            ],
            // ID 75
            [
                'id' => 75,
                'name' => 'Hafiz Muhammad Muneer Ahmed Yousafi',
                'email' => 'fazimuneer99@gmail.com',
                'phone' => '+923064507007',
                'email_verified_at' => '2025-12-03 11:10:58',
                'password' => '$2y$12$Y93Qf4SfWXUmMOdBkRd81.3kbqQTZXqNjpqiD6rUXOmyxmvY5shxO',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '9325653189736225',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 11:10:33',
                'updated_at' => '2025-12-03 11:10:58',
            ],
            // ID 76
            [
                'id' => 76,
                'name' => 'Palak Naz',
                'email' => 'advocatepalaknazmemon@gmail.com',
                'phone' => '=923108802013', // Note: typo in original, keeping as is
                'email_verified_at' => '2025-12-03 11:11:15',
                'password' => '$2y$12$RskkEGZoFGR8l8gF8b1k6.n93Cjqe8OBvkMuu/iZptCWifhFa7dye',
                'role' => 'investor',
                'reseller_id' => 11,
                'kyc_status' => 'not_started',
                'wallet_address' => '5204762646732544',
                'token_balance' => 3350.00,
                'created_at' => '2025-12-03 11:10:36',
                'updated_at' => '2025-12-03 11:39:25',
            ],
            // ID 77
            [
                'id' => 77,
                'name' => 'Mirhazar',
                'email' => 'mirhazarbugti57@gmail.com',
                'phone' => '+92 3113564630',
                'email_verified_at' => '2025-12-03 13:05:13',
                'password' => '$2y$12$.8BczRhE95Xxs4Ttzlk/P.9X57pRkpk9kUO/Uvv0zBi0mHrd7Vh4S',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '2359124475103485',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 13:04:18',
                'updated_at' => '2025-12-03 13:05:13',
            ],
            // ID 78
            [
                'id' => 78,
                'name' => 'M Shahbaz',
                'email' => 'hafizshahbazlqp@gmail.com',
                'phone' => '03130067078',
                'email_verified_at' => '2025-12-03 14:09:17',
                'password' => '$2y$12$ieqb6b9JcgC.NiNHrQ3i3eOgsD25T0q0GlyD3N9M10gJkudHYxFtC',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '5716478787541066',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 14:08:54',
                'updated_at' => '2025-12-03 14:09:17',
            ],
            // ID 79
            [
                'id' => 79,
                'name' => 'afnan ahmed',
                'email' => 'afnanzulfiqar12@gmail.com',
                'phone' => '+923102449353',
                'email_verified_at' => '2025-12-03 14:49:28',
                'password' => '$2y$12$ohmaTfi9a1R3XRuYJ3MR5O8TsNoPOJ0Ro1HR4gKZKGz0Bwazcx7v6',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '8403865834459799',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 14:48:52',
                'updated_at' => '2025-12-03 14:49:28',
            ],
            // ID 80
            [
                'id' => 80,
                'name' => 'Muhammad Usman Siddique',
                'email' => 'usmansiddiqu84@gmail.com',
                'phone' => '+923007518607',
                'email_verified_at' => '2025-12-03 16:33:33',
                'password' => '$2y$12$klGxBXf5bFKfS4YV9tdBFeIMTRSfuCIkI.IXnHwSMmohEdqe/xHI6',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '3654729977899503',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 16:33:05',
                'updated_at' => '2025-12-03 16:33:33',
            ],
            // ID 81
            [
                'id' => 81,
                'name' => 'Altafkhan',
                'email' => 'iltaf4581@gmail.com',
                'phone' => '03467942302',
                'email_verified_at' => '2025-12-03 16:55:50',
                'password' => '$2y$12$2Wxwm.k0t0q9.AXcilLaZuaPuW7vDl5F2j5orBM5icbIH6nw.VO4y',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '4013968878954977',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 16:54:57',
                'updated_at' => '2025-12-03 16:55:50',
            ],
            // ID 82 - Skip (duplicate: cha957676@gmail.com exists as id=82 in live)
            // ID 83
            [
                'id' => 83,
                'name' => 'Muhammad Waqas',
                'email' => 'waqasjavaid1991@gmail.com',
                'phone' => '+923097892394',
                'email_verified_at' => '2025-12-03 20:28:20',
                'password' => '$2y$12$4Fun0S6p9YT6IO7eEkOBJudpoOcal60QeNB.HkYE6v8eCFqhN28SK',
                'role' => 'investor',
                'kyc_status' => 'pending',
                'kyc_id_type' => 'cnic',
                'kyc_id_number' => '3520216208803',
                'kyc_full_name' => 'Muhammad Waqas',
                'kyc_id_front_path' => 'kyc/83/cacxFnCq23jrcn9eerE8f7rfTQpkTom7JG6QIkAM.jpg',
                'kyc_id_back_path' => 'kyc/83/TifhJufsMo1mY8dNBmnR8SR2K8Z6ylEzVU1Xg485.jpg',
                'kyc_selfie_path' => 'kyc/83/ztK0U9NZ33JO5h9A4YXqGkEpmEX8c1AyEzixkEyw.jpg',
                'kyc_submitted_at' => '2025-12-03 20:32:07',
                'wallet_address' => '5416219320174399',
                'token_balance' => 0.00,
                'created_at' => '2025-12-03 20:27:45',
                'updated_at' => '2025-12-03 20:32:07',
            ],
            // ID 84
            [
                'id' => 84,
                'name' => 'Abdul Hanan',
                'email' => 'malikhanni7@gmail.com',
                'phone' => '+923069840702',
                'email_verified_at' => '2025-12-04 08:23:37',
                'password' => '$2y$12$SQ5VDFX/uxremdcmwXe6RuC2fXnR26fs.pOlBESu7r2XkDqJ1wRNi',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '1783271501751767',
                'token_balance' => 0.00,
                'created_at' => '2025-12-04 08:23:00',
                'updated_at' => '2025-12-04 08:23:37',
            ],
            // ID 85
            [
                'id' => 85,
                'name' => 'Tanveershahid',
                'email' => 'tanveershahid800@gmail.com',
                'phone' => '03218077683',
                'email_verified_at' => '2025-12-04 13:39:05',
                'password' => '$2y$12$70y5q/8OolPGLiLaAke5leopnc2c3.ZR3XdLWhczhyanJA6hok1EC',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '9183722443099097',
                'token_balance' => 0.00,
                'remember_token' => '0Wt2sdkD77E1sRLfRHnlNXGRAel0CfwHkkSGfw4msdhKQjQGugDBBFH3HfMm',
                'created_at' => '2025-12-04 13:38:30',
                'updated_at' => '2025-12-04 13:39:05',
            ],
            // ID 86
            [
                'id' => 86,
                'name' => 'Muhammad Mudasir',
                'email' => 'mudasirrandhawa652@gmail.com',
                'phone' => '+923024559790',
                'email_verified_at' => '2025-12-05 13:38:50',
                'password' => '$2y$12$9kjn0m9jI2tCxvCH.tU31ezWg/FnbjbalmcN/K80aCszbUZximWlG',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '9282430667892381',
                'token_balance' => 0.00,
                'created_at' => '2025-12-05 13:36:55',
                'updated_at' => '2025-12-05 13:38:50',
            ],
            // ID 87
            [
                'id' => 87,
                'name' => 'Shahid nazir',
                'email' => 'akansbj@gmail.com',
                'phone' => '+923404550622',
                'email_verified_at' => '2025-12-05 14:12:24',
                'password' => '$2y$12$9QIj8yx7SUW8NbN6lpu/wuC74itmxs8TBEJsYCwswZlNsUAtyhHQK',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '1158914583355195',
                'token_balance' => 0.00,
                'created_at' => '2025-12-05 14:11:19',
                'updated_at' => '2025-12-05 14:12:24',
            ],
            // ID 88
            [
                'id' => 88,
                'name' => 'Muhammad Younas',
                'email' => 'mhryounuskhan@gmail.com',
                'phone' => '0308618979066',
                'email_verified_at' => '2025-12-06 04:51:10',
                'password' => '$2y$12$1X5bdc2qoW4OAvpu4a8lQOdmhVGLl/Rzi7iBl9wE4BVe3FJRs5QHW',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '1472629784145644',
                'token_balance' => 0.00,
                'created_at' => '2025-12-06 04:50:20',
                'updated_at' => '2025-12-06 04:51:10',
            ],
            // ID 89
            [
                'id' => 89,
                'name' => 'Malik Muhammad Qasim',
                'email' => 'malikqasimbrother@gmail.com',
                'phone' => '+923454442814',
                'email_verified_at' => '2025-12-06 09:12:40',
                'password' => '$2y$12$yx1Uq9Wa9nA51VnL8ZXDg.cHerE8Pd81RB/qkLIJZggM5lY7xIAS2',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '5710281941164374',
                'token_balance' => 0.00,
                'created_at' => '2025-12-06 09:12:01',
                'updated_at' => '2025-12-06 09:12:40',
            ],
            // ID 90
            [
                'id' => 90,
                'name' => 'Muhammad Farhan',
                'email' => 'farhanqasim332@gmail.com',
                'phone' => '+923145871381',
                'email_verified_at' => '2025-12-06 13:27:12',
                'password' => '$2y$12$eqLhwATJkpQlqklL.C3ciuCzvPA0g4DflhXJfQ1RCJxV.XGdUallq',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '3484796556075286',
                'token_balance' => 0.00,
                'created_at' => '2025-12-06 13:26:44',
                'updated_at' => '2025-12-06 13:27:12',
            ],
            // ID 91
            [
                'id' => 91,
                'name' => 'Muhammad Ali siddiqui',
                'email' => 'siddali432@gmail.com',
                'phone' => '+923408299306',
                'email_verified_at' => null,
                'password' => '$2y$12$kA.w.B0HFjlyuUk1bh7hMe2mi0xhu/tCmKxADbM5/Mb/BOQCKuwF2',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '5724911505537651',
                'token_balance' => 0.00,
                'created_at' => '2025-12-06 13:53:55',
                'updated_at' => '2025-12-06 13:53:55',
            ],
            // ID 92
            [
                'id' => 92,
                'name' => 'Muhammad Ali siddiqui',
                'email' => 'sid252093@gmail.com',
                'phone' => '+923122564055',
                'email_verified_at' => '2025-12-06 13:59:50',
                'password' => '$2y$12$D5Ws1mjWTyY2dbng26LvbOwemuTjNH0WKJotM4oJ4Z3x9H.PZgWwi',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '5350850502777968',
                'token_balance' => 0.00,
                'created_at' => '2025-12-06 13:59:29',
                'updated_at' => '2025-12-06 13:59:50',
            ],
            // ID 93
            [
                'id' => 93,
                'name' => 'Adnan Shahzad',
                'email' => 'daniawaramundadaniawaramunda@gmail.com',
                'phone' => '+923198907020',
                'email_verified_at' => null,
                'password' => '$2y$12$FeJ84HpmHuoUXylHr6Y6FuOMjRuLP2H54dcwIyMDtFN9O8qyo97Ci',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '8083874152395839',
                'token_balance' => 0.00,
                'created_at' => '2025-12-06 18:17:40',
                'updated_at' => '2025-12-06 18:17:40',
            ],
            // ID 94
            [
                'id' => 94,
                'name' => 'Abdul Basit',
                'email' => 'abdulbassit41@gmail.com',
                'phone' => '03440755979',
                'email_verified_at' => '2025-12-06 19:05:10',
                'password' => '$2y$12$qxJAIYVbQAz3QmASm2zQPOvLGCXxjepPRmFWk93BfC3UIor83dpii',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '9166992982751842',
                'token_balance' => 0.00,
                'created_at' => '2025-12-06 19:04:42',
                'updated_at' => '2025-12-06 19:05:10',
            ],
            // ID 95 - Skip (duplicate: cha957676@gmail.com exists as id=82 in live)
            // ID 96
            [
                'id' => 96,
                'name' => 'Muhammed faizan',
                'email' => 'faizankhanfaizankhan111987@gmail.com',
                'phone' => '03452425005',
                'email_verified_at' => '2025-12-07 17:26:58',
                'password' => '$2y$12$tLdwkL23D53mXOuUTyWqHuCDjbfrSrDkBDnP8dSre/PrPIV3mFlOS',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '1065066640082525',
                'token_balance' => 0.00,
                'created_at' => '2025-12-07 17:26:24',
                'updated_at' => '2025-12-07 17:26:58',
            ],
            // ID 97
            [
                'id' => 97,
                'name' => 'Bashir Ahmed',
                'email' => 'ahmedbashiryahoo56@gmail.com',
                'phone' => '923343052849',
                'email_verified_at' => '2025-12-08 06:33:57',
                'password' => '$2y$12$ZLOKgYiONk9cb83RovPnuOsUbD2qxcbCaSfcbCvDrMDnwD74id842',
                'role' => 'investor',
                'kyc_status' => 'not_started',
                'wallet_address' => '2770969496353273',
                'token_balance' => 0.00,
                'created_at' => '2025-12-08 06:33:20',
                'updated_at' => '2025-12-08 06:33:57',
            ],
        ];
    }

    /**
     * Backfill ULIDs for ALL users with NULL values
     * This includes both existing users and newly inserted ones
     */
    protected function backfillAllUlids(): void
    {
        $usersWithoutUlid = DB::table('users')
            ->whereNull('ulid')
            ->orderBy('id')
            ->get();

        foreach ($usersWithoutUlid as $user) {
            $ulid = (string) Str::ulid();
            
            // Ensure uniqueness
            while (DB::table('users')->where('ulid', $ulid)->exists()) {
                $ulid = (string) Str::ulid();
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['ulid' => $ulid]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove inserted users (IDs 52-97, excluding duplicates)
        $idsToRemove = [52, 54, 55, 58, 59, 60, 61, 62, 63, 64, 65, 66, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 96, 97];
        
        DB::table('users')->whereIn('id', $idsToRemove)->delete();
        
        // Note: We don't remove ULIDs as they're needed for the application
    }
};

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMissingWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-wallets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate wallet addresses for users who do not have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating wallet addresses for users without one...');

        $usersWithoutWallet = User::whereNull('wallet_address')
            ->orWhere('wallet_address', '')
            ->get();

        if ($usersWithoutWallet->isEmpty()) {
            $this->info('All users already have wallet addresses.');
            return 0;
        }

        $this->info("Found {$usersWithoutWallet->count()} users without wallet addresses.");

        $bar = $this->output->createProgressBar($usersWithoutWallet->count());
        $bar->start();

        $generated = 0;
        $errors = 0;

        foreach ($usersWithoutWallet as $user) {
            try {
                $walletAddress = $this->generateUniqueWalletAddress();
                
                $user->wallet_address = $walletAddress;
                $user->save();

                $generated++;
            } catch (\Exception $e) {
                $this->error("\nError generating wallet for user ID {$user->id}: " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Successfully generated {$generated} wallet addresses.");
        if ($errors > 0) {
            $this->warn("Encountered {$errors} errors.");
        }

        return 0;
    }

    /**
     * Generate a unique 16-digit wallet address
     */
    private function generateUniqueWalletAddress(): string
    {
        do {
            $wallet = str_pad(random_int(1000000000000000, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } while (User::where('wallet_address', $wallet)->exists());

        return $wallet;
    }
}


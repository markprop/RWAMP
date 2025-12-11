<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconcileUserBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reconcile-balances 
                            {--fix : Automatically fix inconsistencies}
                            {--user= : Reconcile specific user by ID or email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile user token balances with transaction history to detect and fix inconsistencies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting balance reconciliation...');
        $this->newLine();

        $fix = $this->option('fix');
        $userFilter = $this->option('user');

        $query = User::query();
        
        if ($userFilter) {
            if (is_numeric($userFilter)) {
                $query->where('id', $userFilter);
            } else {
                $query->where('email', $userFilter);
            }
        }

        $users = $query->get();
        $totalUsers = $users->count();
        $inconsistencies = [];
        $fixed = 0;

        $this->info("Checking {$totalUsers} user(s)...");
        $this->newLine();

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        foreach ($users as $user) {
            $reconciliation = $user->reconcileBalance();
            
            if (!$reconciliation['is_consistent']) {
                $inconsistencies[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'stored_balance' => $reconciliation['stored_balance'],
                    'calculated_balance' => $reconciliation['calculated_balance'],
                    'discrepancy' => $reconciliation['discrepancy'],
                ];

                if ($fix) {
                    try {
                        DB::beginTransaction();
                        $user->fixBalanceFromTransactions();
                        DB::commit();
                        $fixed++;
                        
                        Log::info('Balance fixed via reconcile command', [
                            'user_id' => $user->id,
                            'user_email' => $user->email,
                            'old_balance' => $reconciliation['stored_balance'],
                            'new_balance' => $reconciliation['calculated_balance'],
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Failed to fix balance for user {$user->id}: " . $e->getMessage());
                        Log::error('Balance fix failed', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        if (empty($inconsistencies)) {
            $this->info('✓ All balances are consistent!');
            return 0;
        }

        $this->warn("Found " . count($inconsistencies) . " user(s) with balance inconsistencies:");
        $this->newLine();

        $headers = ['User ID', 'Email', 'Name', 'Stored Balance', 'Calculated Balance', 'Discrepancy'];
        $rows = [];

        foreach ($inconsistencies as $inc) {
            $rows[] = [
                $inc['user_id'],
                $inc['email'],
                $inc['name'],
                number_format($inc['stored_balance'], 2),
                number_format($inc['calculated_balance'], 2),
                number_format($inc['discrepancy'], 2),
            ];
        }

        $this->table($headers, $rows);

        if ($fix) {
            $this->newLine();
            $this->info("✓ Fixed {$fixed} user(s) with balance inconsistencies.");
        } else {
            $this->newLine();
            $this->warn('To automatically fix these inconsistencies, run:');
            $this->line('  php artisan users:reconcile-balances --fix');
            if ($userFilter) {
                $this->line('  php artisan users:reconcile-balances --fix --user=' . $userFilter);
            }
        }

        return 0;
    }
}

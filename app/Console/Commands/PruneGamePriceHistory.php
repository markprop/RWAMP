<?php

namespace App\Console\Commands;

use App\Models\GamePriceHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneGamePriceHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:prune-price-history {--days=7 : Number of days to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune game price history older than specified days (default: 7)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $deleted = GamePriceHistory::where('recorded_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deleted} price history records older than {$days} days.");

        return Command::SUCCESS;
    }
}

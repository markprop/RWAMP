<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\PriceHelper;
use Illuminate\Support\Facades\Cache;

class UpdateExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:update-usd-pkr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update USD to PKR exchange rate from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching USD to PKR exchange rate...');
        
        try {
            $rate = PriceHelper::fetchUsdToPkrRate();
            
            // Cache for 1 hour
            Cache::put('exchange_rate_usd_pkr', $rate, now()->addHour());
            
            $this->info("USD to PKR rate updated successfully: {$rate}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to update exchange rate: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}


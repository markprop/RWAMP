<?php

namespace App\Console;

use App\Services\GamePriceEngine;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Monitor crypto payments every 2 minutes
        $schedule->command('crypto:monitor')->everyTwoMinutes();
        
        // Update USD to PKR exchange rate every hour
        $schedule->command('exchange:update-usd-pkr')->hourly();
        
        // Prune game price history older than 7 days (daily at 2 AM)
        $schedule->command('game:prune-price-history')->dailyAt('02:00');

        // Warm up critical game price caches periodically so user requests stay fast
        $schedule->call(function () {
            /** @var \App\Services\GamePriceEngine $engine */
            $engine = app(GamePriceEngine::class);
            $engine->getUsdPkrRate();
            $engine->getBtcUsdPrice();
        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

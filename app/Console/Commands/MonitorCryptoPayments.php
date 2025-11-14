<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\CryptoMonitor;

class MonitorCryptoPayments extends Command
{
    protected $signature = 'crypto:monitor';
    protected $description = 'Polls Ethereum, Tron, and Bitcoin for incoming payments to admin hot wallets';

    public function handle(CryptoMonitor $monitor): int
    {
        if (!config('crypto.features.payments_enabled', false)) {
            $msg = now()->toDateTimeString()." - crypto:monitor skipped (payments disabled)";
            Log::channel('single')->info($msg);
            $this->info($msg);
            return Command::SUCCESS;
        }
        $erc20 = (string) config('crypto.wallets.ERC20', '');
        $trc20 = (string) config('crypto.wallets.TRC20', '');
        $btc   = (string) config('crypto.wallets.BTC', '');
        $etherscanKey = (string) config('crypto.api_keys.etherscan_api_key', '');
        $trongridKey  = (string) config('crypto.api_keys.trongrid_api_key', '');

        $total = 0;
        $total += $monitor->checkEthereumPayments($erc20, $etherscanKey, (float) config('crypto.rates.rwamp_usd', 0.011));
        sleep(2);
        $total += $monitor->checkTronPayments($trc20, $trongridKey);
        sleep(2);
        $total += $monitor->checkBtcPayments($btc);

        $msg = now()->toDateTimeString()." - crypto:monitor processed {$total} new txs";
        Log::channel('single')->info($msg);
        $this->info($msg);
        return Command::SUCCESS;
    }
}

?>


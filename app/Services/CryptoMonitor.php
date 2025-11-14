<?php

namespace App\Services;

use App\Models\ProcessedCryptoTransaction;
use App\Models\CryptoPayment;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class CryptoMonitor
{
    private Client $http;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?: new Client(['timeout' => 15]);
    }

    public function checkEthereumPayments(string $wallet, string $etherscanKey, float $rwampUsdRate): int
    {
        $count = 0;
        if (empty($wallet) || empty($etherscanKey)) return 0;

        // ERC20 USDT transfers
        $url = 'https://api.etherscan.io/api';
        $query = [
            'module' => 'account',
            'action' => 'tokentx',
            'address' => $wallet,
            'contractaddress' => '0xdAC17F958D2ee523a2206206994597C13D831ec7',
            'page' => 1,
            'offset' => 20,
            'sort' => 'desc',
            'apikey' => $etherscanKey,
        ];
        try {
            $res = $this->http->get($url, ['query' => $query]);
            $body = json_decode((string) $res->getBody(), true);
            if (!isset($body['result']) || !is_array($body['result'])) return 0;
            foreach ($body['result'] as $tx) {
                // Validate to address (hot wallet), confirmations >=1
                if (strtolower($tx['to']) !== strtolower($wallet)) continue;
                if ((int) ($tx['confirmations'] ?? 0) < 1) continue;
                $txHash = $tx['hash'];
                if (ProcessedCryptoTransaction::where('tx_hash', $txHash)->exists()) continue;

                // Amount is in token's decimals (USDT 6)
                $amountUsdt = ((float) $tx['value']) / 1e6;
                $amountUsd = $amountUsdt; // 1 USDT ~ 1 USD

                ProcessedCryptoTransaction::create([
                    'tx_hash' => $txHash,
                    'network' => 'ERC20',
                    'amount_usd' => $amountUsd,
                ]);
                $count++;
            }
        } catch (\Throwable $e) {
            Log::error('ETH monitor failed: '.$e->getMessage());
        }
        return $count;
    }

    public function checkTronPayments(string $wallet, string $trongridKey): int
    {
        $count = 0;
        if (empty($wallet)) return 0;
        $headers = [];
        if (!empty($trongridKey)) {
            $headers['TRON-PRO-API-KEY'] = $trongridKey;
        }
        try {
            // USDT TRC20 transfers to address
            $url = 'https://apilist.tronscan.org/api/transfer';
            $query = [
                'count' => 20,
                'start' => 0,
                'sort' => '-timestamp',
                'tokens' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                'address' => $wallet,
            ];
            $res = $this->http->get($url, ['query' => $query, 'headers' => $headers]);
            $body = json_decode((string) $res->getBody(), true);
            $transfers = $body['data'] ?? [];
            foreach ($transfers as $tx) {
                if (($tx['transferToAddress'] ?? '') !== $wallet) continue;
                if ((int) ($tx['confirmations'] ?? 0) < 1) continue;
                $txHash = $tx['transactionHash'] ?? ($tx['hash'] ?? '');
                if (!$txHash) continue;
                if (ProcessedCryptoTransaction::where('tx_hash', $txHash)->exists()) continue;

                $amountUsdt = (float) ($tx['amount_str'] ?? $tx['amount'] ?? 0);
                $amountUsd = $amountUsdt;
                ProcessedCryptoTransaction::create([
                    'tx_hash' => $txHash,
                    'network' => 'TRC20',
                    'amount_usd' => $amountUsd,
                ]);
                $count++;
            }
        } catch (\Throwable $e) {
            Log::error('TRON monitor failed: '.$e->getMessage());
        }
        return $count;
    }

    public function checkBtcPayments(string $wallet): int
    {
        $count = 0;
        if (empty($wallet)) return 0;
        try {
            $url = "https://blockstream.info/api/address/{$wallet}/txs";
            $res = $this->http->get($url);
            $txs = json_decode((string) $res->getBody(), true);
            if (!is_array($txs)) return 0;
            foreach ($txs as $tx) {
                $txHash = $tx['txid'] ?? '';
                if (!$txHash) continue;
                if (ProcessedCryptoTransaction::where('tx_hash', $txHash)->exists()) continue;
                $status = $tx['status'] ?? [];
                if ((int) ($status['confirmations'] ?? 0) < 3) continue;
                // Sum outputs to our address
                $vout = $tx['vout'] ?? [];
                $satsToUs = 0;
                foreach ($vout as $o) {
                    $scriptpubkey_address = $o['scriptpubkey_address'] ?? '';
                    if ($scriptpubkey_address === $wallet) {
                        $satsToUs += (int) ($o['value'] ?? 0);
                    }
                }
                if ($satsToUs <= 0) continue;
                $btcAmount = $satsToUs / 1e8;
                // Approx USD via config rate
                $btcUsd = (float) config('crypto.rates.btc_usd', 60000);
                $amountUsd = $btcAmount * $btcUsd;
                ProcessedCryptoTransaction::create([
                    'tx_hash' => $txHash,
                    'network' => 'BTC',
                    'amount_usd' => $amountUsd,
                ]);
                $count++;
            }
        } catch (\Throwable $e) {
            Log::error('BTC monitor failed: '.$e->getMessage());
        }
        return $count;
    }
}

?>




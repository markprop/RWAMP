<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CryptoPaymentVerifier
{
    protected $client;
    protected $alchemyApiKey;
    protected $tronGridApiKey;
    protected $blockstreamApiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->alchemyApiKey = config('crypto.api_keys.alchemy_api_key');
        $this->tronGridApiKey = config('crypto.api_keys.trongrid_api_key');
        $this->blockstreamApiUrl = config('crypto.api_keys.blockstream_api_url');
    }

    /**
     * Monitor all admin wallets for incoming payments
     */
    public function monitorWallets()
    {
        $wallets = config('crypto.wallets');
        
        // Monitor Ethereum (ERC20 USDT)
        if (!empty($wallets['ERC20']) && $this->alchemyApiKey) {
            $this->monitorEthereumWallet($wallets['ERC20']);
        }
        
        // Monitor Tron (TRC20 USDT)
        if (!empty($wallets['TRC20']) && $this->tronGridApiKey) {
            $this->monitorTronWallet($wallets['TRC20']);
        }
        
        // Monitor Bitcoin
        if (!empty($wallets['BTC'])) {
            $this->monitorBitcoinWallet($wallets['BTC']);
        }
    }

    /**
     * Monitor Ethereum wallet for USDT transactions
     */
    protected function monitorEthereumWallet($walletAddress)
    {
        try {
            // USDT contract address on Ethereum mainnet
            $usdtContract = config('crypto.contracts.usdt_erc20');
            
            $response = $this->client->get('https://eth-mainnet.g.alchemy.com/v2/' . $this->alchemyApiKey, [
                'query' => [
                    'module' => 'account',
                    'action' => 'tokentx',
                    'contractaddress' => $usdtContract,
                    'address' => $walletAddress,
                    'startblock' => 0,
                    'endblock' => 99999999,
                    'sort' => 'desc',
                    'apikey' => $this->alchemyApiKey
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['result']) && is_array($data['result'])) {
                foreach ($data['result'] as $tx) {
                    $this->processEthereumTransaction($tx);
                }
            }
        } catch (RequestException $e) {
            Log::error('Ethereum monitoring error: ' . $e->getMessage());
        }
    }

    /**
     * Monitor Tron wallet for USDT transactions
     */
    protected function monitorTronWallet($walletAddress)
    {
        try {
            // USDT contract address on Tron (TRC20)
            $usdtContract = config('crypto.contracts.usdt_trc20');
            
            $response = $this->client->get('https://api.trongrid.io/v1/accounts/' . $walletAddress . '/transactions/trc20', [
                'headers' => [
                    'TRON-PRO-API-KEY' => $this->tronGridApiKey
                ],
                'query' => [
                    'contract_address' => $usdtContract,
                    'limit' => 50
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $tx) {
                    $this->processTronTransaction($tx);
                }
            }
        } catch (RequestException $e) {
            Log::error('Tron monitoring error: ' . $e->getMessage());
        }
    }

    /**
     * Monitor Bitcoin wallet for transactions
     */
    protected function monitorBitcoinWallet($walletAddress)
    {
        try {
            $response = $this->client->get($this->blockstreamApiUrl . '/address/' . $walletAddress . '/txs');
            $transactions = json_decode($response->getBody(), true);
            
            foreach ($transactions as $tx) {
                $this->processBitcoinTransaction($tx, $walletAddress);
            }
        } catch (RequestException $e) {
            Log::error('Bitcoin monitoring error: ' . $e->getMessage());
        }
    }

    /**
     * Process Ethereum transaction
     */
    protected function processEthereumTransaction($tx)
    {
        // Check if transaction is to our wallet and is USDT
        if ($tx['to'] !== config('crypto.wallets.ERC20')) {
            return;
        }

        // Check if we've already processed this transaction
        if (Transaction::where('tx_hash', $tx['hash'])->exists()) {
            return;
        }

        // Convert from wei to USDT (USDT has 6 decimals)
        $amount = $tx['value'] / pow(10, 6);
        
        // Calculate RWAMP tokens
        $rwampTokens = $this->calculateRwampTokens($amount, 'USD');
        
        // Find user by wallet address (you'll need to implement this)
        $user = $this->findUserByWalletAddress($tx['from']);
        
        if ($user && $rwampTokens > 0) {
            $this->creditUserTokens($user, $rwampTokens, $tx['hash'], 'ERC20', $amount);
        }
    }

    /**
     * Process Tron transaction
     */
    protected function processTronTransaction($tx)
    {
        // Check if transaction is to our wallet
        if ($tx['to'] !== config('crypto.wallets.TRC20')) {
            return;
        }

        // Check if we've already processed this transaction
        if (Transaction::where('tx_hash', $tx['transaction_id'])->exists()) {
            return;
        }

        // Convert from sun to USDT (USDT has 6 decimals on Tron)
        $amount = $tx['value'] / pow(10, 6);
        
        // Calculate RWAMP tokens
        $rwampTokens = $this->calculateRwampTokens($amount, 'USD');
        
        // Find user by wallet address
        $user = $this->findUserByWalletAddress($tx['from']);
        
        if ($user && $rwampTokens > 0) {
            $this->creditUserTokens($user, $rwampTokens, $tx['transaction_id'], 'TRC20', $amount);
        }
    }

    /**
     * Process Bitcoin transaction
     */
    protected function processBitcoinTransaction($tx, $walletAddress)
    {
        // Check if transaction is to our wallet
        $isToOurWallet = false;
        foreach ($tx['vout'] as $output) {
            if (in_array($walletAddress, $output['scriptpubkey_addresses'])) {
                $isToOurWallet = true;
                break;
            }
        }

        if (!$isToOurWallet) {
            return;
        }

        // Check if we've already processed this transaction
        if (Transaction::where('tx_hash', $tx['txid'])->exists()) {
            return;
        }

        // Calculate BTC amount received
        $btcAmount = 0;
        foreach ($tx['vout'] as $output) {
            if (in_array($walletAddress, $output['scriptpubkey_addresses'])) {
                $btcAmount += $output['value'];
            }
        }

        // Convert BTC to USD (you'll need to get current BTC price)
        $usdAmount = $this->convertBtcToUsd($btcAmount);
        
        // Calculate RWAMP tokens
        $rwampTokens = $this->calculateRwampTokens($usdAmount, 'USD');
        
        // Find user by wallet address
        $user = $this->findUserByWalletAddress($tx['vin'][0]['prevout']['scriptpubkey_address'] ?? '');
        
        if ($user && $rwampTokens > 0) {
            $this->creditUserTokens($user, $rwampTokens, $tx['txid'], 'BTC', $btcAmount);
        }
    }

    /**
     * Calculate RWAMP tokens based on USD amount
     */
    protected function calculateRwampTokens($usdAmount, $currency = 'USD')
    {
        $tokenPrice = config('crypto.rates.rwamp_usd', 0.011);
        return floor($usdAmount / $tokenPrice);
    }

    /**
     * Convert BTC to USD
     */
    protected function convertBtcToUsd($btcAmount)
    {
        try {
            $response = $this->client->get('https://api.coinbase.com/v2/exchange-rates?currency=BTC');
            $data = json_decode($response->getBody(), true);
            $btcUsdRate = $data['data']['rates']['USD'];
            return $btcAmount * $btcUsdRate;
        } catch (RequestException $e) {
            Log::error('BTC to USD conversion error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Find user by wallet address
     */
    protected function findUserByWalletAddress($walletAddress)
    {
        // You'll need to add a wallet_address field to users table
        return User::where('wallet_address', $walletAddress)->first();
    }

    /**
     * Credit user tokens and log transaction
     */
    protected function creditUserTokens($user, $rwampTokens, $txHash, $network, $amount)
    {
        try {
            // Start database transaction
            \DB::beginTransaction();

            // Credit user tokens
            $user->increment('token_balance', $rwampTokens);

            // Log transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'crypto_purchase',
                'amount' => $rwampTokens,
                'description' => "Crypto payment via {$network}",
                'tx_hash' => $txHash,
                'network' => $network,
                'crypto_amount' => $amount,
                'status' => 'completed'
            ]);

            \DB::commit();

            // Send email notification
            $this->sendPaymentNotification($user, $rwampTokens, $txHash, $network);

            Log::info("Credited {$rwampTokens} RWAMP tokens to user {$user->id} for transaction {$txHash}");

        } catch (\Exception $e) {
            \DB::rollback();
            Log::error("Failed to credit tokens for transaction {$txHash}: " . $e->getMessage());
        }
    }

    /**
     * Send payment notification email
     */
    protected function sendPaymentNotification($user, $rwampTokens, $txHash, $network)
    {
        try {
            Mail::send('emails.crypto-payment-confirmed', [
                'user' => $user,
                'rwampTokens' => $rwampTokens,
                'txHash' => $txHash,
                'network' => $network
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('RWAMP Tokens Credited - Payment Confirmed');
            });
        } catch (\Exception $e) {
            Log::error("Failed to send payment notification: " . $e->getMessage());
        }
    }
}

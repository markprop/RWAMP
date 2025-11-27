<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PriceHelper
{
    /**
     * Get current RWAMP token price in PKR (from cache, database, or config)
     * This is the official price set by admin and used across the entire website
     */
    public static function getRwampPkrPrice(): float
    {
        // Always check cache first - this is the official admin-set price
        $cachedPrice = Cache::get('crypto_price_rwamp_pkr');
        if ($cachedPrice !== null) {
            return (float) $cachedPrice;
        }
        
        // If cache is empty, check database (persistent storage)
        try {
            if (Schema::hasTable('system_settings')) {
                $dbPrice = DB::table('system_settings')
                    ->where('key', 'crypto_price_rwamp_pkr')
                    ->value('value');
                
                if ($dbPrice !== null) {
                    $price = (float) $dbPrice;
                    // Store in cache for faster access
                    Cache::forever('crypto_price_rwamp_pkr', $price);
                    return $price;
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet, fall through to config
        }
        
        // Fallback to config if cache and database are empty
        $defaultPkr = config('crypto.rates.rwamp_pkr', 3.0);
        return (float) $defaultPkr;
    }

    /**
     * Get current RWAMP token price in USD (from cache or calculated)
     */
    public static function getRwampUsdPrice(): float
    {
        $cached = Cache::get('crypto_price_rwamp_usd');
        if ($cached !== null) {
            return (float) $cached;
        }
        
        // Calculate from PKR price and exchange rate
        $rwampPkr = self::getRwampPkrPrice();
        $usdPkr = self::getUsdToPkrRate();
        return $rwampPkr / $usdPkr;
    }

    /**
     * Get current USDT price in USD (from cache or API)
     */
    public static function getUsdtUsdPrice(): float
    {
        return (float) Cache::get('crypto_price_usdt_usd', config('crypto.rates.usdt_usd', 1.0));
    }

    /**
     * Get current USDT price in PKR (from cache or calculated)
     */
    public static function getUsdtPkrPrice(): float
    {
        $cached = Cache::get('crypto_price_usdt_pkr');
        if ($cached !== null) {
            return (float) $cached;
        }
        
        // Calculate from USD price and exchange rate
        $usdtUsd = self::getUsdtUsdPrice();
        $usdPkr = self::getUsdToPkrRate();
        return $usdtUsd * $usdPkr;
    }

    /**
     * Get current BTC price in USD (from cache or config)
     */
    public static function getBtcUsdPrice(): float
    {
        return (float) Cache::get('crypto_price_btc_usd', config('crypto.rates.btc_usd', 60000));
    }

    /**
     * Get current BTC price in PKR (from cache or calculated)
     */
    public static function getBtcPkrPrice(): float
    {
        $cached = Cache::get('crypto_price_btc_pkr');
        if ($cached !== null) {
            return (float) $cached;
        }
        
        // Calculate from USD price and exchange rate
        $btcUsd = self::getBtcUsdPrice();
        $usdPkr = self::getUsdToPkrRate();
        return $btcUsd * $usdPkr;
    }

    /**
     * Get reseller commission rate (from cache or config)
     */
    public static function getResellerCommissionRate(): float
    {
        return (float) Cache::get('reseller_commission_rate', config('crypto.reseller_commission_rate', 0.10));
    }

    /**
     * Get reseller markup rate (from cache or config)
     */
    public static function getResellerMarkupRate(): float
    {
        return (float) Cache::get('reseller_markup_rate', config('crypto.reseller_markup_rate', 0.05));
    }

    /**
     * Get current USD to PKR exchange rate (from cache or API)
     * Automatically fetches from API if cache is expired or missing
     */
    public static function getUsdToPkrRate(): float
    {
        // Check cache first (cache for 1 hour)
        $cached = Cache::get('exchange_rate_usd_pkr');
        if ($cached !== null) {
            return (float) $cached;
        }

        // Fetch from API if cache is expired
        $rate = self::fetchUsdToPkrRate();
        
        // Cache for 1 hour
        Cache::put('exchange_rate_usd_pkr', $rate, now()->addHour());
        
        return $rate;
    }

    /**
     * Fetch USD to PKR exchange rate from API
     * Uses exchangerate-api.com (free tier) or fallback to config
     */
    public static function fetchUsdToPkrRate(): float
    {
        try {
            // Try exchangerate-api.com first (free tier, no API key needed for basic usage)
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            
            // Using exchangerate-api.com free endpoint
            $response = $client->get('https://api.exchangerate-api.com/v4/latest/USD');
            
            $data = json_decode($response->getBody(), true);
            if (isset($data['rates']['PKR'])) {
                $rate = (float) $data['rates']['PKR'];
                \Log::info('USD to PKR rate fetched from exchangerate-api.com: ' . $rate);
                return $rate;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch USD to PKR rate from exchangerate-api.com: ' . $e->getMessage());
        }

        // Fallback: Try alternative API (fixer.io or currencyapi.net)
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            // Using currencyapi.net free endpoint (alternative)
            $response = $client->get('https://api.currencyapi.com/v3/latest', [
                'query' => [
                    'apikey' => env('CURRENCY_API_KEY', ''),
                    'base_currency' => 'USD',
                    'currencies' => 'PKR'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            if (isset($data['data']['PKR']['value'])) {
                $rate = (float) $data['data']['PKR']['value'];
                \Log::info('USD to PKR rate fetched from currencyapi.net: ' . $rate);
                return $rate;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch USD to PKR rate from currencyapi.net: ' . $e->getMessage());
        }

        // Final fallback to config value
        $defaultRate = (float) config('crypto.rates.usd_pkr', 278);
        \Log::warning('Using default USD to PKR rate from config: ' . $defaultRate);
        return $defaultRate;
    }
}


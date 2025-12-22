<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // In local/dev, avoid external HTTP calls to keep pages fast
        if (app()->environment('local')) {
            return (float) config('crypto.rates.usd_pkr', 278);
        }

        // Use remember to prevent race conditions and repetitive calls
        return (float) Cache::remember('exchange_rate_usd_pkr', now()->addHour(), function () {
            return self::fetchUsdToPkrRate();
        });
    }

    /**
     * Fetch USD to PKR exchange rate from API
     * Uses exchangerate-api.com (free tier) or fallback to config
     */
    public static function fetchUsdToPkrRate(): float
    {
        // In local/dev, don’t hit external APIs
        if (app()->environment('local')) {
            return (float) config('crypto.rates.usd_pkr', 278);
        }

        // Configurable validation bounds and TTLs
        $pkrMin     = (float) config('crypto.rates.pkr_min', 100);
        $pkrMax     = (float) config('crypto.rates.pkr_max', 10000);
        $successTtl = 3600; // seconds (1 hour)
        $defaultTtl = 300;  // seconds (5 minutes)

        // In local/dev, don't hit external APIs but cache defaults for consistency
        if (app()->environment('local')) {
            $defaultRate = (float) config('crypto.rates.usd_pkr', 278);
            try {
                Cache::put('exchange_rate_usd_pkr', $defaultRate, $defaultTtl);
            } catch (\Exception $e) {
                Log::error('Cache put failed for default USD to PKR rate (local env)', [
                    'rate'  => $defaultRate,
                    'ttl'   => $defaultTtl,
                    'error' => $e->getMessage(),
                ]);
            }
            return $defaultRate;
        }

        // Use a lock to prevent concurrent API calls
        $lockKey = 'fetching_usd_pkr_rate';
        $lock = Cache::lock($lockKey, 30); // 30 second lock
        $lockAcquired = false;
        
        try {
            if ($lock->get()) {
                $lockAcquired = true;
                
                // Try exchangerate-api.com first (free tier, no API key needed for basic usage)
                try {
                    $client = new \GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5]);
                    
                    // Using exchangerate-api.com free endpoint
                    $response = $client->get('https://api.exchangerate-api.com/v4/latest/USD');
                    
                    $data = json_decode($response->getBody(), true);
                    if (isset($data['rates']['PKR'])) {
                        $rate = (float) $data['rates']['PKR'];
                        
                        // Validate rate is a reasonable float value
                        if (is_numeric($rate) && $rate >= $pkrMin && $rate <= $pkrMax) {
                            // Cache the fetched rate for 1 hour
                            try {
                                Cache::put('exchange_rate_usd_pkr', $rate, $successTtl);
                            } catch (\Exception $e) {
                                Log::error('Cache put failed for USD to PKR rate (exchangerate-api.com)', [
                                    'rate'  => $rate,
                                    'ttl'   => $successTtl,
                                    'error' => $e->getMessage(),
                                ]);
                            }

                            Log::info('USD to PKR rate fetched from exchangerate-api.com', ['rate' => $rate]);
                            return $rate;
                        } else {
                            Log::warning(
                                'Invalid USD to PKR rate from exchangerate-api.com: ' . $rate .
                                ' (must be >= ' . $pkrMin . ' and <= ' . $pkrMax . ')',
                                [
                                    'rate' => $rate,
                                    'type' => gettype($rate),
                                    'min'  => $pkrMin,
                                    'max'  => $pkrMax,
                                ]
                            );
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch USD to PKR rate from exchangerate-api.com', [
                        'error' => $e->getMessage(),
                    ]);
                }

                // Fallback: Try alternative API (currencyapi.net)
                try {
                    $apiKey = env('CURRENCY_API_KEY', '');
                    if ($apiKey) {
                        $client = new \GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5]);
                        $response = $client->get('https://api.currencyapi.com/v3/latest', [
                            'query' => [
                                'apikey' => $apiKey,
                                'base_currency' => 'USD',
                                'currencies' => 'PKR'
                            ]
                        ]);
                        
                        $data = json_decode($response->getBody(), true);
                        if (isset($data['data']['PKR']['value'])) {
                            $rate = (float) $data['data']['PKR']['value'];
                            
                            // Validate rate is a reasonable float value
                            if (is_numeric($rate) && $rate >= $pkrMin && $rate <= $pkrMax) {
                                // Cache the fetched rate for 1 hour
                                try {
                                    Cache::put('exchange_rate_usd_pkr', $rate, $successTtl);
                                } catch (\Exception $e) {
                                    Log::error('Cache put failed for USD to PKR rate (currencyapi.net)', [
                                        'rate'  => $rate,
                                        'ttl'   => $successTtl,
                                        'error' => $e->getMessage(),
                                    ]);
                                }

                                Log::info('USD to PKR rate fetched from currencyapi.net', ['rate' => $rate]);
                                return $rate;
                            } else {
                                Log::warning(
                                    'Invalid USD to PKR rate from currencyapi.net: ' . $rate .
                                    ' (must be >= ' . $pkrMin . ' and <= ' . $pkrMax . ')',
                                    [
                                        'rate' => $rate,
                                        'type' => gettype($rate),
                                        'min'  => $pkrMin,
                                        'max'  => $pkrMax,
                                    ]
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch USD to PKR rate from currencyapi.net', [
                        'error' => $e->getMessage(),
                    ]);
                }

                // Final fallback to config value
                $defaultRate = (float) config('crypto.rates.usd_pkr', 278);
                // Cache default value temporarily (5 minutes) to reduce immediate re-fetches
                try {
                    Cache::put('exchange_rate_usd_pkr', $defaultRate, $defaultTtl);
                } catch (\Exception $e) {
                    Log::error('Cache put failed for default USD to PKR rate', [
                        'rate'  => $defaultRate,
                        'ttl'   => $defaultTtl,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::warning('Using default USD to PKR rate from config', ['rate' => $defaultRate]);
                return $defaultRate;
            } else {
                // Another process is fetching, wait a bit and return cached value or default
                sleep(1);
                try {
                    $cached = Cache::get('exchange_rate_usd_pkr');
                } catch (\Exception $e) {
                    Log::error('Cache get failed for USD to PKR rate', [
                        'error' => $e->getMessage(),
                    ]);
                    $cached = null;
                }

                if ($cached !== null && is_numeric($cached)) {
                    return (float) $cached;
                }
                // If cache is still empty, return default and cache it temporarily
                $defaultRate = (float) config('crypto.rates.usd_pkr', 278);
                try {
                    Cache::put('exchange_rate_usd_pkr', $defaultRate, $defaultTtl);
                } catch (\Exception $e) {
                    Log::error('Cache put failed for default USD to PKR rate (else branch)', [
                        'rate'  => $defaultRate,
                        'ttl'   => $defaultTtl,
                        'error' => $e->getMessage(),
                    ]);
                }
                return $defaultRate;
            }
        } finally {
            // Always release the lock if it was acquired
            if ($lockAcquired) {
                $lock->release();
            }
        }
    }

    /**
     * Get current USD to AED exchange rate (from cache or API)
     * Automatically fetches from API if cache is expired or missing
     */
    public static function getUsdToAedRate(): float
    {
        // In local/dev, avoid external HTTP calls to keep pages fast
        if (app()->environment('local')) {
            return (float) config('crypto.rates.usd_aed', 3.67);
        }

        // Use remember to prevent race conditions and repetitive calls
        return (float) Cache::remember('exchange_rate_usd_aed', now()->addHour(), function () {
            return self::fetchUsdToAedRate();
        });
    }

    /**
     * Fetch USD to AED exchange rate from API
     * Uses exchangerate-api.com (free tier) or fallback to config
     */
    public static function fetchUsdToAedRate(): float
    {
        // In local/dev, don’t hit external APIs
        if (app()->environment('local')) {
            return (float) config('crypto.rates.usd_aed', 3.67);
        }

        // Configurable validation bounds and TTLs
        $aedMin     = (float) config('crypto.rates.aed_min', 1);
        $aedMax     = (float) config('crypto.rates.aed_max', 10);
        $successTtl = 3600; // seconds (1 hour)
        $defaultTtl = 300;  // seconds (5 minutes)

        // In local/dev, don't hit external APIs but cache defaults for consistency
        if (app()->environment('local')) {
            $defaultRate = (float) config('crypto.rates.usd_aed', 3.67);
            try {
                Cache::put('exchange_rate_usd_aed', $defaultRate, $defaultTtl);
            } catch (\Exception $e) {
                Log::error('Cache put failed for default USD to AED rate (local env)', [
                    'rate'  => $defaultRate,
                    'ttl'   => $defaultTtl,
                    'error' => $e->getMessage(),
                ]);
            }
            return $defaultRate;
        }

        // Use a lock to prevent concurrent API calls
        $lockKey = 'fetching_usd_aed_rate';
        $lock = Cache::lock($lockKey, 30); // 30 second lock
        $lockAcquired = false;
        
        try {
            if ($lock->get()) {
                $lockAcquired = true;
                
                // Try exchangerate-api.com first (free tier, no API key needed for basic usage)
                try {
                    $client = new \GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5]);
                    
                    // Using exchangerate-api.com free endpoint
                    $response = $client->get('https://api.exchangerate-api.com/v4/latest/USD');
                    
                    $data = json_decode($response->getBody(), true);
                    if (isset($data['rates']['AED'])) {
                        $rate = (float) $data['rates']['AED'];
                        
                        // Validate rate is a reasonable float value
                        if (is_numeric($rate) && $rate >= $aedMin && $rate <= $aedMax) {
                            // Cache the fetched rate for 1 hour
                            try {
                                Cache::put('exchange_rate_usd_aed', $rate, $successTtl);
                            } catch (\Exception $e) {
                                Log::error('Cache put failed for USD to AED rate (exchangerate-api.com)', [
                                    'rate'  => $rate,
                                    'ttl'   => $successTtl,
                                    'error' => $e->getMessage(),
                                ]);
                            }

                            Log::info('USD to AED rate fetched from exchangerate-api.com', ['rate' => $rate]);
                            return $rate;
                        } else {
                            Log::warning(
                                'Invalid USD to AED rate from exchangerate-api.com: ' . $rate .
                                ' (must be >= ' . $aedMin . ' and <= ' . $aedMax . ')',
                                [
                                    'rate' => $rate,
                                    'type' => gettype($rate),
                                    'min'  => $aedMin,
                                    'max'  => $aedMax,
                                ]
                            );
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch USD to AED rate from exchangerate-api.com', [
                        'error' => $e->getMessage(),
                    ]);
                }

                // Fallback: Try alternative API
                try {
                    $apiKey = env('CURRENCY_API_KEY', '');
                    if ($apiKey) {
                        $client = new \GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5]);
                        $response = $client->get('https://api.currencyapi.com/v3/latest', [
                            'query' => [
                                'apikey' => $apiKey,
                                'base_currency' => 'USD',
                                'currencies' => 'AED'
                            ]
                        ]);
                        
                        $data = json_decode($response->getBody(), true);
                        if (isset($data['data']['AED']['value'])) {
                            $rate = (float) $data['data']['AED']['value'];
                            
                            // Validate rate is a reasonable float value
                            if (is_numeric($rate) && $rate >= $aedMin && $rate <= $aedMax) {
                                // Cache the fetched rate for 1 hour
                                try {
                                    Cache::put('exchange_rate_usd_aed', $rate, $successTtl);
                                } catch (\Exception $e) {
                                    Log::error('Cache put failed for USD to AED rate (currencyapi.net)', [
                                        'rate'  => $rate,
                                        'ttl'   => $successTtl,
                                        'error' => $e->getMessage(),
                                    ]);
                                }

                                Log::info('USD to AED rate fetched from currencyapi.net', ['rate' => $rate]);
                                return $rate;
                            } else {
                                Log::warning(
                                    'Invalid USD to AED rate from currencyapi.net: ' . $rate .
                                    ' (must be >= ' . $aedMin . ' and <= ' . $aedMax . ')',
                                    [
                                        'rate' => $rate,
                                        'type' => gettype($rate),
                                        'min'  => $aedMin,
                                        'max'  => $aedMax,
                                    ]
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch USD to AED rate from currencyapi.net', [
                        'error' => $e->getMessage(),
                    ]);
                }

                // Final fallback to config value (typical rate: 1 USD ≈ 3.67 AED)
                $defaultRate = (float) config('crypto.rates.usd_aed', 3.67);
                // Cache default value temporarily (5 minutes) to reduce immediate re-fetches
                try {
                    Cache::put('exchange_rate_usd_aed', $defaultRate, $defaultTtl);
                } catch (\Exception $e) {
                    Log::error('Cache put failed for default USD to AED rate', [
                        'rate'  => $defaultRate,
                        'ttl'   => $defaultTtl,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::warning('Using default USD to AED rate from config', ['rate' => $defaultRate]);
                return $defaultRate;
            } else {
                // Another process is fetching, wait a bit and return cached value or default
                sleep(1);
                try {
                    $cached = Cache::get('exchange_rate_usd_aed');
                } catch (\Exception $e) {
                    Log::error('Cache get failed for USD to AED rate', [
                        'error' => $e->getMessage(),
                    ]);
                    $cached = null;
                }

                if ($cached !== null && is_numeric($cached)) {
                    return (float) $cached;
                }
                // If cache is still empty, return default and cache it temporarily
                $defaultRate = (float) config('crypto.rates.usd_aed', 3.67);
                try {
                    Cache::put('exchange_rate_usd_aed', $defaultRate, $defaultTtl);
                } catch (\Exception $e) {
                    Log::error('Cache put failed for default USD to AED rate (else branch)', [
                        'rate'  => $defaultRate,
                        'ttl'   => $defaultTtl,
                        'error' => $e->getMessage(),
                    ]);
                }
                return $defaultRate;
            }
        } finally {
            // Always release the lock if it was acquired
            if ($lockAcquired) {
                $lock->release();
            }
        }
    }

    /**
     * Get current AED to PKR exchange rate (calculated from USD rates)
     */
    public static function getAedToPkrRate(): float
    {
        $usdPkr = self::getUsdToPkrRate();
        $usdAed = self::getUsdToAedRate();
        
        if ($usdAed > 0) {
            return $usdPkr / $usdAed;
        }
        
        // Fallback: typical rate (1 AED ≈ 75.7 PKR based on 1 USD = 278 PKR and 1 USD = 3.67 AED)
        return (float) config('crypto.rates.aed_pkr', 75.7);
    }
}


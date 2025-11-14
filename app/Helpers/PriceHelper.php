<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class PriceHelper
{
    /**
     * Get current RWAMP token price in PKR (from cache or config)
     */
    public static function getRwampPkrPrice(): float
    {
        $defaultPkr = config('crypto.rates.rwamp_pkr', 3.0);
        return (float) Cache::get('crypto_price_rwamp_pkr', $defaultPkr);
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
        $usdPkr = config('crypto.rates.usd_pkr', 278);
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
        $usdPkr = config('crypto.rates.usd_pkr', 278);
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
        $usdPkr = config('crypto.rates.usd_pkr', 278);
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
}


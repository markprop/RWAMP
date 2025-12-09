<?php

namespace App\Services;

use App\Helpers\PriceHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GamePriceEngine
{
    /**
     * Get current BTC/USD price from Binance API
     */
    public function getBtcUsdPrice(): float
    {
        // Check cache first
        $cached = Cache::get('game_btc_usd_price');
        if ($cached !== null) {
            return (float) $cached;
        }

        try {
            $response = Http::timeout(5)->get('https://api.binance.com/api/v3/ticker/price', [
                'symbol' => 'BTCUSDT'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $price = (float) $data['price'];
                
                // Cache for 30 seconds
                Cache::put('game_btc_usd_price', $price, now()->addSeconds(30));
                
                Log::info('Game: Fetched BTC/USD from Binance', ['price' => $price]);
                return $price;
            }
        } catch (\Exception $e) {
            Log::warning('Game: Failed to fetch BTC/USD from Binance', ['error' => $e->getMessage()]);
        }

        // Fallback to PriceHelper
        return PriceHelper::getBtcUsdPrice();
    }

    /**
     * Get USD/PKR exchange rate
     */
    public function getUsdPkrRate(): float
    {
        // Check cache first
        $cached = Cache::get('game_usd_pkr_rate');
        if ($cached !== null) {
            return (float) $cached;
        }

        try {
            $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rates']['PKR'])) {
                    $rate = (float) $data['rates']['PKR'];
                    
                    // Cache for 1 hour
                    Cache::put('game_usd_pkr_rate', $rate, now()->addHour());
                    
                    Log::info('Game: Fetched USD/PKR from exchangerate-api', ['rate' => $rate]);
                    return $rate;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Game: Failed to fetch USD/PKR from exchangerate-api', ['error' => $e->getMessage()]);
        }

        // Fallback to PriceHelper
        return PriceHelper::getUsdToPkrRate();
    }

    /**
     * Get game parameters from system settings
     */
    public function getGameParameters(): array
    {
        return [
            'tokens_per_btc' => (float) Cache::get('system_setting_tokens_per_btc', 
                \DB::table('system_settings')->where('key', 'tokens_per_btc')->value('value') ?? 1000000),
            'spread_pkr' => (float) Cache::get('system_setting_spread_pkr',
                \DB::table('system_settings')->where('key', 'spread_pkr')->value('value') ?? 0.5),
            'buy_fee_pct' => (float) Cache::get('system_setting_buy_fee_pct',
                \DB::table('system_settings')->where('key', 'buy_fee_pct')->value('value') ?? 0.01),
            'sell_fee_pct' => (float) Cache::get('system_setting_sell_fee_pct',
                \DB::table('system_settings')->where('key', 'sell_fee_pct')->value('value') ?? 0.01),
            'velocity_multiplier' => (float) Cache::get('system_setting_velocity_multiplier',
                \DB::table('system_settings')->where('key', 'velocity_multiplier')->value('value') ?? 1.0),
        ];
    }

    /**
     * Calculate anchored RWAMP price based on BTC movement
     * 
     * @param float $anchorBtcUsd BTC/USD price at session start
     * @param float $anchorMidPrice RWAMP mid price at anchor
     * @param float $currentBtcUsd Current BTC/USD price
     * @param float $velocityMultiplier Velocity multiplier (default 1.0)
     * @return float Anchored mid price
     */
    public function calculateAnchoredPrice(
        float $anchorBtcUsd,
        float $anchorMidPrice,
        float $currentBtcUsd,
        float $velocityMultiplier = 1.0
    ): float {
        // Calculate BTC percentage change
        $btcPctChange = ($currentBtcUsd / $anchorBtcUsd) - 1;
        
        // Apply velocity multiplier
        $rwampPctChange = $btcPctChange * $velocityMultiplier;
        
        // Calculate anchored mid price
        $anchoredMid = $anchorMidPrice * (1 + $rwampPctChange);
        
        return max(0.01, $anchoredMid); // Ensure price is never negative
    }

    /**
     * Calculate buy and sell prices with spread and fees
     * 
     * @param float $midPrice Mid price (anchored)
     * @param float $spreadPkr Spread in PKR
     * @param float $buyFeePct Buy fee percentage
     * @param float $sellFeePct Sell fee percentage
     * @return array ['buy_price' => float, 'sell_price' => float, 'mid_price' => float]
     */
    public function calculatePrices(
        float $midPrice,
        float $spreadPkr,
        float $buyFeePct,
        float $sellFeePct
    ): array {
        // Buy price = mid + spread/2 (user pays more)
        $buyPrice = $midPrice + ($spreadPkr / 2);
        
        // Sell price = mid - spread/2 (user receives less)
        $sellPrice = max(0.01, $midPrice - ($spreadPkr / 2));
        
        return [
            'buy_price' => $buyPrice,
            'sell_price' => $sellPrice,
            'mid_price' => $midPrice,
        ];
    }

    /**
     * Get current game prices (live calculation)
     * 
     * @param float|null $anchorBtcUsd Anchor BTC price (null for new calculation)
     * @param float|null $anchorMidPrice Anchor mid price (null for new calculation)
     * @return array
     */
    public function getCurrentPrices(?float $anchorBtcUsd = null, ?float $anchorMidPrice = null): array
    {
        $params = $this->getGameParameters();
        $btcUsd = $this->getBtcUsdPrice();
        $usdPkr = $this->getUsdPkrRate();
        
        // If anchor not provided, calculate new anchor
        if ($anchorBtcUsd === null || $anchorMidPrice === null) {
            $anchorBtcUsd = $btcUsd;
            $anchorMidPrice = ($btcUsd * $usdPkr) / $params['tokens_per_btc'];
        }
        
        // Calculate anchored price
        $midPrice = $this->calculateAnchoredPrice(
            $anchorBtcUsd,
            $anchorMidPrice,
            $btcUsd,
            $params['velocity_multiplier']
        );
        
        // Calculate buy/sell prices
        $prices = $this->calculatePrices(
            $midPrice,
            $params['spread_pkr'],
            $params['buy_fee_pct'],
            $params['sell_fee_pct']
        );
        
        return array_merge($prices, [
            'btc_usd' => $btcUsd,
            'usd_pkr' => $usdPkr,
            'anchor_btc_usd' => $anchorBtcUsd,
            'anchor_mid_price' => $anchorMidPrice,
        ]);
    }
}


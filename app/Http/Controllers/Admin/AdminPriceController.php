<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\PriceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AdminPriceController extends Controller
{
    /**
     * Display price management page
     */
    public function index()
    {
        $usdPkr = PriceHelper::getUsdToPkrRate();
        
        // Get RWAMP prices
        $rwampPkr = PriceHelper::getRwampPkrPrice();
        $rwampUsd = PriceHelper::getRwampUsdPrice();
        
        // Fetch USDT and BTC prices dynamically
        $usdtUsd = $this->fetchUsdtPrice();
        $usdtPkr = $usdtUsd * $usdPkr;
        
        $btcUsd = $this->fetchBtcPrice();
        $btcPkr = $btcUsd * $usdPkr;
        
        // Get reseller rates from cache or config
        $resellerCommissionRate = Cache::get('reseller_commission_rate', config('crypto.reseller_commission_rate', 0.10));
        $resellerMarkupRate = Cache::get('reseller_markup_rate', config('crypto.reseller_markup_rate', 0.05));
        
        $currentPrices = [
            'rwamp_pkr' => $rwampPkr,
            'rwamp_usd' => $rwampUsd,
            'usdt_usd' => $usdtUsd,
            'usdt_pkr' => $usdtPkr,
            'btc_usd' => $btcUsd,
            'btc_pkr' => $btcPkr,
            'usd_pkr' => $usdPkr,
            'reseller_commission_rate' => $resellerCommissionRate,
            'reseller_markup_rate' => $resellerMarkupRate,
        ];

        return view('dashboard.admin-prices', compact('currentPrices'));
    }

    /**
     * Update prices
     */
    public function update(Request $request)
    {
        $request->validate([
            'rwamp_pkr' => 'required|numeric|min:0.01|max:1000000',
            'reseller_commission_rate' => 'nullable|numeric|min:0|max:100',
            'reseller_markup_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $rwampPkr = (float) $request->rwamp_pkr;
        $usdPkr = PriceHelper::getUsdToPkrRate();
        
        // Auto-calculate RWAMP/USD price from PKR using exchange rate
        $rwampUsd = $rwampPkr / $usdPkr;
        
        // Fetch USDT and BTC prices dynamically from API
        $usdtUsd = $this->fetchUsdtPrice();
        $usdtPkr = $usdtUsd * $usdPkr;
        
        $btcUsd = $this->fetchBtcPrice();
        $btcPkr = $btcUsd * $usdPkr;

        // Store prices in cache
        Cache::forever('crypto_price_rwamp_pkr', $rwampPkr);
        Cache::forever('crypto_price_rwamp_usd', $rwampUsd);
        Cache::forever('crypto_price_usdt_usd', $usdtUsd);
        Cache::forever('crypto_price_usdt_pkr', $usdtPkr);
        Cache::forever('crypto_price_btc_usd', $btcUsd);
        Cache::forever('crypto_price_btc_pkr', $btcPkr);

        // Store reseller rates in cache if provided
        if ($request->has('reseller_commission_rate')) {
            $commissionRate = (float) $request->reseller_commission_rate / 100;
            Cache::forever('reseller_commission_rate', $commissionRate);
        }
        
        if ($request->has('reseller_markup_rate')) {
            $markupRate = (float) $request->reseller_markup_rate / 100;
            Cache::forever('reseller_markup_rate', $markupRate);
        }

        // Clear config cache
        \Artisan::call('config:clear');

        $message = 'Prices updated successfully! RWAMP/USD: $' . number_format($rwampUsd, 4) . ', USDT/USD: $' . number_format($usdtUsd, 4) . ', BTC/USD: $' . number_format($btcUsd, 2);
        
        if ($request->has('reseller_commission_rate') || $request->has('reseller_markup_rate')) {
            $message .= '. Reseller rates updated.';
        }

        return back()->with('success', $message);
    }

    /**
     * Fetch current USDT price from CoinGecko API
     */
    private function fetchUsdtPrice(): float
    {
        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->get('https://api.coingecko.com/api/v3/simple/price', [
                'query' => [
                    'ids' => 'tether',
                    'vs_currencies' => 'usd'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            if (isset($data['tether']['usd'])) {
                return (float) $data['tether']['usd'];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch USDT price from CoinGecko: ' . $e->getMessage());
        }
        
        // Fallback to cached or config value
        $cached = Cache::get('crypto_price_usdt_usd');
        if ($cached !== null) {
            return (float) $cached;
        }
        return (float) config('crypto.rates.usdt_usd', 1.0);
    }

    /**
     * Fetch current BTC price from CoinGecko API
     */
    private function fetchBtcPrice(): float
    {
        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->get('https://api.coingecko.com/api/v3/simple/price', [
                'query' => [
                    'ids' => 'bitcoin',
                    'vs_currencies' => 'usd'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            if (isset($data['bitcoin']['usd'])) {
                return (float) $data['bitcoin']['usd'];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch BTC price from CoinGecko: ' . $e->getMessage());
        }
        
        // Fallback to cached or config value
        $cached = Cache::get('crypto_price_btc_usd');
        if ($cached !== null) {
            return (float) $cached;
        }
        return (float) config('crypto.rates.btc_usd', 60000);
    }
}


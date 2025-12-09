<?php
return [
    'rates' => [
        'rwamp_usd' => (float) env('RWAMP_USD_RATE', 0.011),  // Default, overridden by cache (auto-calculated from PKR)
        'rwamp_pkr' => (float) env('RWAMP_PKR_RATE', 3.0),    // Default RWAMP price in PKR (admin-controlled via panel)
        'usd_pkr'   => (float) env('USD_PKR_RATE', 278),
        'btc_usd'   => (float) env('BTC_USD_RATE', 60000),    // Default, overridden by cache (auto-fetched from API)
        // Admin-controlled USDT prices (set via admin panel, stored in cache)
        // Use PriceHelper functions to get current prices
        'usdt_usd'  => (float) env('USDT_USD_RATE', 1.0),     // Default, overridden by cache (auto-fetched from API)
        'usdt_pkr'  => (float) env('USDT_PKR_RATE', env('USD_PKR_RATE', 278)),  // Default, overridden by cache (auto-calculated)
    ],
    'wallets' => [
        'TRC20' => env('CRYPTO_WALLET_TRC20', ''),
        'ERC20' => env('CRYPTO_WALLET_ERC20', ''),
        'BEP20' => env('CRYPTO_WALLET_BEP20', env('CRYPTO_WALLET_ERC20', '')), // BNB Chain wallet
        'BTC'   => env('CRYPTO_WALLET_BTC', ''),
    ],
    'api_keys' => [
        'etherscan_api_key' => env('ETHERSCAN_API_KEY', ''),
        'alchemy_api_key' => env('ALCHEMY_API_KEY', ''),
        'trongrid_api_key' => env('TRONGRID_API_KEY', ''),
        'blockstream_api_url' => env('BLOCKSTREAM_API_URL', 'https://blockstream.info/api'),
    ],
    'contracts' => [
        'usdt_erc20' => '0xdAC17F958D2ee523a2206206994597C13D831ec7',
        'usdt_trc20' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
        'usdt_bep20' => '0x55d398326f99059fF775485246999027B3197955', // USDT on BNB Chain
    ],
    'confirmations' => [
        'ethereum' => 1,
        'tron' => 1,
        'bitcoin' => 3,
    ],
    'walletconnect_project_id' => env('WALLETCONNECT_PROJECT_ID', ''),
    'features' => [
        // Master switch to enable/disable all crypto payment functionality
        'payments_enabled' => (bool) env('CRYPTO_PAYMENTS_ENABLED', false),
        // Controls loading of WalletConnect modal and wallet linking
        'walletconnect_enabled' => (bool) env('WALLETCONNECT_ENABLED', true),
        // Disable static address payment method (use WalletConnect instead)
        'static_payment_disabled' => (bool) env('STATIC_PAYMENT_DISABLED', true),
    ],
    'reseller_commission_rate' => (float) env('RESELLER_COMMISSION_RATE', 0.10), // 10% commission
    'reseller_markup_rate' => (float) env('RESELLER_MARKUP_RATE', 0.05), // 5% markup for buy-from-reseller
    'presale' => [
        'stage' => (int) env('PRESALE_STAGE', 2), // Default to Stage 2
        'bonus_percentage' => (float) env('PRESALE_BONUS_PERCENTAGE', 10), // Default 10% bonus
        'max_supply' => (int) env('PRESALE_MAX_SUPPLY', 300000000), // Default 10M tokens
        'min_purchase_usd' => (float) env('PRESALE_MIN_PURCHASE_USD', 55), // Default $55 minimum
    ],
];



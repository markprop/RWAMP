<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PriceHelper;
use App\Models\CryptoPayment;
use App\Models\Transaction;

class PageController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index()
    {
        // Presale Statistics
        $tokenPriceUsd = PriceHelper::getRwampUsdPrice();
        $tokenPricePkr = PriceHelper::getRwampPkrPrice();
        
        // Calculate total raised from approved payments (in USD)
        $totalRaisedUsd = CryptoPayment::where('status', 'approved')
            ->sum('usd_amount') ?? 0;
        
        // Calculate total tokens sold from credit transactions
        $totalTokensSold = Transaction::where('type', 'credit')
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
        
        // Presale configuration
        $presaleStage = config('crypto.presale.stage', 2); // Default to Stage 2
        $presaleBonus = config('crypto.presale.bonus_percentage', 10); // Default 10% bonus
        $maxSupply = config('crypto.presale.max_supply', 60000000); // Default 60M tokens
        $minPurchaseUsd = config('crypto.presale.min_purchase_usd', 55); // Default $55 minimum
        
        // Calculate supply progress
        $supplyProgress = $maxSupply > 0 ? ($totalTokensSold / $maxSupply) * 100 : 0;
        $supplyProgress = min(100, max(0, $supplyProgress)); // Clamp between 0-100
        
        // Presale data
        $presaleData = [
            'stage' => $presaleStage,
            'bonus_percentage' => $presaleBonus,
            'token_price_usd' => $tokenPriceUsd,
            'token_price_pkr' => $tokenPricePkr,
            'total_raised_usd' => $totalRaisedUsd,
            'total_tokens_sold' => $totalTokensSold,
            'max_supply' => $maxSupply,
            'supply_progress' => $supplyProgress,
            'min_purchase_usd' => $minPurchaseUsd,
        ];
        
        // Rates and wallets for purchase modal
        $rates = [
            'tokenUsd' => $tokenPriceUsd,
            'tokenPkr' => $tokenPricePkr,
            'usdToPkr' => (float) config('crypto.rates.usd_pkr', 278),
            'usdtUsd' => PriceHelper::getUsdtUsdPrice(),
            'usdtPkr' => PriceHelper::getUsdtPkrPrice(),
            'btcUsd' => PriceHelper::getBtcUsdPrice(),
            'btcPkr' => PriceHelper::getBtcPkrPrice(),
        ];
        
        $wallets = [
            'TRC20' => (string) config('crypto.wallets.TRC20', ''),
            'ERC20' => (string) config('crypto.wallets.ERC20', ''),
            'BEP20' => (string) config('crypto.wallets.BEP20', config('crypto.wallets.ERC20', '')),
            'BTC' => (string) config('crypto.wallets.BTC', ''),
        ];
        
        return view('pages.index', array_merge(compact('presaleData', 'rates', 'wallets'), [
            'title' => 'RWAMP - The Currency of Real Estate Investments',
            'description' => 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia.',
            'keywords' => 'RWAMP, real estate, token, investment, Dubai, Pakistan, Saudi Arabia',
            'ogTitle' => 'RWAMP - The Currency of Real Estate Investments',
            'ogDescription' => 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia.',
            'ogImage' => asset('images/logo.jpeg'),
            'twitterTitle' => 'RWAMP - The Currency of Real Estate Investments',
            'twitterDescription' => 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia.',
            'twitterImage' => asset('images/logo.jpeg'),
        ]));
    }

    /**
     * Display the about page.
     */
    public function about()
    {
        return view('pages.about', [
            'title' => 'About RWAMP - Real Estate Investment Token',
            'description' => 'Learn about RWAMP, the official token for real estate investments across Dubai, Pakistan, and Saudi Arabia.',
            'keywords' => 'about RWAMP, real estate token, investment, Dubai, Pakistan, Saudi Arabia',
        ]);
    }

    /**
     * Display the contact page.
     */
    public function contact()
    {
        return view('pages.contact', [
            'title' => 'Contact RWAMP - Get in Touch',
            'description' => 'Contact RWAMP for investment opportunities, reseller programs, and real estate project inquiries.',
            'keywords' => 'contact RWAMP, investment inquiry, reseller program, real estate contact',
        ]);
    }

    /**
     * Display the become partner page.
     */
    public function becomePartner()
    {
        return view('pages.become-partner', [
            'title' => 'Become a Partner - RWAMP',
            'description' => 'Join our exclusive partner program and earn profits by selling RWAMP tokens.',
            'keywords' => 'become partner, RWAMP partner, reseller program, token partner',
            'ogTitle' => 'Become a Partner - RWAMP',
            'ogDescription' => 'Join our exclusive partner program and earn profits by selling RWAMP tokens.',
            'ogImage' => asset('images/logo.jpeg'),
            'twitterTitle' => 'Become a Partner - RWAMP',
            'twitterDescription' => 'Join our exclusive partner program and earn profits by selling RWAMP tokens.',
            'twitterImage' => asset('images/logo.jpeg'),
        ]);
    }

    /**
     * Display the purchase page.
     */
    public function purchase()
    {
        return view('pages.purchase', [
            'title' => 'Purchase RWAMP Tokens',
            'description' => 'Buy RWAMP tokens securely using Credit Card, Wallet, or Bank Transfer.',
            'keywords' => 'buy RWAMP, purchase token, payment, credit card, wallet, bank transfer',
        ]);
    }

    /**
     * Display the how to buy guide page.
     */
    public function howToBuy()
    {
        return view('pages.how-to-buy', [
            'title' => 'How to Buy RWAMP Tokens - Complete Guide',
            'description' => 'Learn how to set up crypto wallets and purchase RWAMP tokens with our step-by-step guide.',
            'keywords' => 'how to buy RWAMP, crypto wallet setup, MetaMask, Trust Wallet, crypto guide',
            'ogTitle' => 'How to Buy RWAMP Tokens - Complete Guide',
            'ogDescription' => 'Learn how to set up crypto wallets and purchase RWAMP tokens with our step-by-step guide.',
            'ogImage' => asset('images/logo.jpeg'),
            'twitterTitle' => 'How to Buy RWAMP Tokens - Complete Guide',
            'twitterDescription' => 'Learn how to set up crypto wallets and purchase RWAMP tokens.',
            'twitterImage' => asset('images/logo.jpeg'),
        ]);
    }

    /**
     * Display the whitepaper viewer page.
     */
    public function whitepaper()
    {
        return view('pages.whitepaper', [
            'title' => 'RWAMP Whitepaper – RWAMP',
            'description' => 'Read the comprehensive RWAMP whitepaper to learn about our tokenomics, roadmap, and vision.',
            'ogTitle' => 'RWAMP Whitepaper – RWAMP',
            'ogDescription' => 'Read the comprehensive RWAMP whitepaper to learn about our tokenomics, roadmap, and vision.',
            'ogImage' => asset('images/logo.jpeg'),
            'twitterTitle' => 'RWAMP Whitepaper – RWAMP',
            'twitterDescription' => 'Read the comprehensive RWAMP whitepaper to learn about our tokenomics, roadmap, and vision.',
            'twitterImage' => asset('images/logo.jpeg'),
        ]);
    }

    /**
     * Serve the whitepaper PDF with proper headers.
     */
    public function serveWhitepaper()
    {
        $pdfPath = public_path('Whitepaper-Design.pdf');
        
        if (!file_exists($pdfPath)) {
            abort(404, 'Whitepaper not found');
        }

        // Use Laravel's file response and set headers for inline viewing
        $response = response()->file($pdfPath);
        
        // Set headers for inline viewing (not download)
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="RWAMP-Whitepaper.pdf"');
        
        // Remove X-Frame-Options if set by middleware (to allow iframe embedding)
        $response->headers->remove('X-Frame-Options');
        // Set to SAMEORIGIN to allow embedding in same origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        $response->headers->set('Accept-Ranges', 'bytes');
        
        return $response;
    }
}

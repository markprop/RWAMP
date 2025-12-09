@extends('layouts.app')

@push('head')
@php($wcEnabled = config('crypto.features.walletconnect_enabled'))
@if($wcEnabled)
<!-- WalletConnect v2 Modal (only if project ID is configured) -->
<script>
    (function loadWalletConnect() {
        // WalletConnect is optional - MetaMask works directly via window.ethereum
        // Only load WalletConnect if explicitly needed
        const sources = [
            'https://unpkg.com/@walletconnect/modal@2.7.3/dist/index.umd.js',
            'https://cdn.jsdelivr.net/npm/@walletconnect/modal@2.7.3/dist/index.umd.js'
        ];
        function tryNext(i){
            if(i>=sources.length){
                // Silently fail - WalletConnect is optional, MetaMask works without it
                window.walletConnectFallback = true;
                window.walletConnectCDNFailed = true;
                return;
            }
            const s=document.createElement('script');
            s.src=sources[i];
            s.async=true;
            s.onload=function(){
                // Normalise global
                window.WalletConnectModalGlobal = (window.WalletConnectModal && (window.WalletConnectModal.default||window.WalletConnectModal)) || null;
                if(!window.WalletConnectModalGlobal){
                    // Silently handle - WalletConnect is optional
                    window.walletConnectFallback = true;
                }
            };
            s.onerror=function(){
                // Silently try next CDN (no console error)
                tryNext(i+1);
            };
            document.head.appendChild(s);
        }
        tryNext(0);
    })();
    </script>
<script>
    // Initialize WalletConnect Modal (attach to window for global access)
    window.walletConnectModal = window.walletConnectModal || null;
    window.walletConnectLoaded = false;
    
    // Function to initialize WalletConnect (optional - MetaMask works without it)
    // Expose initializer globally so Alpine and other scripts can call it
    window.initializeWalletConnect = function initializeWalletConnect() {
        try {
            if (window.WalletConnectModalGlobal) {
                const projectId = '{{ config("crypto.walletconnect_project_id", "") }}';
                if (!projectId || projectId === 'your-project-id' || projectId === '') {
                    // No project ID configured - WalletConnect not needed
                    window.walletConnectLoaded = false;
                    window.walletConnectFallback = true;
                    return;
                }
                
                window.walletConnectModal = new window.WalletConnectModalGlobal({
                    projectId: projectId,
                    chains: ['eip155:1', 'eip155:56', 'eip155:11155111'], // Ethereum, BNB Chain, Sepolia
                    optionalChains: ['eip155:137', 'eip155:43114'], // Polygon, Avalanche
                    enableNetworkSwitching: true,
                    enableExplorer: true,
                    explorerRecommendedWalletIds: [
                        'c57ca95b47569778a828d19178114f4db188b89b', // MetaMask
                        '4622a2b2d6af1c9844944291e5e7351a6aa24cd7'  // Trust Wallet
                    ],
                    explorerExcludedWalletIds: 'ALL',
                    themeMode: 'light',
                    themeVariables: {
                        '--wcm-z-index': '1000'
                    }
                });
                window.walletConnectLoaded = true;
            } else {
                // WalletConnect not available - this is fine, MetaMask works directly
                window.walletConnectLoaded = false;
                window.walletConnectFallback = true;
            }
        } catch (error) {
            // Silently handle errors - WalletConnect is optional
            window.walletConnectLoaded = false;
            window.walletConnectFallback = true;
        }
    }
    
    // Try to initialize when DOM is ready (only if WalletConnect is enabled)
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a bit for the script to load
        setTimeout(window.initializeWalletConnect, 1000);
    });
    
    // Also try to initialize when the script loads
    window.addEventListener('load', function() {
        if (!window.walletConnectLoaded && !window.walletConnectFallback) {
            setTimeout(window.initializeWalletConnect, 500);
        }
    });
</script>
@endif
@endpush

@section('content')
<div class="min-h-screen bg-white" x-data="purchaseFlow()">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Purchase RWAMP Tokens</h1>
            <p class="text-white/80 mt-2">Buy using cryptocurrency with WalletConnect. Prices are set by admin.</p>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-4 py-10 space-y-10">
        @php($paymentsDisabled = (bool) ($paymentsDisabled ?? config('crypto.features.payments_enabled') === false))
        
        {{-- KYC restriction disabled - all users can purchase --}}
        {{-- KYC code kept intact but not enforced on purchase page --}}
        @auth
            @php($user = auth()->user())
            {{-- KYC warning removed - users can purchase without KYC --}}
        @endauth

        <!-- New Simple Purchase Flow (Single Step) -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h2 class="text-xl font-montserrat font-bold mb-4">Buy RWAMP Tokens</h2>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Token Quantity (min 1000)</label>
                    <input 
                        x-model="formattedTokenQuantity" 
                        @input="handleTokenQuantityInput()"
                        @keyup="handleTokenQuantityInput()"
                        @change="handleTokenQuantityInput()"
                        type="text" 
                        class="form-input text-lg" 
                        placeholder="Enter token amount"
                    />
                    <p class="text-xs text-gray-600 mt-1">Minimum purchase: 1000 tokens</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RWAMP Token Price</label>
                        <div class="text-sm font-semibold text-gray-600" x-html="formatPriceTag(rates.tokenPkr, {size: 'small', class: 'inline'}) + ' per token'"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">USDT Amount (real‑time)</label>
                        <div class="text-2xl font-mono font-bold text-primary" x-text="usdtAmount"></div>
                        <div class="text-xs text-gray-500" x-text="'USDT price: $' + usdtUsd.toFixed(4) + ' | ₨' + formatNumberFixed(usdtPkr, 2) + ' (Admin Set)'"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PKR Amount (real‑time)</label>
                        <div class="text-2xl font-mono font-bold text-primary" x-html="formatPriceTag(pkrAmount || 0, {size: 'large'})"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div 
                            @click="selectNetwork('ERC20')" 
                            :class="{ 'is-selected': selectedNetwork === 'ERC20' }"
                            class="rw-pay-option cursor-pointer"
                        >
                            <div class="rw-pay-icon">🔵</div>
                            <div class="font-montserrat font-bold">ERC20</div>
                            <div class="text-sm text-gray-600">USDT (Ethereum)</div>
                        </div>
                        <div 
                            @click="selectNetwork('BEP20')" 
                            :class="{ 'is-selected': selectedNetwork === 'BEP20' }"
                            class="rw-pay-option cursor-pointer"
                        >
                            <div class="rw-pay-icon">🟡</div>
                            <div class="font-montserrat font-bold">BEP20</div>
                            <div class="text-sm text-gray-600">USDT (BNB Chain)</div>
                            <div class="rw-badge rw-badge-secure">Recommended</div>
                        </div>
                        <div 
                            @click="selectNetwork('TRC20')" 
                            :class="{ 'is-selected': selectedNetwork === 'TRC20' }"
                            class="rw-pay-option cursor-pointer"
                        >
                            <div class="rw-pay-icon">🟢</div>
                            <div class="font-montserrat font-bold">TRC20</div>
                            <div class="text-sm text-gray-600">USDT (Tron)</div>
                            <div class="rw-badge rw-badge-secure">Fast & Cheap</div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button 
                        x-show="!isWalletConnected" 
                        @click="connectWallet()" 
                        :disabled="isConnecting || !canConnectWallet()" 
                        class="btn-primary px-6 py-3 disabled:opacity-60 disabled:cursor-not-allowed"
                        :title="getConnectButtonTooltip()"
                    >
                        <span x-show="!isConnecting">Connect Wallet</span>
                        <span x-show="isConnecting">Connecting...</span>
                    </button>
                    <button 
                        x-show="isWalletConnected" 
                        @click="executePayment()" 
                        :disabled="!canPay() || isProcessingPayment" 
                        class="btn-primary px-6 py-3 disabled:opacity-60 disabled:cursor-not-allowed"
                        :title="getPayButtonTooltip()"
                    >
                        <span x-show="!isProcessingPayment">
                            Pay <span x-text="getUsdtAmountForPayment()"></span> USDT
                        </span>
                        <span x-show="isProcessingPayment">Processing...</span>
                    </button>
                </div>
                <!-- Helpful messages explaining why button might be disabled -->
                <div x-show="!canConnectWallet() && !isWalletConnected" class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="text-sm text-blue-800">
                        <strong>ℹ️ Why can't I connect?</strong>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li x-show="tokenQuantity < minTokenQuantity">Minimum purchase is <span x-text="minTokenQuantity"></span> tokens (you have <span x-text="tokenQuantity"></span>)</li>
                            <li x-show="!selectedNetwork">Please select a payment network (ERC20, BEP20, or TRC20)</li>
                            <li x-show="!walletConnectEnabled" class="text-red-600 font-semibold">
                                ❌ WalletConnect is currently disabled. 
                                <span class="text-xs">(Check WALLETCONNECT_ENABLED in .env file)</span>
                            </li>
                        </ul>
                        <div x-show="paymentsDisabled && walletConnectEnabled" class="mt-2 text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded p-2">
                            ⚠️ Note: Payments are currently disabled, but you can still connect your wallet. 
                            Payments will be enabled when you try to complete the transaction.
                        </div>
                    </div>
                </div>
                
                <!-- Debug info (only show in development) -->
                @if(app()->environment('local'))
                <div class="mt-2 text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded p-2">
                    <strong>Debug Info:</strong>
                    <ul class="list-disc list-inside mt-1">
                        <li>Token Quantity: <span x-text="tokenQuantity"></span></li>
                        <li>Selected Network: <span x-text="selectedNetwork || 'None'"></span></li>
                        <li>WalletConnect Enabled: <span x-text="walletConnectEnabled"></span></li>
                        <li>Payments Disabled: <span x-text="paymentsDisabled"></span></li>
                        <li>Can Connect: <span x-text="canConnectWallet()"></span></li>
                    </ul>
                </div>
                @endif
                <div x-show="isWalletConnected" class="bg-green-50 border border-green-200 rounded-lg p-3 mt-3">
                    <div class="flex items-center gap-2">
                        <span class="text-green-600">✓</span>
                        <span class="text-sm text-green-800">Wallet Connected:</span>
                        <code class="text-xs font-mono text-green-700" x-text="connectedAddress"></code>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-600 mt-4">Token price: ${{ number_format($rates['tokenUsd'], 4) }} per RWAMP. USDT rate updates live.</p>
        </div>

        <!-- WalletConnect Usage Guide -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-lg p-6 mb-6 border border-blue-200">
            <h3 class="text-xl font-montserrat font-bold mb-4 flex items-center gap-2">
                <span>📚</span>
                <span>How to Purchase with WalletConnect</span>
            </h3>
            <div class="space-y-4">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm">1</span>
                        Enter Token Quantity
                    </h4>
                    <p class="text-sm text-gray-600 ml-8">Enter the number of RWAMP tokens you want to purchase (minimum 1000 tokens). The USDT and PKR amounts will update automatically based on live rates.</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm">2</span>
                        Select Payment Network
                    </h4>
                    <p class="text-sm text-gray-600 ml-8 mb-2">Choose your preferred network:</p>
                    <ul class="text-sm text-gray-600 ml-8 space-y-1 list-disc list-inside">
                        <li><strong>BEP20 (BNB Chain):</strong> Recommended - Lower fees, faster transactions</li>
                        <li><strong>ERC20 (Ethereum):</strong> Widely supported, higher fees</li>
                        <li><strong>TRC20 (Tron):</strong> Fast & cheap, but requires manual transfer (not supported via WalletConnect)</li>
                    </ul>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm">3</span>
                        Connect Your Wallet
                    </h4>
                    <p class="text-sm text-gray-600 ml-8 mb-2">Click "Connect Wallet" and select your wallet:</p>
                    <ul class="text-sm text-gray-600 ml-8 space-y-1 list-disc list-inside">
                        <li><strong>MetaMask:</strong> Browser extension or mobile app</li>
                        <li><strong>Trust Wallet:</strong> Mobile wallet app</li>
                        <li><strong>Coinbase Wallet:</strong> Mobile or browser extension</li>
                        <li>Or any other WalletConnect-compatible wallet</li>
                    </ul>
                    <p class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded p-2 mt-2 ml-8">
                        ⚠️ <strong>Note:</strong> Make sure you have USDT in your wallet on the selected network before proceeding.
                    </p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm">4</span>
                        Approve & Send Payment
                    </h4>
                    <p class="text-sm text-gray-600 ml-8 mb-2">After connecting your wallet:</p>
                    <ul class="text-sm text-gray-600 ml-8 space-y-1 list-disc list-inside">
                        <li>Click "Pay with Wallet" button</li>
                        <li>Your wallet will prompt you to approve the transaction</li>
                        <li>Review the amount and network carefully</li>
                        <li>Confirm the transaction in your wallet</li>
                        <li>Wait for transaction confirmation (usually 1-2 minutes)</li>
                    </ul>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm">5</span>
                        Automatic Token Credit
                    </h4>
                    <p class="text-sm text-gray-600 ml-8">Once your payment is confirmed on the blockchain, your RWAMP tokens will be automatically credited to your account. You'll receive an email confirmation.</p>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                    <h4 class="font-semibold text-yellow-800 mb-2">⚠️ Important Security Tips:</h4>
                    <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                        <li>Always verify the receiving address matches the official RWAMP wallet</li>
                        <li>Double-check the network (ERC20, BEP20, or TRC20) before sending</li>
                        <li>Never share your wallet's private key or seed phrase</li>
                        <li>Start with a small test transaction if you're unsure</li>
                        <li>Keep your wallet software updated</li>
                    </ul>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold text-green-800 mb-2">✅ Need Help?</h4>
                    <p class="text-sm text-green-700">If you encounter any issues:</p>
                    <ul class="text-sm text-green-700 space-y-1 list-disc list-inside mt-1">
                        <li>Check that your wallet is connected to the correct network</li>
                        <li>Ensure you have sufficient USDT balance (including gas fees)</li>
                        <li>Contact support if your payment is not detected after 10 minutes</li>
                    </ul>
                </div>
            </div>
        </div>
        @if (app()->environment('local') && app()->hasDebugModeEnabled() && (\Illuminate\Support\Str::contains($wallets['TRC20'] ?? '', 'Your') || \Illuminate\Support\Str::contains($wallets['ERC20'] ?? '', 'Your') || \Illuminate\Support\Str::contains($wallets['BTC'] ?? '', 'Your')))        
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
                ⚠️ <strong>Dev Notice:</strong> Replace placeholder wallet addresses in .env before testing payments.
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">{{ $errors->first() }}</div>
        @endif
        @if (session('status'))
            <div class="rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        <!-- Step Progress Indicator -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">ℹ️</span>
                </div>
                <div>
                    <div class="font-medium text-blue-800">How to Purchase RWAMP Tokens</div>
                    <div class="text-sm text-blue-600">1) Enter quantity, 2) Click Pay Now to get USDT (TRC20) address & QR, 3) Send payment and paste your transaction hash to submit for review.</div>
                </div>
            </div>
        </div>

        <!-- Old multi‑step flow hidden (kept for reference) -->
        <div class="rw-stepper" x-cloak x-show="false">
            <li :class="{ 'is-active': currentStep === 1, 'is-complete': currentStep > 1 }" @click="goToStep(1)" class="cursor-pointer hover:bg-gray-50 transition-colors duration-200 rounded-lg p-2">
                <span class="block">1</span>
                <span class="block">Calculate Amount</span>
            </li>
            <li :class="{ 'is-active': currentStep === 2, 'is-complete': currentStep > 2 }" @click="goToStep(2)" class="cursor-pointer hover:bg-gray-50 transition-colors duration-200 rounded-lg p-2">
                <span class="block">2</span>
                <span class="block">Connect Wallet</span>
            </li>
            <li :class="{ 'is-active': currentStep === 3, 'is-complete': currentStep > 3 }" @click="goToStep(3)" class="cursor-pointer hover:bg-gray-50 transition-colors duration-200 rounded-lg p-2">
                <span class="block">3</span>
                <span class="block">Payment Status</span>
            </li>
        </div>

        <!-- Step 1: Amount Calculator -->
        <div x-cloak x-show="false" x-transition class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h2 class="text-xl font-montserrat font-bold mb-4">Step 1: Calculate Your Purchase</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Token Quantity (min 1000)</label>
                    <input 
                        x-model="formattedTokenQuantity" 
                        @input="handleTokenQuantityInput()"
                        @keyup="handleTokenQuantityInput()"
                        @change="handleTokenQuantityInput()"
                        type="text" 
                        class="form-input text-lg" 
                        placeholder="Enter token amount"
                    />
                    <p class="text-xs text-gray-600 mt-1">Minimum purchase: 1000 tokens</p>
                </div>
                <div class="space-y-3">
                <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">USD Amount</label>
                        <div class="text-2xl font-mono font-bold text-primary" x-text="usdAmount"></div>
                </div>
                <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PKR Amount</label>
                        <div class="text-2xl font-mono font-bold text-primary" x-text="pkrAmount"></div>
                </div>
                </div>
            </div>
            @if(!$paymentsDisabled)
            <div class="mt-6">
                <button @click="nextStep()" :disabled="tokenQuantity < minTokenQuantity" class="btn-primary">
                    Continue to Connect Wallet
                </button>
            </div>
            @endif
            <p class="text-xs text-gray-600 mt-4">Token price: ${{ number_format($rates['tokenUsd'], 4) }} per RWAMP, ₨{{ number_format($rates['tokenUsd'] * $rates['usdToPkr'], 2) }} per RWAMP</p>
        </div>

        <!-- Step 2: Connect Wallet -->
        <div x-cloak x-show="false" x-transition class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h2 class="text-xl font-montserrat font-bold mb-4">Step 2: Connect Your Wallet</h2>
            
            <div x-show="!isWalletConnected" class="text-center">
                <div class="mb-6">
                    <div class="w-24 h-24 bg-gradient-to-r from-primary to-red-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-4xl">🔗</span>
                    </div>
                    <h3 class="text-xl font-montserrat font-bold mb-2">Connect Your Wallet</h3>
                    <p class="text-gray-600 mb-6">Connect your crypto wallet to proceed with the purchase</p>
                </div>
                
                <div class="space-y-4">
                    <button @click="connectWallet()" :disabled="isConnecting" class="btn-primary text-lg px-8 py-4 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!isConnecting">Connect Wallet</span>
                        <span x-show="isConnecting">Connecting...</span>
                    </button>
                    
                    <div class="text-sm text-gray-500 mb-4">or</div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-800 mb-2">Manual Wallet Address</h4>
                        <p class="text-sm text-gray-600 mb-3">If WalletConnect is not working, you can enter your wallet address manually:</p>
                        <input 
                            x-model="manualWalletAddress" 
                            type="text" 
                            placeholder="Enter your wallet address (0x... or T...)"
                            class="form-input w-full mb-3"
                        />
                        <button @click="useManualAddress()" class="btn-secondary text-sm">
                            Use This Address
                        </button>
                    </div>
                    <div class="text-sm">
                        <a href="{{ route('how-to-buy') }}" class="underline hover:text-primary">Don't have a wallet? Learn how to buy</a>
                    </div>
                    
                    <div class="flex justify-center">
                        <button @click="prevStep()" class="btn-secondary">
                            ← Back to Amount
                        </button>
                    </div>
                </div>
                
                <div class="mt-6 text-sm text-gray-500">
                    <p>Supported wallets: MetaMask, Trust Wallet, Coinbase Wallet, and more</p>
                </div>
            </div>

            <div x-show="isWalletConnected" class="space-y-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-sm">✓</span>
                        </div>
                <div>
                            <div class="font-medium text-green-800">Wallet Connected</div>
                            <div class="text-sm text-green-600" x-text="connectedAddress"></div>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div 
                        @click="selectNetwork('TRC20')" 
                        :class="{ 'is-selected': selectedNetwork === 'TRC20' }"
                        class="rw-pay-option cursor-pointer"
                        data-network="TRC20" data-address="{{ $wallets['TRC20'] }}"
                    >
                        <div class="rw-pay-icon">🟢</div>
                        <div class="font-montserrat font-bold">TRC20</div>
                        <div class="text-sm text-gray-600">USDT (Tron)</div>
                        <div class="rw-badge rw-badge-secure">Fast & Cheap</div>
                    </div>
                    <div 
                        @click="selectNetwork('ERC20')" 
                        :class="{ 'is-selected': selectedNetwork === 'ERC20' }"
                        class="rw-pay-option cursor-pointer"
                        data-network="ERC20" data-address="{{ $wallets['ERC20'] }}"
                    >
                        <div class="rw-pay-icon">🔵</div>
                        <div class="font-montserrat font-bold">ERC20</div>
                        <div class="text-sm text-gray-600">USDT (Ethereum)</div>
                        <div class="rw-badge">Widely Supported</div>
                    </div>
                    <div 
                        @click="selectNetwork('BTC')" 
                        :class="{ 'is-selected': selectedNetwork === 'BTC' }"
                        class="rw-pay-option cursor-pointer"
                        data-network="BTC" data-address="{{ $wallets['BTC'] }}"
                    >
                        <div class="rw-pay-icon">🟠</div>
                        <div class="font-montserrat font-bold">BTC</div>
                        <div class="text-sm text-gray-600">Bitcoin</div>
                        <div class="rw-badge">Original Crypto</div>
                    </div>
                </div>

                <div x-show="selectedNetwork" x-transition class="bg-gray-50 rounded-lg p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                <div>
                            <h3 class="font-montserrat font-bold mb-3">Send Payment To</h3>
                            <div class="bg-white border rounded-lg p-4">
                                <code class="break-all text-sm font-mono" x-text="getWalletAddress()"></code>
                                <button @click="copyAddress()" class="mt-2 btn-secondary text-sm">
                                    📋 Copy Address
                                </button>
                            </div>
                            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="text-sm font-medium text-red-800">⚠️ Important</div>
                                <div class="text-sm text-red-700">Send only <span x-text="selectedNetwork"></span> tokens. Other tokens will be lost!</div>
                            </div>
                            <div class="mt-3 text-sm text-gray-600" x-show="getWalletAddress()">
                                <div>View address on explorer:</div>
                                <ul class="list-disc ml-5">
                                    <li x-show="selectedNetwork==='ERC20'"><a :href="'https://etherscan.io/address/' + getWalletAddress()" target="_blank" rel="noopener" class="underline">Etherscan</a></li>
                                    <li x-show="selectedNetwork==='TRC20'"><a :href="'https://tronscan.org/#/address/' + getWalletAddress()" target="_blank" rel="noopener" class="underline">Tronscan</a></li>
                                    <li x-show="selectedNetwork==='BTC'"><a :href="'https://blockchair.com/bitcoin/address/' + getWalletAddress()" target="_blank" rel="noopener" class="underline">Blockchair</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center">
                            <h3 class="font-montserrat font-bold mb-3">QR Code</h3>
                            <div class="bg-white border rounded-lg p-4 inline-block">
                                <img :src="getQrCode()" alt="Wallet QR Code" class="w-48 h-48 mx-auto" />
                            </div>
                            <p class="text-xs text-gray-600 mt-2">Scan with your wallet app</p>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button @click="prevStep()" class="btn-secondary">
                            ← Back
                        </button>
                        <button @click="nextStep()" class="btn-primary">
                            Continue to Payment Status
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Payment Status -->
        <div x-cloak x-show="false" x-transition class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h2 class="text-xl font-montserrat font-bold mb-4">Step 3: Payment Status</h2>
            
            <div class="space-y-6">
                <!-- Payment Summary -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="font-montserrat font-bold mb-4">Payment Summary</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-600">Token Quantity</div>
                            <div class="text-lg font-bold" x-text="formattedTokenQuantity"></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Network</div>
                            <div class="text-lg font-bold" x-text="selectedNetwork"></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">USD Amount</div>
                            <div class="text-lg font-bold text-primary" x-text="usdAmount"></div>
                        </div>
                <div>
                            <div class="text-sm text-gray-600">PKR Amount</div>
                            <div class="text-lg font-bold text-primary" x-text="pkrAmount"></div>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="font-montserrat font-bold mb-3 text-blue-800">Payment Instructions</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">1.</span>
                            <p class="text-sm text-blue-700">Send exactly <span class="font-bold" x-text="usdAmount"></span> USDT to the address below</p>
                        </div>
                        <div class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">2.</span>
                            <p class="text-sm text-blue-700">Use <span class="font-bold" x-text="selectedNetwork"></span> network only</p>
                        </div>
                        <div class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">3.</span>
                            <p class="text-sm text-blue-700">After sending, submit your transaction hash below for manual approval</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Address -->
                <div class="bg-white border rounded-lg p-6">
                    <h3 class="font-montserrat font-bold mb-3">Send Payment To</h3>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <code class="break-all text-sm font-mono" x-text="getWalletAddress()"></code>
                        <button @click="copyAddress()" class="mt-2 btn-secondary text-sm">
                            📋 Copy Address
                        </button>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="text-center">
                    <h3 class="font-montserrat font-bold mb-3">QR Code</h3>
                    <div class="bg-white border rounded-lg p-4 inline-block">
                        <img :src="getQrCode()" alt="Wallet QR Code" class="w-48 h-48 mx-auto" />
                    </div>
                    <p class="text-xs text-gray-600 mt-2">Scan with your wallet app</p>
                </div>

                <!-- Submit TX Hash -->
                <div class="bg-white border rounded-lg p-6">
                    <h3 class="font-montserrat font-bold mb-3">Submit Transaction Hash</h3>
                    <div class="grid md:grid-cols-3 gap-3 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Hash</label>
                            <input x-model="txHash" type="text" placeholder="Paste your TX hash (0x..., T..., or btc txid)" class="form-input w-full" />
                        </div>
                        <div>
                            <button @click="submitTxHash()" :disabled="!txHash || !selectedNetwork" class="btn-primary w-full">Submit</button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 mt-2">We will review and approve manually. You can check status below.</p>
                </div>

                <!-- Status Display -->
                <div class="text-center">
                    <div class="inline-flex items-center px-6 py-3 bg-yellow-100 border border-yellow-300 rounded-lg">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-yellow-600 mr-3"></div>
                        <span class="text-yellow-800 font-medium">Pending manual review...</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">After approval, your tokens will be credited.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 justify-center">
                    <button @click="prevStep()" class="btn-secondary">
                        ← Back to Wallet
                    </button>
                    <button @click="checkPaymentStatus()" class="btn-primary">
                        Check Payment Status
                    </button>
                </div>

                <!-- Help Section -->
                <div class="bg-gray-50 border rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-800 mb-2">💡 Need Help?</div>
                    <div class="text-sm text-gray-600">
                        Learn how to set up MetaMask, Trust Wallet, or other crypto wallets in our 
                        <a href="{{ route('how-to-buy') }}" class="underline hover:text-primary">How to Buy guide</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div x-show="toast.visible" class="fixed bottom-4 right-4 z-60">
        <div :class="{
            'bg-green-600 text-white': toast.type==='success',
            'bg-yellow-500 text-white': toast.type==='warning',
            'bg-red-600 text-white': toast.type==='error',
            'bg-gray-800 text-white': toast.type==='info'
        }" class="px-4 py-3 rounded shadow-lg font-medium">
            <span x-text="toast.message"></span>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div x-cloak x-show="paymentConfirmationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="cancelPaymentConfirmation()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-sm w-full mx-4 transform transition-all" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="p-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-montserrat font-bold text-gray-900">Confirm Payment</h3>
                    <button @click="cancelPaymentConfirmation()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Payment Details -->
                <div class="space-y-3 mb-4">
                    <div class="bg-gradient-to-r from-primary to-red-600 rounded-lg p-3 text-white">
                        <div class="text-xs opacity-90 mb-1">You are about to receive coins</div>
                        <div class="text-2xl font-bold" x-text="paymentConfirmData.tokens + ' RWAMP'"></div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-200">
                            <span class="text-xs text-gray-600">Token Price:</span>
                            <span class="text-xs font-semibold text-gray-900" x-text="'₨' + formatNumberFixed(paymentConfirmData.tokenPrice, 2) + ' per token'"></span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-200">
                            <span class="text-xs text-gray-600">Total Amount:</span>
                            <span class="text-xs font-semibold text-primary" x-text="paymentConfirmData.usdtAmount + ' USDT'"></span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-200">
                            <span class="text-xs text-gray-600">PKR Equivalent:</span>
                            <span class="text-xs font-semibold text-gray-900" x-text="'₨' + formatNumberFixed(paymentConfirmData.pkrAmount, 2)"></span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-200">
                            <span class="text-xs text-gray-600">Network:</span>
                            <span class="text-xs font-semibold text-gray-900" x-text="paymentConfirmData.network"></span>
                        </div>
                        <div class="pt-1.5">
                            <div class="text-xs text-gray-600 mb-1">To Address:</div>
                            <div class="font-mono text-xs bg-gray-100 rounded p-1.5 break-all" x-text="paymentConfirmData.toAddress"></div>
                        </div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 mb-3">
                    <div class="flex items-start">
                        <svg class="w-4 h-4 text-yellow-600 mt-0.5 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs text-yellow-800">Please review all details carefully. This transaction cannot be reversed once confirmed.</p>
                    </div>
                </div>
                
                <!-- MetaMask Note -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 mb-4">
                    <div class="flex items-start">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-xs text-blue-800">
                            <p class="font-semibold mb-0.5">MetaMask Security Warning:</p>
                            <p class="text-xs leading-tight">If MetaMask shows a security warning about localhost, this is normal for development. The transaction is safe - verify the USDT amount (<span x-text="paymentConfirmData.usdtAmount" class="font-mono font-bold"></span> USDT) and recipient address match what's shown above before confirming.</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button @click="cancelPaymentConfirmation()" class="flex-1 btn-secondary text-sm py-2">
                        Cancel
                    </button>
                    <button @click="proceedWithPayment()" :disabled="isProcessingPayment" class="flex-1 btn-primary text-sm py-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!isProcessingPayment">Confirm & Pay</span>
                        <span x-show="isProcessingPayment" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Status Modal -->
    <div x-cloak x-show="paymentStatusModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="paymentStatusModal=false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl p-6 mx-auto max-w-md w-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-montserrat font-bold">Payment Status</h3>
                <button @click="paymentStatusModal=false" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <div class="space-y-4">
                <div x-show="paymentStatus === 'pending'" class="text-center py-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                    <p class="text-gray-700 font-semibold">Processing payment...</p>
                    <p class="text-sm text-gray-500 mt-2">Please confirm the transaction in your wallet (MetaMask/Trust Wallet)</p>
                    <p class="text-xs text-gray-400 mt-1">The exact USDT amount will be shown in your wallet</p>
                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 text-left">
                        <p class="text-xs text-blue-800">
                            <strong>Note:</strong> If you see a security warning in MetaMask about localhost, this is normal for development. 
                            The transaction is safe - verify the USDT amount and recipient address match what's shown above before confirming.
                        </p>
                    </div>
                </div>
                <div x-show="paymentStatus === 'success'" class="text-center py-4">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl">✓</span>
                    </div>
                    <p class="text-green-700 font-semibold text-lg">Payment Successful!</p>
                    <div class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3 text-left">
                        <p class="text-sm text-gray-700"><strong>Transaction Hash:</strong></p>
                        <code class="text-xs text-gray-600 break-all" x-text="lastTxHash || 'Pending...'"></code>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Your tokens will be credited automatically once the transaction is confirmed on-chain (usually 1-2 minutes).</p>
                    <p class="text-xs text-blue-600 mt-2">
                        <a :href="'https://' + (selectedNetwork === 'BEP20' ? 'bscscan.com' : 'etherscan.io') + '/tx/' + lastTxHash" target="_blank" class="underline">
                            View on Block Explorer →
                        </a>
                    </p>
                </div>
                <div x-show="paymentStatus === 'error'" class="text-center py-4">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl">✕</span>
                    </div>
                    <p class="text-red-700 font-semibold">Payment Failed</p>
                    <p class="text-sm text-gray-600 mt-2" x-text="paymentError || 'Please try again'"></p>
                </div>
                <div class="mt-4 pt-4 border-t text-right">
                    <button @click="paymentStatusModal=false" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Define purchaseFlow first (base function)
function purchaseFlow() {
    return {
        paymentsDisabled: {{ ($paymentsDisabled ?? (config('crypto.features.payments_enabled') === false)) ? 'true' : 'false' }},
        staticPaymentDisabled: {{ config('crypto.features.static_payment_disabled', true) ? 'true' : 'false' }},
        walletConnectEnabled: {{ config('crypto.features.walletconnect_enabled', true) ? 'true' : 'false' }},
        currentStep: 1,
        minTokenQuantity: 1000,
        tokenQuantity: 1000,
        formattedTokenQuantity: '1000',
        selectedNetwork: 'BEP20',
        txHash: '',
        lastTxHash: '',
        usdAmount: 0,
        pkrAmount: 0,
        pkrAmountLive: 'PKR 0.00',
        usdtAmount: 'USDT 0.00',
        calculatedUsdtAmount: 0,  // Store calculated USDT amount for payment
        usdtUsd: {{ (float) ($rates['usdtUsd'] ?? 1.0) }},  // Admin-set USDT price in USD
        usdtPkr: {{ (float) ($rates['usdtPkr'] ?? $rates['usdToPkr'] ?? 278) }},  // Admin-set USDT price in PKR
        bonusPercentage: 0,
        bonusTokens: 0,
        isWalletConnected: false,
        connectedAddress: '',
        manualWalletAddress: '',
        isConnecting: false,
        isProcessingPayment: false,
        paymentStatus: '', // 'pending', 'success', 'error'
        paymentConfirmationModal: false,
        paymentConfirmData: {
            tokens: '',
            tokenPrice: 0,
            usdtAmount: '',
            pkrAmount: 0,
            network: '',
            toAddress: ''
        },
        paymentStatusModal: false,
        paymentError: '',
        walletProvider: null, // Will hold the provider instance
        toast: { visible: false, message: '', type: 'success' },
        submitAlert: { visible: false, message: '', type: 'success' },
        modalOpen: false,
        isSubmitting: false,
        rates: {
            tokenUsd: {{ $rates['tokenUsd'] }},  // RWAMP price in USD (auto-calculated from PKR)
            tokenPkr: {{ (float) ($rates['tokenPkr'] ?? ($rates['tokenUsd'] * $rates['usdToPkr'])) }},  // RWAMP price in PKR (admin-set)
            usdToPkr: {{ $rates['usdToPkr'] }},
            btcUsd: {{ (float) ($rates['btcUsd'] ?? config('crypto.rates.btc_usd', 60000)) }},  // BTC price in USD (auto-fetched)
            btcPkr: {{ (float) ($rates['btcPkr'] ?? (($rates['btcUsd'] ?? config('crypto.rates.btc_usd', 60000)) * ($rates['usdToPkr'] ?? 278))) }}  // BTC price in PKR (auto-calculated)
        },
        
        
        init() {
            this.formattedTokenQuantity = this.formatNumber(this.tokenQuantity);
            this.calculateAmounts();
            // Prices are now admin-controlled, no need to fetch from API
            // Admin can update prices via admin panel, and they'll be applied immediately
        },

        formatNumber(value) {
            if (isNaN(value) || value === null || value === '') return '';
            const num = parseFloat(value);
            // For very small numbers, show more decimal places
            if (num < 0.01 && num > 0) {
                return num.toFixed(7); // Show up to 7 decimal places for small numbers
            }
            // For larger numbers, format with commas
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 7
            }).format(num);
        },

        formatNumberFixed(value, digits = 2) {
            if (isNaN(value) || value === null || value === '') return '0.00';
            const num = parseFloat(value);
            // For very small numbers, show more decimal places automatically
            if (num < 0.01 && num > 0) {
                return num.toFixed(7);
            }
            try { 
                return new Intl.NumberFormat('en-US', { 
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits
                }).format(num); 
            } catch (_) { 
                return num.toFixed(digits); 
            }
        },

        getUsdNumeric() {
            // Convert formatted currency string like $1,234.56 to 1234.56
            const num = String(this.usdAmount).replace(/[^0-9.]/g, '');
            return parseFloat(num || '0');
        },
        
        parseFormattedNumber(formattedString) {
            if (!formattedString || formattedString === '') return 0;
            // Remove commas but keep decimal point and numbers
            const cleaned = formattedString.replace(/,/g, '').replace(/[^0-9.]/g, '');
            return cleaned === '' ? 0 : parseFloat(cleaned) || 0;
        },
        
        handleTokenQuantityInput() {
            const rawValue = this.formattedTokenQuantity;
            const parsedValue = this.parseFormattedNumber(rawValue);
            
            // Ensure minimum value
            if (parsedValue < this.minTokenQuantity && parsedValue > 0) {
                this.tokenQuantity = this.minTokenQuantity;
                this.formattedTokenQuantity = this.minTokenQuantity.toString();
            } else if (parsedValue >= this.minTokenQuantity) {
                this.tokenQuantity = parsedValue;
                this.formattedTokenQuantity = this.formatNumber(parsedValue);
            } else if (parsedValue === 0) {
                this.tokenQuantity = 0;
                this.formattedTokenQuantity = '';
            }
            
            this.calculateAmounts();
        },
        
        calculateAmounts() {
            // Ensure tokenQuantity is a number
            const quantity = parseFloat(this.tokenQuantity) || 0;
            
            // Calculate using RWAMP token price
            // Option 1: Calculate from PKR price (more accurate)
            const pkrValue = quantity * (this.rates.tokenPkr || (this.rates.tokenUsd * this.rates.usdToPkr));
            
            // Option 2: Calculate USD value from token price
            const usdValue = quantity * this.rates.tokenUsd;
            
            // Calculate USDT amount needed (USD value / USDT price in USD)
            const usdtValue = usdValue / (this.usdtUsd || 1);
            
            // PKR value can be calculated directly from token PKR price or from USDT amount
            // Using direct calculation from token PKR price for accuracy
            const pkrValueLive = pkrValue;
            
            // Format USD amount
            this.usdAmount = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 4
            }).format(usdValue);
            
            // Format live USDT and PKR amounts
            this.usdtAmount = 'USDT ' + this.formatNumberFixed(usdtValue, 2);
            this.pkrAmountLive = 'PKR ' + this.formatNumberFixed(pkrValueLive, 2);
            this.pkrAmount = pkrValueLive; // Store numeric value for price component
            
            // Store calculated USDT amount for payment execution
            this.calculatedUsdtAmount = usdtValue;
            
            // Remove bonus calculation
            this.bonusPercentage = 0;
            this.bonusTokens = 0;
        },
        
        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },
        
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        
        goToStep(step) {
            if (step >= 1 && step <= 3) {
                this.currentStep = step;
            }
        },
        
        selectNetwork(network) {
            this.selectedNetwork = network;
        },

        canConnectWallet() {
            // Allow wallet connection if basic requirements are met
            // Note: We don't require token quantity or payments to be enabled just to connect wallet
            // Token quantity and payment checks happen when actually trying to pay
            // Users should be able to connect their wallet first, then enter the amount
            const canConnect = this.selectedNetwork && this.walletConnectEnabled;
            
            // Debug logging (remove in production)
            if (!canConnect) {
                console.log('Wallet connection disabled:', {
                    tokenQuantity: this.tokenQuantity,
                    selectedNetwork: this.selectedNetwork,
                    walletConnectEnabled: this.walletConnectEnabled,
                    paymentsDisabled: this.paymentsDisabled
                });
            }
            
            return canConnect;
        },

        canPay() {
            // KYC check disabled - all users can purchase
            // KYC code kept intact but not enforced
            return (this.tokenQuantity >= this.minTokenQuantity) && this.selectedNetwork && this.walletConnectEnabled && this.isWalletConnected && !this.paymentsDisabled;
        },

        getConnectButtonTooltip() {
            if (this.isConnecting) return 'Connecting...';
            // Removed token quantity check - users can connect wallet first, then enter amount
            if (!this.selectedNetwork) return 'Please select a payment network (ERC20, BEP20, or TRC20)';
            if (!this.walletConnectEnabled) return 'WalletConnect is disabled. Check WALLETCONNECT_ENABLED in .env';
            return 'Click to connect your crypto wallet';
        },

        getPayButtonTooltip() {
            if (this.isProcessingPayment) return 'Processing payment...';
            if (!this.isWalletConnected) return 'Please connect your wallet first';
            if (this.tokenQuantity < this.minTokenQuantity) return 'Minimum purchase is ' + this.minTokenQuantity + ' tokens';
            if (!this.selectedNetwork) return 'Please select a payment network';
            // KYC check disabled - all users can purchase
            const amount = this.getUsdtAmountForPayment();
            return `Click to send ${amount} USDT from your connected wallet`;
        },

        getUsdtAmountForPayment() {
            // Calculate USDT amount for display
            const tokenQty = parseFloat(this.tokenQuantity) || 0;
            const tokenPrice = this.rates.tokenUsd;
            const usdtPrice = this.usdtUsd || 1;
            const usdtAmount = (tokenQty * tokenPrice) / usdtPrice;
            return usdtAmount.toFixed(2);
        },

        getWalletAddress() {
            // Get receiving wallet address based on selected network
            const wallets = {
                'ERC20': '{{ config("crypto.wallets.ERC20", "") }}',
                'BEP20': '{{ config("crypto.wallets.BEP20", config("crypto.wallets.ERC20", "")) }}',
                'TRC20': '{{ config("crypto.wallets.TRC20", "") }}',
                'BTC': '{{ config("crypto.wallets.BTC", "") }}'
            };
            return wallets[this.selectedNetwork] || '';
        },

        getContractAddress() {
            // Get USDT contract address based on network
            const contracts = {
                'ERC20': '{{ config("crypto.contracts.usdt_erc20", "") }}',
                'BEP20': '{{ config("crypto.contracts.usdt_bep20", "") }}',
                'TRC20': '{{ config("crypto.contracts.usdt_trc20", "") }}'
            };
            return contracts[this.selectedNetwork] || '';
        },

        getChainId() {
            // Get chain ID for WalletConnect
            const chainIds = {
                'ERC20': '0x1',      // Ethereum Mainnet
                'BEP20': '0x38',     // BNB Chain
                'TRC20': null        // Tron not supported via WalletConnect (different protocol)
            };
            return chainIds[this.selectedNetwork];
        },
        
        getQrCode() {
            // Generate QR code URL for the selected network
            const network = this.selectedNetwork || 'ERC20';
            const baseUrl = '{{ url("/") }}';
            return `${baseUrl}/qr-code/${network}`;
        },
        
        async copyAddress() {
            const address = this.getWalletAddress();
            if (address) {
                try {
                    await navigator.clipboard.writeText(address);
                    this.showToast('Address copied to clipboard!', 'success');
                } catch (err) {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = address;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    this.showToast('Address copied to clipboard!', 'success');
                }
            } else {
                this.showToast('No address available to copy', 'error');
            }
        },

        async connectWallet() {
            // Allow wallet connection without token quantity requirement
            // Users can connect wallet first, then enter the amount
            if (!this.canConnectWallet()) {
                if (!this.selectedNetwork) {
                    this.showToast('Please select a payment network first', 'warning');
                } else if (!this.walletConnectEnabled) {
                    this.showToast('WalletConnect is currently disabled. Please contact support.', 'error');
                }
                return;
            }
            
            // Token quantity check is only needed when making payment, not when connecting wallet
            try {
                this.isConnecting = true;
                // 1) Try MetaMask/injected provider first for desktop browsers
                if (window.ethereum && typeof window.ethereum.request === 'function') {
                    try {
                        const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
                        if (accounts && accounts.length) {
                            this.isWalletConnected = true;
                            this.connectedAddress = accounts[0];
                            this.walletProvider = window.ethereum; // Store provider for transactions
                            await this.saveWalletAddress(this.connectedAddress);
                            this.showToast('MetaMask connected successfully!', 'success');
                            this.isConnecting = false;
                            return;
                        }
                    } catch (mmError) {
                        // If user rejects, stop here; otherwise continue to WalletConnect fallback
                        const message = String(mmError && mmError.message || '').toLowerCase();
                        if (message.includes('user rejected')) {
                            this.showToast('Wallet connection was cancelled in MetaMask.', 'warning');
                            this.isConnecting = false;
                            return;
                        }
                    }
                }

                // Check if WalletConnect is loaded
                if (!window.walletConnectLoaded || !window.walletConnectModal) {
                    // Try to initialize one more time
                    window.initializeWalletConnect();
                    
                    if (!window.walletConnectLoaded || !window.walletConnectModal) {
                        // WalletConnect not available - this is fine, MetaMask works directly
                        // Don't show error, just continue with MetaMask connection
                        // The user can still connect via MetaMask directly
                        this.isConnecting = false;
                        // Don't return - let the code continue to try MetaMask connection
                        // The WalletConnect modal code below will be skipped if not available
                    }
                }

                // Only try WalletConnect modal if it's available
                if (window.walletConnectLoaded && window.walletConnectModal) {
                    try {
                        // Open WalletConnect modal
                        const session = await window.walletConnectModal.open();
                    
                        if (session && session.accounts && session.accounts.length > 0) {
                            this.isWalletConnected = true;
                            this.connectedAddress = session.accounts[0];
                            
                            // For WalletConnect, use window.ethereum if available (set by WalletConnect)
                            // Otherwise, we'll use the provider from the modal instance
                            if (window.ethereum) {
                                this.walletProvider = window.ethereum;
                            } else if (window.walletConnectModal && window.walletConnectModal.getProvider) {
                                this.walletProvider = window.walletConnectModal.getProvider();
                            } else if (session.provider) {
                                this.walletProvider = session.provider;
                            }
                            
                            // Save wallet address to user profile
                            await this.saveWalletAddress(this.connectedAddress);
                            
                            // Show success message
                            this.showToast('Wallet connected successfully!', 'success');
                            this.isConnecting = false;
                            return;
                        } else {
                            this.showToast('No wallet was connected. Please try again.', 'warning');
                            this.isConnecting = false;
                            return;
                        }
                    } catch (wcError) {
                        // WalletConnect failed, but that's okay - continue with MetaMask
                        console.log('WalletConnect modal failed, trying MetaMask directly')
                    }
                }
                
                // If WalletConnect is not available or failed, try MetaMask directly as final fallback
                // This ensures users can always connect via MetaMask even if WalletConnect fails
                if (!this.isWalletConnected && window.ethereum && typeof window.ethereum.request === 'function') {
                    try {
                        const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
                        if (accounts && accounts.length) {
                            this.isWalletConnected = true;
                            this.connectedAddress = accounts[0];
                            this.walletProvider = window.ethereum;
                            await this.saveWalletAddress(this.connectedAddress);
                            this.showToast('MetaMask connected successfully!', 'success');
                            this.isConnecting = false;
                            return;
                        }
                    } catch (mmError) {
                        // User rejected or error - show appropriate message
                        if (mmError.code === 4001) {
                            this.showToast('Wallet connection was cancelled.', 'warning');
                        } else {
                            this.showToast('Failed to connect MetaMask. Please check your wallet and try again.', 'error');
                        }
                    }
                } else if (!this.isWalletConnected) {
                    // No wallet provider available at all
                    this.showToast('No wallet detected. Please install MetaMask or another compatible wallet.', 'error');
                }
            } catch (error) {
                console.error('Wallet connection failed:', error)
                if (error.message && error.message.includes('User rejected')) {
                    this.showToast('Wallet connection was cancelled.', 'warning');
                } else {
                    this.showToast('Failed to connect wallet. Check your wallet app and try again.', 'error');
                }
            } finally {
                this.isConnecting = false;
            }
        },

        useManualAddress() {
            if (this.paymentsDisabled) {
                this.showToast('Payments are disabled.', 'warning');
                return;
            }
            if (!this.manualWalletAddress || this.manualWalletAddress.trim() === '') {
                this.showToast('Please enter a valid wallet address', 'warning');
                return;
            }
            
            // Basic validation for wallet address format
            const address = this.manualWalletAddress.trim();
            if (address.length < 20 || (!address.startsWith('0x') && !address.startsWith('T') && !address.startsWith('1') && !address.startsWith('3') && !address.startsWith('bc1'))) {
                this.showToast('Enter a valid wallet address (0x..., T..., 1/3/bc1...)', 'warning');
                return;
            }
            
            this.isWalletConnected = true;
            this.connectedAddress = address;
            
            // Save wallet address to user profile
            this.saveWalletAddress(address);
            
            this.showToast('Wallet address saved successfully!', 'success');
        },

        async saveWalletAddress(address) {
            if (this.paymentsDisabled) return;
            try {
                const response = await fetch('/api/save-wallet-address', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ wallet_address: address })
                });

                if (!response.ok) {
                    throw new Error('Failed to save wallet address');
                }
            } catch (error) {
                console.error('Failed to save wallet address:', error)
            }
        },

        async executePayment() {
            // KYC check disabled - all users can purchase
            // KYC code kept intact but not enforced

            if (!this.canPay() || !this.isWalletConnected) {
                this.showToast('Please connect your wallet first', 'warning');
                return;
            }

            // TRC20 (Tron) is not supported via WalletConnect - show manual instructions
            if (this.selectedNetwork === 'TRC20') {
                this.showToast('TRC20 payments require manual transfer. Please use the manual payment method.', 'info');
                return;
            }

            if (!this.walletProvider) {
                this.showToast('Wallet provider not available. Please reconnect your wallet.', 'error');
                return;
            }

            // Calculate payment amounts
            const tokenQty = parseFloat(this.tokenQuantity) || 0;
            const tokenPrice = this.rates.tokenUsd;
            const usdtPrice = this.usdtUsd || 1;
            const usdValue = tokenQty * tokenPrice;
            const usdtAmount = usdValue / usdtPrice;
            const usdtAmountFormatted = usdtAmount.toFixed(6);
            const pkrAmount = tokenQty * (this.rates.tokenPkr || (this.rates.tokenUsd * this.rates.usdToPkr));
            const tokenPricePkr = this.rates.tokenPkr || (this.rates.tokenUsd * this.rates.usdToPkr);
            
            const toAddress = this.getWalletAddress();
            const contractAddress = this.getContractAddress();

            if (!toAddress || !contractAddress) {
                this.showToast('Invalid wallet or contract address configuration', 'error');
                return;
            }

            // Store payment confirmation data
            this.paymentConfirmData = {
                tokens: this.formatNumber(this.tokenQuantity),
                tokenPrice: tokenPricePkr,
                usdtAmount: usdtAmountFormatted,
                pkrAmount: pkrAmount,
                network: this.selectedNetwork,
                toAddress: toAddress
            };

            // Show custom payment confirmation modal
            this.paymentConfirmationModal = true;
        },

        cancelPaymentConfirmation() {
            this.paymentConfirmationModal = false;
            this.isProcessingPayment = false;
            this.paymentStatusModal = false;
        },

        async proceedWithPayment() {
            // Close confirmation modal
            this.paymentConfirmationModal = false;
            
            // Proceed with the actual payment execution
            await this.executePaymentTransaction();
        },

        async executePaymentTransaction() {
            // KYC check disabled - all users can purchase
            // KYC code kept intact but not enforced

            if (!this.canPay() || !this.isWalletConnected) {
                this.showToast('Please connect your wallet first', 'warning');
                return;
            }

            // TRC20 (Tron) is not supported via WalletConnect - show manual instructions
            if (this.selectedNetwork === 'TRC20') {
                this.showToast('TRC20 payments require manual transfer. Please use the manual payment method.', 'info');
                return;
            }

            if (!this.walletProvider) {
                this.showToast('Wallet provider not available. Please reconnect your wallet.', 'error');
                return;
            }

            try {
                this.isProcessingPayment = true;
                this.paymentStatus = 'pending';
                this.paymentStatusModal = true;
                this.paymentError = '';

                const toAddress = this.getWalletAddress();
                const contractAddress = this.getContractAddress();
                const chainId = this.getChainId();

                if (!toAddress || !contractAddress) {
                    throw new Error('Invalid wallet or contract address configuration');
                }

                // Switch to correct network if needed (must be done first)
                if (chainId) {
                    try {
                        await this.walletProvider.request({
                            method: 'wallet_switchEthereumChain',
                            params: [{ chainId: chainId }],
                        });
                    } catch (switchError) {
                        // If network doesn't exist, add it
                        if (switchError.code === 4902) {
                            const networkParams = this.getNetworkParams(chainId);
                            if (networkParams) {
                                await this.walletProvider.request({
                                    method: 'wallet_addEthereumChain',
                                    params: [networkParams],
                                });
                            }
                        } else {
                            throw switchError;
                        }
                    }
                }
                
                // CRITICAL: Get actual token decimals from the contract FIRST
                // BEP20 USDT uses 18 decimals, ERC20 USDT uses 6 decimals
                let tokenDecimals = 6; // Default
                try {
                    const decimalsData = '0x313ce567'; // decimals() function signature
                    const decimalsResult = await this.walletProvider.request({
                        method: 'eth_call',
                        params: [{
                            to: contractAddress,
                            data: decimalsData
                        }, 'latest'],
                    });
                    tokenDecimals = parseInt(decimalsResult, 16);
                    console.log('✅ Token decimals detected from contract:', tokenDecimals, 'for', this.selectedNetwork)
                } catch (decimalsError) {
                    console.warn('Could not read token decimals, using network default:', decimalsError.message)
                    // Use network-specific defaults
                    if (this.selectedNetwork === 'BEP20') {
                        tokenDecimals = 18; // BEP20 USDT uses 18 decimals
                    } else if (this.selectedNetwork === 'ERC20') {
                        tokenDecimals = 6; // ERC20 USDT uses 6 decimals
                    }
                    console.log('Using default decimals for', this.selectedNetwork + ':', tokenDecimals)
                }
                
                // Calculate USDT amount fresh to ensure accuracy
                const tokenQty = parseFloat(this.tokenQuantity) || 0;
                const tokenPrice = parseFloat(this.rates.tokenUsd) || 0; // Price per token in USD
                const usdtPrice = parseFloat(this.usdtUsd) || 1; // USDT price in USD
                
                // Debug: Log initial values
                console.log('🔍 Payment Calculation - Initial Values:', {
                tokenQuantity: this.tokenQuantity,
                tokenQty: tokenQty,
                tokenPrice: tokenPrice,
                usdtPrice: usdtPrice,
                tokenDecimals: tokenDecimals,
                network: this.selectedNetwork,
                rates: this.rates,
                usdtUsd: this.usdtUsd
                // })
                
                // Validate inputs
                if (tokenQty <= 0 || tokenPrice <= 0) {
                    throw new Error('Invalid token quantity or price. Please check your input.');
                }
                
                // Calculate USD value: tokens * price per token
                const usdValue = tokenQty * tokenPrice;
                
                // Calculate USDT amount: USD value / USDT price
                const usdtAmount = usdValue / usdtPrice;
                
                // Debug: Log calculation
                console.log('🔍 Payment Calculation - Step 1:', {
                    usdValue: usdValue,
                    usdtAmount: usdtAmount,
                    // calculation: `${tokenQty} tokens × $${tokenPrice} = $${usdValue} ÷ $${usdtPrice} = ${usdtAmount} USDT`
                })
                
                // Ensure we have a valid amount
                if (isNaN(usdtAmount) || usdtAmount <= 0) {
                    console.error('Invalid USDT amount calculation:', {
                        // tokenQty,
                        // tokenPrice,
                        // usdtPrice,
                        // usdValue,
                        // usdtAmount,
                        rates: this.rates,
                        usdtUsd: this.usdtUsd
                    })
                    throw new Error('Invalid USDT amount calculated. Please check your token quantity.');
                }
                
                // Convert to smallest unit using the correct decimals (detected above)
                // Use precise calculation: multiply by 10^decimals and round to avoid floating point issues
                const decimalsMultiplier = Math.pow(10, tokenDecimals);
                const usdtAmountInSmallestUnit = Math.round(usdtAmount * decimalsMultiplier);
                
                console.log('🔍 CRITICAL: Amount conversion with correct decimals:', {
                    usdtAmount: usdtAmount,
                    tokenDecimals: tokenDecimals,
                    decimalsMultiplier: decimalsMultiplier,
                    // calculation: `${usdtAmount} × ${decimalsMultiplier}`,
                    result: usdtAmountInSmallestUnit,
                    // verification: `${usdtAmountInSmallestUnit} / ${decimalsMultiplier} = ${(usdtAmountInSmallestUnit / decimalsMultiplier).toFixed(6)} USDT`
                });
                
                // Debug: Log conversion to smallest unit
                console.log('🔍 Payment Calculation - Step 2 (Convert to smallest unit):', {
                    usdtAmount: usdtAmount,
                    tokenDecimals: tokenDecimals,
                    network: this.selectedNetwork,
                    decimalsMultiplier: decimalsMultiplier,
                    multiplication: `${usdtAmount} × ${decimalsMultiplier}`,
                    beforeRound: usdtAmount * decimalsMultiplier,
                    usdtAmountInSmallestUnit: usdtAmountInSmallestUnit,
                    verification: `${usdtAmountInSmallestUnit} / ${decimalsMultiplier} = ${usdtAmountInSmallestUnit / decimalsMultiplier} USDT`
                });
                
                // Ensure we have a valid integer amount (at least 1 smallest unit)
                if (usdtAmountInSmallestUnit <= 0 || usdtAmountInSmallestUnit < 1) {
                    console.error('❌ Amount too small:', {
                        // usdtAmount,
                        // usdtAmountInSmallestUnit,
                        // tokenQty,
                        // tokenPrice
                    })
                    throw new Error('Amount too small. Minimum purchase is ' + this.minTokenQuantity + ' tokens.');
                }
                
                // Convert to BigInt directly (don't convert to string and back)
                const usdtAmountBigInt = BigInt(usdtAmountInSmallestUnit);
                const usdtAmountFormatted = usdtAmount.toFixed(6);
                
                // Debug: Log BigInt conversion
                console.log('🔍 Payment Calculation - Step 3 (BigInt conversion):', {
                    usdtAmountInSmallestUnit: usdtAmountInSmallestUnit,
                    usdtAmountBigInt: usdtAmountBigInt.toString(),
                    usdtAmountBigIntType: typeof usdtAmountBigInt,
                    usdtAmountFormatted: usdtAmountFormatted
                });
                
                // Verify the calculation is correct
                const verification = {
                    tokenQty,
                    tokenPrice,
                    usdtPrice,
                    tokenDecimals,
                    decimalsMultiplier,
                    usdValue: usdValue.toFixed(6),
                    usdtAmount: usdtAmount.toFixed(6),
                    usdtAmountFormatted,
                    usdtAmountInSmallestUnit,
                    usdtAmountBigInt: usdtAmountBigInt.toString(),
                    verification: (Number(usdtAmountBigInt) / decimalsMultiplier).toFixed(6) + ' USDT'
                };
                
                // Debug logging
                console.log('Payment Calculation:', verification)
                
                // Double-check: the amount should match what we calculated
                if (Math.abs(parseFloat(verification.verification) - usdtAmount) > 0.000001) {
                    console.warn('Amount verification mismatch:', verification)
                }

                // Addresses and network already set up above

                // Validate addresses are properly formatted
                if (!toAddress || !toAddress.match(/^0x[a-fA-F0-9]{40}$/)) {
                    throw new Error('Invalid recipient wallet address format');
                }
                if (!contractAddress || !contractAddress.match(/^0x[a-fA-F0-9]{40}$/)) {
                    throw new Error('Invalid USDT contract address format');
                }
                
                // ERC20/BEP20 transfer function signature: transfer(address,uint256)
                // Function selector: transfer(address,uint256) = 0xa9059cbb
                // IMPORTANT: Remove '0x' prefix - data field should only contain hex characters without prefix
                const transferFunctionSignature = 'a9059cbb'; // 4 bytes = 8 hex characters (without 0x)
                
                // Remove '0x' prefix from address and ensure lowercase for encoding
                // Addresses in data field should be lowercase (EIP-55 checksum is for display only)
                const cleanToAddress = toAddress.startsWith('0x') ? toAddress.slice(2) : toAddress;
                const toAddressLower = cleanToAddress.toLowerCase();
                
                // Validate address length (should be 40 hex characters = 20 bytes)
                if (toAddressLower.length !== 40) {
                    throw new Error('Invalid recipient address length. Expected 40 hex characters.');
                }
                
                // Pad address to 64 hex characters (32 bytes) - addresses are 20 bytes, so pad with leading zeros
                const toAddressPadded = toAddressLower.padStart(64, '0');
                
                // Convert amount to hex and pad to 64 characters (32 bytes)
                // USDT uses 6 decimals, so we multiply by 1e6 to get smallest unit
                // Use the BigInt directly (already calculated above)
                const amountHex = usdtAmountBigInt.toString(16).padStart(64, '0');
                
                // Debug: Log hex encoding
                console.log('🔍 Payment Calculation - Step 4 (Hex encoding):', {
                    usdtAmountBigInt: usdtAmountBigInt.toString(),
                    amountHex: amountHex,
                    amountHexLength: amountHex.length,
                    decodedBack: parseInt(amountHex, 16),
                    decodedUsdt: (parseInt(amountHex, 16) / 1000000).toFixed(6) + ' USDT',
                    matches: parseInt(amountHex, 16) === usdtAmountInSmallestUnit
                });
                
                // Construct the data field: function selector (4 bytes = 8 hex chars) + address (32 bytes = 64 hex chars) + amount (32 bytes = 64 hex chars)
                // Total: 8 + 64 + 64 = 136 hex characters (without 0x prefix)
                const data = transferFunctionSignature + toAddressPadded + amountHex;
                
                // Debug: Verify the amount in hex
                console.log('Transaction Data Construction:', {
                    transferFunctionSignature: transferFunctionSignature,
                    transferFunctionSignatureLength: transferFunctionSignature.length,
                    toAddress: toAddress,
                    toAddressPadded: toAddressPadded,
                    toAddressPaddedLength: toAddressPadded.length,
                    usdtAmount: usdtAmountFormatted,
                    usdtAmountInSmallestUnit: usdtAmountInSmallestUnit,
                    usdtAmountBigInt: usdtAmountBigInt.toString(),
                    amountHex: amountHex,
                    amountHexLength: amountHex.length,
                    // decodedAmount: (parseInt(amountHex, 16) / 1000000).toFixed(6) + ' USDT',
                    dataLength: data.length,
                    // expectedDataLength: 136, // 8 (function) + 64 (address) + 64 (amount) = 136 hex characters
                    // dataPreview: data.substring(0, 20) + '...' + data.substring(data.length - 20)
                });

                // Get current gas price
                let gasPrice = null;
                try {
                    const gasPriceHex = await this.walletProvider.request({
                        method: 'eth_gasPrice',
                        params: []
,
                    });
                    gasPrice = gasPriceHex;
                } catch (gasPriceError) {
                    console.warn('Gas price fetch failed, MetaMask will use default:', gasPriceError)
                }

                // Token decimals already detected above - just verify they match
                console.log('✅ Using token decimals:', tokenDecimals, 'for network:', this.selectedNetwork)
                
                // Try to simulate the transaction to help MetaMask understand it
                try {
                    const callResult = await this.walletProvider.request({
                        method: 'eth_call',
                        params: [{
                            from: this.connectedAddress,
                            to: contractAddress,
                            value: '0x0',
                            data: '0x' + data,
                        }, 'latest'],
                    });
                    console.log('✅ Transaction simulation successful:', callResult)
                    // Small delay to let MetaMask process and cache the transaction
                    await new Promise(resolve => setTimeout(resolve, 300));
                } catch (simError) {
                    // Simulation might fail if user doesn't have enough balance, but that's okay
                    // The important thing is that MetaMask now knows about the transaction
                    console.log('Transaction simulation note:', simError.message || 'OK')
                    // Still add a small delay to let MetaMask process
                    await new Promise(resolve => setTimeout(resolve, 300));
                }

                // Estimate gas limit
                let gasLimit = '0x186a0'; // Default 100000
                try {
                    const gasEstimate = await this.walletProvider.request({
                        method: 'eth_estimateGas',
                        params: [{
                            from: this.connectedAddress,
                            to: contractAddress,
                            value: '0x0', // Token transfers have 0 value
                            data: '0x' + data, // Add 0x prefix for estimation
                        }],
                    });
                    // Add 20% buffer to gas estimate
                    const estimatedGas = BigInt(gasEstimate);
                    const bufferedGas = estimatedGas * BigInt(120) / BigInt(100);
                    gasLimit = '0x' + bufferedGas.toString(16);
                } catch (gasError) {
                    console.warn('Gas estimation failed, using default:', gasError)
                    // Use a safe default for token transfers
                    gasLimit = '0x186a0'; // 100000
                }

                // Send transaction - MetaMask will show the exact amount
                // For ERC20/BEP20 token transfers:
                // - value must be '0x0' (we're sending tokens, not native currency)
                // - data must have '0x' prefix and contain the encoded transfer function call
                // - gas and gasPrice help MetaMask properly estimate and display the transaction
                // IMPORTANT: Ensure all parameters are properly formatted for MetaMask
                
                // Construct the complete transaction data with proper formatting
                const completeData = '0x' + data;
                
                // Verify the data is exactly 138 characters (2 for '0x' + 136 hex chars)
                if (completeData.length !== 138) {
,
                    console.error('❌ CRITICAL: Transaction data length is incorrect!', {
                        expected: 138,
                        actual: completeData.length,
                        // data: completeData.substring(0, 50) + '...'
                    });
                    throw new Error('Transaction data format error. Please refresh and try again.');
                }
                
                const txParams = {
                    from: this.connectedAddress, // Keep original format (MetaMask handles checksum)
                    to: contractAddress, // Keep original format (MetaMask handles checksum)
                    value: '0x0', // Always 0 for token transfers (tokens are sent via data field)
                    data: completeData, // Complete data with 0x prefix
                    gas: gasLimit,
                };
                
                // Add gas price if available (helps MetaMask estimate better)
                if (gasPrice) {
                    txParams.gasPrice = gasPrice;
                }
                
                // For BEP20, add maxFeePerGas and maxPriorityFeePerGas if using EIP-1559
                // But for now, gasPrice should work fine
                
                // For BEP20, ensure we're using the correct chain
                if (this.selectedNetwork === 'BEP20') {
                    // Verify we're on BNB Chain
                    try {
                        const currentChainId = await this.walletProvider.request({
                            method: 'eth_chainId',
                            params: []
,
                        });
                        if (currentChainId !== '0x38') {
                            console.warn('Warning: Not on BNB Chain. Current chain:', currentChainId)
                        }
                    } catch (chainError) {
                        console.warn('Could not verify chain ID:', chainError)
                    }
                }
                
                // Log the final transaction parameters for debugging
                console.log('📤 Final Transaction Parameters:', {
                    from: txParams.from,
                    to: txParams.to,
                    value: txParams.value,
                    // data: txParams.data.substring(0, 20) + '...' + txParams.data.substring(txParams.data.length - 20),
                    dataLength: txParams.data.length,
                    gas: txParams.gas,
                    gasPrice: txParams.gasPrice || 'auto',
                    expectedUSDT: usdtAmountFormatted + ' USDT',
                    amountInSmallestUnit: usdtAmountInSmallestUnit,
                    amountHex: amountHex
                });
                
                // Verify transaction data before sending
                // Data should be: function selector (8 hex chars) + address (64 hex chars) + amount (64 hex chars) = 136 hex characters
                // This equals 68 bytes total (136 / 2 = 68 bytes)
                const expectedLength = 136; // 8 + 64 + 64 = 136 hex characters
                if (data.length !== expectedLength) {
                    console.error('Invalid transaction data length:', {
                        actual: data.length,
                        expected: expectedLength,
                        functionSig: transferFunctionSignature,
                        functionSigLength: transferFunctionSignature.length,
                        addressPadded: toAddressPadded,
                        addressPaddedLength: toAddressPadded.length,
                        amountHex: amountHex,
                        amountHexLength: amountHex.length,
                        // data: data.substring(0, 50) + '...' // Show first 50 chars for debugging
                    });
                    throw new Error(`Invalid transaction data format. Expected ${expectedLength} hex characters, got ${data.length}. Please try again.`);
                }
                
                // Additional validation: verify the hex string is valid
                if (!/^[0-9a-f]+$/.test(data)) {
                    throw new Error('Invalid transaction data: contains non-hexadecimal characters');
                }
                
                // Verify the amount can be decoded correctly using the correct decimals
                const decodedAmount = parseInt(amountHex, 16);
                const decodedUsdt = decodedAmount / decimalsMultiplier;
                
                // Critical verification: ensure the encoded amount matches what we calculated
                if (Math.abs(decodedUsdt - usdtAmount) > 0.000001) {
                    console.error('CRITICAL: Amount encoding mismatch!', {
                        original: usdtAmount,
                        decoded: decodedUsdt,
                        amountHex: amountHex,
                        usdtAmountBigInt: usdtAmountBigInt.toString(),
                        usdtAmountInSmallestUnit: usdtAmountInSmallestUnit,
                        // amountHexParsed: parseInt(amountHex, 16)
                    });
                    throw new Error('Amount encoding error. The transaction amount does not match. Please refresh and try again.');
                }
                
                // Final verification: Ensure the amount matches what was shown in confirmation modal
                const confirmationAmount = this.paymentConfirmData?.usdtAmount || usdtAmountFormatted;
                const confirmationAmountFloat = parseFloat(confirmationAmount);
                const calculatedAmountFloat = parseFloat(usdtAmountFormatted);
                
                if (Math.abs(confirmationAmountFloat - calculatedAmountFloat) > 0.000001) {
                    console.warn('⚠️ Amount mismatch between confirmation and calculation:', {
                        confirmationAmount: confirmationAmount,
                        calculatedAmount: usdtAmountFormatted,
                        difference: Math.abs(confirmationAmountFloat - calculatedAmountFloat)
                    });
                }
                
                // Final verification log before sending
                console.log('✅ Transaction Verified - Sending to MetaMask:', {
                    from: txParams.from,
                    to: txParams.to,
                    contractAddress: contractAddress,
                    value: txParams.value,
                    gas: txParams.gas,
                    gasPrice: txParams.gasPrice || 'auto',
                    dataLength: txParams.data.length,
                    // dataPreview: txParams.data.substring(0, 20) + '...',
                    usdtAmount: usdtAmountFormatted,
                    confirmationAmount: confirmationAmount,
                    usdtAmountInSmallestUnit: usdtAmountInSmallestUnit,
                    usdtAmountBigInt: usdtAmountBigInt.toString(),
                    decodedAmount: decodedUsdt.toFixed(6) + ' USDT',
                    amountHex: amountHex,
                    network: this.selectedNetwork,
                    tokenQuantity: this.tokenQuantity,
                    tokenPrice: this.rates.tokenUsd,
                    // verification: `Sending ${usdtAmountFormatted} USDT (${usdtAmountInSmallestUnit} smallest units) to ${toAddress}`
                });
                
                // CRITICAL: Verify the amount is correctly positioned in the data field
                // The amount should be in the last 64 hex characters of the data (after removing '0x')
                const dataWithoutPrefix = completeData.slice(2); // Remove '0x'
                const amountInData = dataWithoutPrefix.slice(-64); // Last 64 hex chars
                const decodedAmountFromData = parseInt(amountInData, 16);
                const decodedUsdtFromData = decodedAmountFromData / decimalsMultiplier;
                
                if (amountInData !== amountHex) {
                    console.error('❌ CRITICAL: Amount hex mismatch in data field!', {
                        expected: amountHex,
                        actual: amountInData,
                        decodedExpected: decodedUsdt.toFixed(6) + ' USDT',
                        decodedActual: decodedUsdtFromData.toFixed(6) + ' USDT'
                    });
                    throw new Error('Transaction data encoding error. Amount mismatch detected.');
                }
                
                // CRITICAL: Log the exact transaction data that will be sent
                console.log('🔐 EXACT TRANSACTION DATA BEING SENT TO METAMASK:', {
                    method: 'eth_sendTransaction',
                    params: [{
                        // from: txParams.from,
                        to: txParams.to,
                        value: txParams.value,
                        data: txParams.data,
                        gas: txParams.gas,
                        gasPrice: txParams.gasPrice || 'auto'
,
                    }],
                    // decodedAmount: `${decodedUsdt.toFixed(6)} USDT`,
                    // expectedAmount: `${usdtAmountFormatted} USDT`,
                    amountInSmallestUnit: usdtAmountInSmallestUnit,
                    amountHex: amountHex,
                    amountInDataField: amountInData,
                    // verification: `Amount ${decodedUsdtFromData.toFixed(6)} USDT correctly encoded in transaction data`
                });
                
                // Show user-friendly message about what to expect in MetaMask
                console.log('📋 IMPORTANT: When MetaMask opens, it should show:', {
                    // amount: `${usdtAmountFormatted} USDT`,
                    recipient: toAddress,
                    contract: contractAddress,
                    network: this.selectedNetwork,
                    // note: 'If MetaMask shows a different amount, please check the transaction details carefully'
                })
                
                const txHash = await this.walletProvider.request({
                    method: 'eth_sendTransaction',
                    params: [txParams],
                });

                this.lastTxHash = txHash;
                this.paymentStatus = 'success';
                
                // Submit transaction hash to backend automatically
                const submitResult = await this.submitTxHashAfterPayment(txHash);
                
                if (submitResult && submitResult.success) {
                    this.showToast(`Payment of ${usdtAmountFormatted} USDT submitted successfully! Transaction hash saved to database.`, 'success');
                    console.log('✅ Payment complete - Transaction hash saved:', {
                        tx_hash: txHash,
                        payment_id: submitResult.data?.id,
                        status: 'pending'
,
                    })
                } else {
                    this.showToast(`Payment successful! Transaction hash: ${txHash.substring(0, 10)}... (Saving to database...)`, 'success');
                    console.warn('Transaction hash submission had issues, but payment was successful:', submitResult?.error)
                    // Try to save again after a delay
                    setTimeout(async () => {
                        const retryResult = await this.submitTxHashAfterPayment(txHash);
                        if (retryResult && retryResult.success) {
                            this.showToast('Transaction hash saved successfully!', 'success');
                        }
                    }, 2000);
                }
                
                // Auto-close modal after 5 seconds if successful
                setTimeout(() => {
                    if (this.paymentStatus === 'success') {
                        this.paymentStatusModal = false;
                    }
                }, 5000);
                
            } catch (error) {
                console.error('Payment execution failed:', error)
                this.paymentStatus = 'error';
                this.paymentError = error.message || 'Payment failed. Please try again.';
                
                if (error.message && (error.message.includes('User rejected') || error.message.includes('user rejected'))) {
                    this.paymentError = 'Transaction was cancelled in your wallet.';
                } else if (error.message && error.message.includes('insufficient funds')) {
                    this.paymentError = 'Insufficient USDT balance. Please ensure you have enough USDT and gas fees.';
                }
                
                this.showToast(this.paymentError, 'error');
            } finally {
                this.isProcessingPayment = false;
            }
        },

        getNetworkParams(chainId) {
            const networks = {
                '0x1': {
                    chainId: '0x1',
                    chainName: 'Ethereum Mainnet',
                    nativeCurrency: { name: 'ETH', symbol: 'ETH', decimals: 18 },
                    rpcUrls: ['https://mainnet.infura.io/v3/'],
                    blockExplorerUrls: ['https://etherscan.io']
                },
                '0x38': {
                    chainId: '0x38',
                    chainName: 'BNB Smart Chain',
                    nativeCurrency: { name: 'BNB', symbol: 'BNB', decimals: 18 },
                    rpcUrls: ['https://bsc-dataseed.binance.org/'],
                    blockExplorerUrls: ['https://bscscan.com']
                }
            };
            return networks[chainId] || null;
        },

        async submitTxHashAfterPayment(txHash) {
            try {
                // Extract numeric values from formatted strings
                const extractNumeric = (str) => {
                    if (typeof str !== 'string') return String(str || '0');
                    return str.replace(/[^0-9.]/g, '') || '0';
                };
                
                const usdValue = extractNumeric(this.usdAmount);
                const pkrValue = extractNumeric(this.pkrAmountLive);
                const tokenQty = parseFloat(this.tokenQuantity) || 0;
                
                console.log('💾 Saving transaction hash to database:', {
                    tx_hash: txHash,
                    network: this.selectedNetwork,
                    token_amount: tokenQty,
                    usd_amount: usdValue,
                    pkr_amount: pkrValue
                })
                
                const response = await fetch('/api/submit-tx-hash', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        tx_hash: txHash,
                        network: this.selectedNetwork,
                        token_amount: tokenQty, // Use parseFloat to preserve decimals
                        usd_amount: usdValue,
                        pkr_amount: pkrValue
,
                    })
                });
                
                const responseData = await response.json();
                
                if (!response.ok) {
                    console.error('❌ Failed to submit transaction hash:', responseData)
                    // KYC check disabled - don't redirect to KYC page
                    // Just show error message
                    throw new Error(responseData.message || 'Failed to save transaction hash');
                }
                
                console.log('✅ Transaction hash saved successfully:', {
                    payment_id: responseData.id,
                    tx_hash: txHash,
                    status: 'pending'
,
                })
                
                return { success: true, data: responseData };
            } catch (error) {
                console.error('❌ Error submitting transaction hash:', error)
                // Don't throw - payment was successful, just hash saving failed
                return { success: false, error: error.message };
            }
        },

        async checkPaymentStatus() {
            try {
                const response = await fetch('/api/check-payment-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        tx_hash: this.txHash,
                        network: this.selectedNetwork,
                        amount: this.tokenQuantity
,
                    })
                });

                const data = await response.json();
                
                if (data.payment_found) {
                    this.showToast(`Status: ${data.status}`, data.status === 'approved' ? 'success' : 'info');
                } else {
                    this.showToast('Payment not found yet. Try again later.', 'info');
                }
            } catch (error) {
                console.error('Failed to check payment status:', error)
                this.showToast('Failed to check payment status. Please try again.', 'error');
            }
        },

        async submitTxHash() {
            // KYC check disabled - all users can submit payments
            // KYC code kept intact but not enforced
            if (!this.txHash || !this.selectedNetwork) {
                this.showToast('Enter TX hash and select network', 'warning');
                return;
            }
            try {
                this.isSubmitting = true;
                
                // Extract numeric values from formatted strings
                const extractNumeric = (str) => {
                    if (typeof str !== 'string') return String(str || '0');
                    return str.replace(/[^0-9.]/g, '') || '0';
                };
                
                const usdValue = extractNumeric(this.usdAmount);
                const pkrValue = extractNumeric(this.pkrAmountLive);
                
                const response = await fetch('/api/submit-tx-hash', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        tx_hash: this.txHash.trim(),
                        network: this.selectedNetwork,
                        token_amount: parseInt(this.tokenQuantity) || 0,
                        usd_amount: usdValue,
                        pkr_amount: pkrValue
,
                    })
                });
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Submit failed' }));
                    // KYC check disabled - don't redirect to KYC page
                    throw new Error(errorData.message || 'Submit failed');
                }
                this.showToast('Payment proof submitted! We will review shortly.', 'success');
                this.submitAlert = { visible: true, message: 'Submitted successfully. You can close this dialog.', type: 'success' };
                this.txHash = '';
            } catch (e) {
                console.error(e)
                this.showToast('Failed to submit. Please check your hash and try again.', 'error');
                this.submitAlert = { visible: true, message: 'Submission failed. Please verify your hash and try again.', type: 'error' };
            } finally {
                this.isSubmitting = false;
            }
        },

        async checkAutoDetection() {
            if (this.paymentsDisabled) return;
            try {
                const response = await fetch('/api/check-auto-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        network: this.selectedNetwork,
                        expected_usd: this.getUsdNumeric()
,
                    })
                });
                if (!response.ok) return;
                const data = await response.json();
                if (data.detected) {
                    this.showToast('Payment detected on-chain. Submit TX hash to speed approval.', 'success');
                }
            } catch (e) {
                // silent
            }
        },

        showToast(message, type = 'info') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.visible = true;
            setTimeout(() => { this.toast.visible = false; }, 3000);
        }
    }
}

// Define purchaseModalFlow (used by modal component) - ensure globally accessible
window.purchaseModalFlow = function purchaseModalFlow() {
    const baseFlow = purchaseFlow();
    return {
        ...baseFlow,
        purchaseModalOpen: false,
        handlePurchaseModalOpen(event) {
            this.purchaseModalOpen = true;
            // If event has detail with network/method, set it
            if (event.detail && event.detail.network) {
                this.selectedNetwork = event.detail.network;
            }
        }
    };
};
</script>
@endsection

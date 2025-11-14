@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">How to Buy RWAMP Tokens</h1>
            <p class="text-white/80 mt-2">Complete guide to setting up crypto wallets and purchasing RWAMP tokens.</p>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 py-10 space-y-10">
        <!-- Introduction -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-4">Getting Started</h2>
            <p class="text-gray-700 mb-4">
                To purchase RWAMP tokens, you'll need a cryptocurrency wallet and some USDT or BTC. 
                This guide will walk you through setting up popular wallets and making your first purchase.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="text-sm font-medium text-blue-800 mb-2">💡 Quick Start</div>
                <div class="text-sm text-blue-700">
                    If you're new to crypto, we recommend starting with <strong>MetaMask</strong> for USDT (ERC20/TRC20) 
                    or <strong>Trust Wallet</strong> for mobile users. Both are beginner-friendly and widely supported.
                </div>
            </div>
        </div>

        <!-- Wallet Setup Options -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- MetaMask -->
            <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                        <span class="text-2xl">🦊</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-montserrat font-bold">MetaMask</h3>
                        <p class="text-sm text-gray-600">Browser Extension & Mobile</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">1.</span>
                        <p class="text-sm text-gray-700">Visit <a href="https://metamask.io" target="_blank" class="text-primary hover:underline">metamask.io</a> and install the browser extension</p>
                    </div>
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">2.</span>
                        <p class="text-sm text-gray-700">Create a new wallet and securely store your seed phrase</p>
                    </div>
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">3.</span>
                        <p class="text-sm text-gray-700">Add USDT token to your wallet (both ERC20 and TRC20 supported)</p>
                    </div>
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">4.</span>
                        <p class="text-sm text-gray-700">Fund your wallet with USDT from an exchange</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="https://metamask.io" target="_blank" class="btn-primary text-sm">Get MetaMask</a>
                </div>
            </div>

            <!-- Trust Wallet -->
            <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <span class="text-2xl">🔵</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-montserrat font-bold">Trust Wallet</h3>
                        <p class="text-sm text-gray-600">Mobile App</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">1.</span>
                        <p class="text-sm text-gray-700">Download from <a href="https://trustwallet.com" target="_blank" class="text-primary hover:underline">trustwallet.com</a> or app stores</p>
                    </div>
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">2.</span>
                        <p class="text-sm text-gray-700">Create wallet and backup your recovery phrase</p>
                    </div>
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">3.</span>
                        <p class="text-sm text-gray-700">Enable USDT (Tron) and USDT (Ethereum) in settings</p>
                    </div>
                    <div class="flex items-start">
                        <span class="text-primary font-bold mr-2">4.</span>
                        <p class="text-sm text-gray-700">Buy USDT directly in the app or transfer from exchange</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="https://trustwallet.com" target="_blank" class="btn-primary text-sm">Get Trust Wallet</a>
                </div>
            </div>
        </div>

        <!-- Other Wallets -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="text-xl font-montserrat font-bold mb-4">Other Popular Wallets</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="text-center p-4 border rounded-lg">
                    <div class="text-2xl mb-2">🔷</div>
                    <h4 class="font-montserrat font-bold">Coinbase Wallet</h4>
                    <p class="text-sm text-gray-600">Beginner-friendly</p>
                </div>
                <div class="text-center p-4 border rounded-lg">
                    <div class="text-2xl mb-2">⚡</div>
                    <h4 class="font-montserrat font-bold">Exodus</h4>
                    <p class="text-sm text-gray-600">Multi-crypto support</p>
                </div>
                <div class="text-center p-4 border rounded-lg">
                    <div class="text-2xl mb-2">🔐</div>
                    <h4 class="font-montserrat font-bold">Ledger</h4>
                    <p class="text-sm text-gray-600">Hardware security</p>
                </div>
            </div>
        </div>

        <!-- Getting USDT -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="text-xl font-montserrat font-bold mb-4">How to Get USDT</h3>
            <div class="space-y-4">
                <div class="border-l-4 border-primary pl-4">
                    <h4 class="font-montserrat font-bold text-lg">Method 1: Crypto Exchange</h4>
                    <p class="text-gray-700 text-sm mt-1">
                        Buy USDT directly on exchanges like Binance, Coinbase, or Kraken using your credit card or bank transfer.
                    </p>
                </div>
                <div class="border-l-4 border-accent pl-4">
                    <h4 class="font-montserrat font-bold text-lg">Method 2: P2P Trading</h4>
                    <p class="text-gray-700 text-sm mt-1">
                        Use peer-to-peer platforms to buy USDT from other users with local payment methods.
                    </p>
                </div>
                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-montserrat font-bold text-lg">Method 3: Wallet Apps</h4>
                    <p class="text-gray-700 text-sm mt-1">
                        Many wallet apps like Trust Wallet allow you to buy crypto directly with your credit card.
                    </p>
                </div>
            </div>
        </div>

        <!-- Network Selection Guide -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="text-xl font-montserrat font-bold mb-4">Choosing the Right Network</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="text-center p-4 border-2 border-green-200 rounded-lg bg-green-50">
                    <div class="text-2xl mb-2">🟢</div>
                    <h4 class="font-montserrat font-bold text-green-800">TRC20 (Tron)</h4>
                    <p class="text-sm text-green-700 mt-1">Fast & Cheap</p>
                    <p class="text-xs text-green-600 mt-2">Best for: Small amounts, frequent transactions</p>
                </div>
                <div class="text-center p-4 border-2 border-blue-200 rounded-lg bg-blue-50">
                    <div class="text-2xl mb-2">🔵</div>
                    <h4 class="font-montserrat font-bold text-blue-800">ERC20 (Ethereum)</h4>
                    <p class="text-sm text-blue-700 mt-1">Widely Supported</p>
                    <p class="text-xs text-blue-600 mt-2">Best for: Large amounts, maximum compatibility</p>
                </div>
                <div class="text-center p-4 border-2 border-orange-200 rounded-lg bg-orange-50">
                    <div class="text-2xl mb-2">🟠</div>
                    <h4 class="font-montserrat font-bold text-orange-800">BTC (Bitcoin)</h4>
                    <p class="text-sm text-orange-700 mt-1">Original Crypto</p>
                    <p class="text-xs text-orange-600 mt-2">Best for: Long-term holders, Bitcoin users</p>
                </div>
            </div>
        </div>

        <!-- Purchase Process -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="text-xl font-montserrat font-bold mb-4">Making Your Purchase</h3>
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">1</div>
                    <div>
                        <h4 class="font-montserrat font-bold">Calculate Your Purchase</h4>
                        <p class="text-sm text-gray-700">Enter the number of RWAMP tokens you want to buy (minimum 100). The system will show you the exact USD and PKR amounts.</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">2</div>
                    <div>
                        <h4 class="font-montserrat font-bold">Choose Payment Method</h4>
                        <p class="text-sm text-gray-700">Select TRC20, ERC20, or BTC and copy the wallet address or scan the QR code with your wallet app.</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">3</div>
                    <div>
                        <h4 class="font-montserrat font-bold">Send Payment</h4>
                        <p class="text-sm text-gray-700">Send the exact amount of USDT or BTC to the provided address. Double-check the network and amount!</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">4</div>
                    <div>
                        <h4 class="font-montserrat font-bold">Submit Proof</h4>
                        <p class="text-sm text-gray-700">Enter your transaction hash and optionally upload a screenshot. Our team will manually verify and approve your purchase.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <h3 class="text-xl font-montserrat font-bold text-red-800 mb-4">⚠️ Security Tips</h3>
            <ul class="space-y-2 text-sm text-red-700">
                <li class="flex items-start">
                    <span class="text-red-500 mr-2">•</span>
                    <span>Never share your wallet seed phrase with anyone</span>
                </li>
                <li class="flex items-start">
                    <span class="text-red-500 mr-2">•</span>
                    <span>Always verify wallet addresses before sending</span>
                </li>
                <li class="flex items-start">
                    <span class="text-red-500 mr-2">•</span>
                    <span>Use official wallet apps only</span>
                </li>
                <li class="flex items-start">
                    <span class="text-red-500 mr-2">•</span>
                    <span>Start with small amounts to test the process</span>
                </li>
                <li class="flex items-start">
                    <span class="text-red-500 mr-2">•</span>
                    <span>Keep your wallet software updated</span>
                </li>
            </ul>
        </div>

        <!-- CTA -->
        <div class="text-center bg-gradient-to-r from-primary to-red-600 text-white rounded-xl p-8">
            <h3 class="text-2xl font-montserrat font-bold mb-4">Ready to Buy RWAMP Tokens?</h3>
            <p class="text-white/90 mb-6">Follow our secure 3-step process to purchase your tokens today.</p>
            <a href="{{ route('purchase.create') }}" class="btn-secondary bg-white text-primary hover:bg-gray-100">
                Start Purchase Process
            </a>
        </div>
    </div>
</div>
@endsection

<!-- Purchase Form Content -->
<div class="space-y-4">
    <!-- Compact Token Quantity & Pricing Section -->
    <div class="grid md:grid-cols-2 gap-4">
        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50/50">
            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                Token Quantity <span class="text-red-500">*</span>
                <span class="text-xs font-normal text-gray-500">(min 1000)</span>
            </label>
            <input 
                x-model="formattedTokenQuantity" 
                @input="handleTokenQuantityInput()"
                @keyup="handleTokenQuantityInput()"
                @change="handleTokenQuantityInput()"
                type="text" 
                class="w-full px-3 py-2 text-base border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-white" 
                placeholder="500"
            />
        </div>
        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50/50 space-y-2">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-0.5">Price per Token</label>
                <div class="text-sm font-bold text-gray-800" x-html="formatPriceTag(rates.tokenPkr, {size: 'small', class: 'inline'})"></div>
            </div>
        </div>
    </div>

    <!-- Compact Payment Amounts - Highlighted -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border-2 border-primary/20 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-3 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    <span x-show="selectedNetwork !== 'BTC'">USDT Amount</span>
                    <span x-show="selectedNetwork === 'BTC'">BTC Amount</span>
                </label>
                <div class="text-xl font-bold text-red-600" x-text="usdtAmount"></div>
                <div class="text-xs text-gray-500 mt-1" x-show="selectedNetwork !== 'BTC'" x-text="'$' + usdtUsd.toFixed(4) + ' | ₨' + formatNumberFixed(usdtPkr, 2)"></div>
                <div class="text-xs text-gray-500 mt-1" x-show="selectedNetwork === 'BTC'" x-text="'$' + formatNumberFixed(rates.btcUsd || 60000, 2) + ' | ₨' + formatNumberFixed(rates.btcPkr || (rates.btcUsd * rates.usdToPkr), 2)"></div>
            </div>
            <div class="bg-white rounded-lg p-3 border-2 border-primary/30">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Total Amount</label>
                <div class="text-xl font-bold text-primary" x-html="formatPriceTag(pkrAmount || 0, {size: 'large'})"></div>
            </div>
        </div>
    </div>

    <!-- Ultra-Compact Modern Payment Method Selection -->
    <div class="border border-gray-200 rounded-lg p-2.5 bg-gradient-to-br from-gray-50 to-white">
        <label class="block text-xs font-bold text-gray-800 mb-2">
            Payment Method <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <!-- ERC20 Card -->
            <div 
                @click="selectNetwork('ERC20')" 
                :class="{ 
                    'ring-2 ring-blue-500 ring-offset-1 bg-gradient-to-br from-blue-50 to-blue-100 border-blue-500 shadow-lg scale-[1.02]': selectedNetwork === 'ERC20', 
                    'bg-white border-gray-300 hover:border-blue-400 hover:bg-blue-50/40': selectedNetwork !== 'ERC20' 
                }"
                class="cursor-pointer border-2 rounded-lg p-2 transition-all duration-150 hover:shadow-md hover:scale-[1.02] relative"
            >
                <div class="text-center">
                    <div class="w-7 h-7 mx-auto mb-1.5 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-md">
                        <span class="text-sm">🔵</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight mb-0.5">ERC20</div>
                    <div class="text-[10px] text-gray-600 leading-tight font-semibold">Ethereum</div>
                    <div x-show="selectedNetwork === 'ERC20'" class="mt-1 text-[9px] font-bold text-blue-700 bg-blue-200/80 px-1.5 py-0.5 rounded-full">✓ Selected</div>
                </div>
            </div>
            
            <!-- BEP20 Card - Recommended -->
            <div 
                @click="selectNetwork('BEP20')" 
                :class="{ 
                    'ring-2 ring-yellow-500 ring-offset-1 bg-gradient-to-br from-yellow-50 to-yellow-100 border-yellow-500 shadow-lg scale-[1.02]': selectedNetwork === 'BEP20', 
                    'bg-white border-gray-300 hover:border-yellow-400 hover:bg-yellow-50/40': selectedNetwork !== 'BEP20' 
                }"
                class="cursor-pointer border-2 rounded-lg p-2 transition-all duration-150 hover:shadow-md hover:scale-[1.02] relative"
            >
                <div class="absolute -top-1 -right-1 bg-gradient-to-r from-green-500 to-green-600 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold shadow-md z-20 border border-white">
                    ✓ Recommended
                </div>
                <div class="text-center">
                    <div class="w-7 h-7 mx-auto mb-1.5 rounded-full bg-gradient-to-br from-yellow-500 to-yellow-700 flex items-center justify-center shadow-md">
                        <span class="text-sm">🟡</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight mb-0.5">BEP20</div>
                    <div class="text-[10px] text-gray-600 leading-tight font-semibold">BNB Chain</div>
                    <div x-show="selectedNetwork === 'BEP20'" class="mt-1 text-[9px] font-bold text-yellow-700 bg-yellow-200/80 px-1.5 py-0.5 rounded-full">✓ Selected</div>
                </div>
            </div>
            
            <!-- TRC20 Card -->
            <div 
                @click="selectNetwork('TRC20')" 
                :class="{ 
                    'ring-2 ring-green-500 ring-offset-1 bg-gradient-to-br from-green-50 to-green-100 border-green-500 shadow-lg scale-[1.02]': selectedNetwork === 'TRC20', 
                    'bg-white border-gray-300 hover:border-green-400 hover:bg-green-50/40': selectedNetwork !== 'TRC20' 
                }"
                class="cursor-pointer border-2 rounded-lg p-2 transition-all duration-150 hover:shadow-md hover:scale-[1.02] relative"
            >
                <div class="text-center">
                    <div class="w-7 h-7 mx-auto mb-1.5 rounded-full bg-gradient-to-br from-green-500 to-green-700 flex items-center justify-center shadow-md">
                        <span class="text-sm">🟢</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight mb-0.5">TRC20</div>
                    <div class="text-[10px] text-gray-600 leading-tight font-semibold">Tron</div>
                    <div x-show="selectedNetwork === 'TRC20'" class="mt-1 text-[9px] font-bold text-green-700 bg-green-200/80 px-1.5 py-0.5 rounded-full">✓ Selected</div>
                </div>
            </div>
            
            <!-- BTC Card -->
            <div 
                @click="selectNetwork('BTC')" 
                :class="{ 
                    'ring-2 ring-orange-500 ring-offset-1 bg-gradient-to-br from-orange-50 to-orange-100 border-orange-500 shadow-lg scale-[1.02]': selectedNetwork === 'BTC', 
                    'bg-white border-gray-300 hover:border-orange-400 hover:bg-orange-50/40': selectedNetwork !== 'BTC' 
                }"
                class="cursor-pointer border-2 rounded-lg p-2 transition-all duration-150 hover:shadow-md hover:scale-[1.02] relative"
            >
                <div class="text-center">
                    <div class="w-7 h-7 mx-auto mb-1.5 rounded-full bg-gradient-to-br from-orange-500 to-orange-700 flex items-center justify-center shadow-md">
                        <span class="text-sm">🟠</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight mb-0.5">BTC</div>
                    <div class="text-[10px] text-gray-600 leading-tight font-semibold">Bitcoin</div>
                    <div x-show="selectedNetwork === 'BTC'" class="mt-1 text-[9px] font-bold text-orange-700 bg-orange-200/80 px-1.5 py-0.5 rounded-full">✓ Selected</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact Action Buttons - Enhanced -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2 border-t border-gray-200">
        <!-- Offline Payment Option - Full Message -->
        <button 
            @click="openOfflinePaymentChat()" 
            class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 border-2 border-blue-300 rounded-lg text-xs sm:text-sm font-semibold text-blue-700 transition-all hover:shadow-md hover:border-blue-400"
        >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <span class="text-center">Don't Have USDT? Contact Support via Chat for Offline Payment Options (Bank Transfer, JazzCash, EasyPaisa)</span>
        </button>
        
        <!-- Wallet Action Buttons - Enhanced -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <button 
                x-show="!isWalletConnected" 
                @click="connectWallet()" 
                :disabled="isConnecting || !canConnectWallet()" 
                class="btn-primary px-5 py-2.5 text-sm disabled:opacity-60 disabled:cursor-not-allowed border-2 border-transparent hover:border-primary/50 shadow-md hover:shadow-lg transition-all"
                :title="getConnectButtonTooltip()"
            >
                <span x-show="!isConnecting">
                    <span x-show="isMobile">📱 Connect</span>
                    <span x-show="!isMobile">Connect Wallet</span>
                </span>
                <span x-show="isConnecting">Connecting...</span>
            </button>
            <button 
                x-show="isWalletConnected && selectedNetwork !== 'BTC' && selectedNetwork !== 'TRC20'" 
                @click="executePayment()" 
                :disabled="!canPay() || isProcessingPayment" 
                class="btn-primary px-5 py-2.5 text-sm disabled:opacity-60 disabled:cursor-not-allowed border-2 border-transparent hover:border-primary/50 shadow-md hover:shadow-lg transition-all"
                :title="getPayButtonTooltip()"
            >
                <span x-show="!isProcessingPayment">
                    Pay <span class="font-bold" x-text="getUsdtAmountForPayment()"></span> 
                    <span x-show="selectedNetwork !== 'BTC'">USDT</span>
                    <span x-show="selectedNetwork === 'BTC'">BTC</span>
                </span>
                <span x-show="isProcessingPayment">Processing...</span>
            </button>
            <div x-show="(selectedNetwork === 'BTC' || selectedNetwork === 'TRC20') && isWalletConnected" class="text-xs text-yellow-800 px-3 py-2 bg-yellow-50 border-2 border-yellow-300 rounded-lg">
                <strong>ℹ️</strong> <span x-show="selectedNetwork === 'BTC'">BTC</span><span x-show="selectedNetwork === 'TRC20'">TRC20</span> requires manual transfer. Use <strong>Offline Pay</strong> button above.
            </div>
        </div>
    </div>

    <!-- Compact Status Messages -->
    <div x-show="!canConnectWallet() && !isWalletConnected && selectedNetwork" class="bg-yellow-50 border border-yellow-200 rounded-lg p-2.5 mt-2">
        <div class="text-xs text-yellow-800">
            <strong>ℹ️</strong> 
            <span x-show="selectedNetwork === 'BTC' || selectedNetwork === 'TRC20'">
                <span x-show="selectedNetwork === 'BTC'">BTC</span>
                <span x-show="selectedNetwork === 'TRC20'">TRC20</span> requires manual transfer. Use <strong>Offline Pay</strong> option.
            </span>
        </div>
    </div>
    <div x-show="!canConnectWallet() && !isWalletConnected && !selectedNetwork" class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 mt-2">
        <div class="text-xs text-blue-800">
            <strong>ℹ️</strong> Select a payment method first
        </div>
    </div>
    <div x-show="!walletConnectEnabled && selectedNetwork && selectedNetwork !== 'BTC' && selectedNetwork !== 'TRC20'" class="bg-red-50 border border-red-200 rounded-lg p-2.5 mt-2">
        <div class="text-xs text-red-800">
            <strong>❌</strong> WalletConnect disabled
        </div>
    </div>
    
    <div x-show="isWalletConnected" class="bg-green-50 border border-green-200 rounded-lg p-2.5 mt-2">
        <div class="flex items-center gap-2">
            <span class="text-green-600 text-sm">🟢</span>
            <span class="text-xs font-semibold text-green-800">Connected:</span>
            <code class="text-xs font-mono text-green-700" x-text="connectedAddress.substring(0, 10) + '...' + connectedAddress.substring(connectedAddress.length - 6)"></code>
        </div>
    </div>

    <!-- Compact Footer Info -->
    <p class="text-xs text-gray-500 mt-3 text-center">
        Price: <span class="font-semibold text-gray-700">${{ number_format($rates['tokenUsd'], 4) }}</span> per token | 
        Rates update in real-time
    </p>
</div>


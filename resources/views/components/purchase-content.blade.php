<!-- Purchase Form Content -->
<div>
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
        <div class="space-y-3">
            <!-- Offline Payment Option -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-900 mb-1">Don't Have USDT?</h4>
                        <p class="text-xs text-blue-700 mb-3">Pay through offline methods. Contact our support team via chat to arrange payment through bank transfer, JazzCash, EasyPaisa, or other convenient payment methods.</p>
                        <button 
                            @click="openOfflinePaymentChat()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-colors duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span>Offline Pay</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Crypto Payment Buttons -->
            <div class="flex items-center justify-end gap-3">
                <button 
                    x-show="!isWalletConnected" 
                    @click="connectWallet()" 
                    :disabled="isConnecting || !canConnectWallet()" 
                    class="btn-primary px-6 py-3 disabled:opacity-60 disabled:cursor-not-allowed"
                    :title="getConnectButtonTooltip()"
                >
                    <span x-show="!isConnecting">
                        <span x-show="isMobile">Open in MetaMask 📱</span>
                        <span x-show="!isMobile">Connect Wallet</span>
                    </span>
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
        </div>
        <!-- Helpful messages explaining why button might be disabled -->
        <div x-show="!canConnectWallet() && !isWalletConnected" class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="text-sm text-blue-800">
                <strong>ℹ️ Why can't I connect?</strong>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li x-show="!selectedNetwork">Please select a payment network (ERC20, BEP20, or TRC20)</li>
                    <li x-show="!walletConnectEnabled" class="text-red-600 font-semibold">
                        ❌ WalletConnect is currently disabled. 
                        <span class="text-xs">(Check WALLETCONNECT_ENABLED in .env file)</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div x-show="isWalletConnected" class="bg-green-50 border border-green-200 rounded-lg p-3 mt-3">
            <div class="flex items-center gap-2">
                <span class="text-green-600">🟢</span>
                <span class="text-sm text-green-800">Wallet Connected:</span>
                <code class="text-xs font-mono text-green-700" x-text="connectedAddress"></code>
                <span x-show="isMobile" class="text-xs text-gray-500">📱</span>
            </div>
        </div>
    </div>

    <p class="text-xs text-gray-600 mt-4">Token price: ${{ number_format($rates['tokenUsd'], 4) }} per RWAMP. USDT rate updates live.</p>
</div>


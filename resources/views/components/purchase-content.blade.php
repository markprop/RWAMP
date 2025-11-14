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
                <div class="text-sm font-semibold text-gray-600" x-text="'₨' + formatNumberFixed(rates.tokenPkr, 2) + ' per token | $' + rates.tokenUsd.toFixed(4) + ' per token'"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">USDT Amount (real‑time)</label>
                <div class="text-2xl font-mono font-bold text-primary" x-text="usdtAmount"></div>
                <div class="text-xs text-gray-500" x-text="'USDT price: $' + usdtUsd.toFixed(4) + ' | ₨' + formatNumberFixed(usdtPkr, 2) + ' (Admin Set)'"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PKR Amount (real‑time)</label>
                <div class="text-2xl font-mono font-bold text-primary" x-text="pkrAmountLive"></div>
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
                <span class="text-green-600">✓</span>
                <span class="text-sm text-green-800">Wallet Connected:</span>
                <code class="text-xs font-mono text-green-700" x-text="connectedAddress"></code>
            </div>
        </div>
    </div>

    <p class="text-xs text-gray-600 mt-4">Token price: ${{ number_format($rates['tokenUsd'], 4) }} per RWAMP. USDT rate updates live.</p>
</div>


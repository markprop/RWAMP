<!-- Toast Notification -->
<div x-show="toast.visible" class="fixed bottom-4 right-4 z-[60]">
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
<div x-cloak x-show="paymentConfirmationModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display: none;">
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
                        <span class="text-xs font-semibold text-gray-900" x-html="formatPriceTag(paymentConfirmData.tokenPrice, {size: 'small', class: 'inline'}) + ' per token'"></span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-200">
                        <span class="text-xs text-gray-600">Total Amount:</span>
                        <span class="text-xs font-semibold text-primary" x-text="paymentConfirmData.usdtAmount + ' USDT'"></span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-200">
                        <span class="text-xs text-gray-600">PKR Equivalent:</span>
                        <span class="text-xs font-semibold text-gray-900" x-html="formatPriceTag(paymentConfirmData.pkrAmount, {size: 'small', class: 'inline'})"></span>
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
<div x-cloak x-show="paymentStatusModal" class="fixed inset-0 z-[60] flex items-center justify-center">
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

<!-- Wallet Error / Fallback Modal -->
<div x-cloak x-show="walletErrorModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display: none;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="walletErrorModal=false"></div>
    <div class="relative bg-white rounded-xl shadow-2xl max-w-sm w-full mx-4 transform transition-all"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-montserrat font-bold text-gray-900">Connect Wallet</h3>
                <button @click="walletErrorModal=false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800 space-y-2">
                <p x-text="walletErrorMessage || 'We could not detect a browser wallet on this device.'"></p>
                <p class="leading-snug">
                    On mobile, MetaMask works best when you open this page inside the MetaMask app, or connect via WalletConnect.
                </p>
            </div>

            <div class="space-y-2">
                <button type="button"
                        class="w-full btn-primary text-sm py-2"
                        @click="openMetaMaskDeepLink()">
                    Open in MetaMask App
                </button>
                <button type="button"
                        class="w-full btn-secondary text-sm py-2"
                        @click="retryWalletConnect()">
                    Try WalletConnect Again
                </button>
                <button type="button"
                        class="w-full border border-gray-300 rounded-lg text-sm py-2 text-gray-700 hover:bg-gray-50"
                        @click="walletErrorModal=false; openOfflinePaymentChat();">
                    Use Offline Pay
                </button>
            </div>
        </div>
    </div>
</div>


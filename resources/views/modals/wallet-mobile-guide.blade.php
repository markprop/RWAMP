<!-- Mobile Wallet Guide Modal -->
<div x-show="mobileWalletGuideModal" 
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[70] flex items-center justify-center p-4 rw-modal rw-modal--mobile"
     @click.self="mobileWalletGuideModal = false"
     style="display: none;"
     x-cloak>
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto rw-modal__panel">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-montserrat font-bold text-gray-900">Connect Mobile Wallet</h3>
                <button @click="mobileWalletGuideModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- QR Code Section (if WalletConnect URI available) -->
            <div x-show="walletConnectUri" class="mb-6 text-center">
                <p class="text-sm text-gray-600 mb-3">Scan QR code with your wallet app:</p>
                <div class="bg-white p-4 rounded-lg border-2 border-gray-200 inline-block mb-3" id="walletConnectQrCode">
                    <!-- QR code will be rendered here -->
                </div>
                <button @click="copyWalletConnectUri()" class="text-xs text-primary hover:underline">
                    Copy connection link
                </button>
            </div>

            <!-- Wallet Options -->
            <div class="space-y-3 mb-6">
                <p class="text-sm font-semibold text-gray-700 mb-3">Or open in wallet app:</p>
                
                <!-- MetaMask -->
                <button @click="connectMetaMaskMobile()" 
                        class="w-full flex items-center gap-3 p-4 border-2 border-primary rounded-lg hover:border-primary hover:bg-primary/5 transition-all bg-primary/5">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">🦊</span>
                    </div>
                    <div class="flex-1 text-left">
                        <div class="font-semibold text-gray-900">MetaMask</div>
                        <div class="text-xs text-gray-500">Open in MetaMask app</div>
                    </div>
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Trust Wallet -->
                <button @click="openWalletApp('trust')" 
                        class="w-full flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-all">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">🔵</span>
                    </div>
                    <div class="flex-1 text-left">
                        <div class="font-semibold text-gray-900">Trust Wallet</div>
                        <div class="text-xs text-gray-500">Open in Trust Wallet app</div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Coinbase Wallet -->
                <button @click="openWalletApp('coinbase')" 
                        class="w-full flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-all">
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">🔷</span>
                    </div>
                    <div class="flex-1 text-left">
                        <div class="font-semibold text-gray-900">Coinbase Wallet</div>
                        <div class="text-xs text-gray-500">Open in Coinbase Wallet app</div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800 font-semibold mb-2">📱 Connection Steps:</p>
                <ol class="text-sm text-blue-800 list-decimal list-inside space-y-1">
                    <li>Tap "Open in MetaMask" (or your preferred wallet)</li>
                    <li>Confirm the connection in your wallet app</li>
                    <li>Return to this page - your wallet will be connected automatically</li>
                </ol>
                <p class="text-xs text-blue-700 mt-2">
                    <strong>💡 Tip:</strong> If you don't have a wallet app installed, download MetaMask or Trust Wallet from your app store first.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button @click="mobileWalletGuideModal = false" class="flex-1 btn-secondary btn-small">
                    Cancel
                </button>
                <button @click="retryMobileWalletConnect()" class="flex-1 btn-primary btn-small">
                    Try Again
                </button>
            </div>
        </div>
    </div>
</div>


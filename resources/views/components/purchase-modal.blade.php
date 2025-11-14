@php
    use App\Helpers\PriceHelper;
    $rates = $rates ?? [
        'tokenUsd' => PriceHelper::getRwampUsdPrice(),
        'tokenPkr' => PriceHelper::getRwampPkrPrice(),
        'usdToPkr' => (float) config('crypto.rates.usd_pkr', 278),
        'usdtUsd' => PriceHelper::getUsdtUsdPrice(),
        'usdtPkr' => PriceHelper::getUsdtPkrPrice(),
        'btcUsd' => PriceHelper::getBtcUsdPrice(),
        'btcPkr' => PriceHelper::getBtcPkrPrice(),
    ];
    $wallets = $wallets ?? [
        'TRC20' => (string) config('crypto.wallets.TRC20', ''),
        'ERC20' => (string) config('crypto.wallets.ERC20', ''),
        'BEP20' => (string) config('crypto.wallets.BEP20', config('crypto.wallets.ERC20', '')),
        'BTC' => (string) config('crypto.wallets.BTC', ''),
    ];
    $paymentsDisabled = (bool) ($paymentsDisabled ?? config('crypto.features.payments_enabled') === false);
@endphp

<!-- Purchase Modal -->
<div x-data="purchaseModalFlow()" 
     @open-purchase-modal.window="handlePurchaseModalOpen($event)"
     x-show="purchaseModalOpen" 
     @keydown.escape.window="purchaseModalOpen = false"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
            <div x-show="purchaseModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="purchaseModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

        <!-- Modal panel -->
        <div x-show="purchaseModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl border-2 border-red-300 ring-1 ring-red-500/20 ring-offset-2 ring-offset-white transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            
            <!-- Modal header -->
            <div class="bg-gradient-to-r from-black to-secondary text-white px-6 py-4 border-b border-red-400/30">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-montserrat font-bold">Purchase RWAMP Tokens</h3>
                    <button @click="purchaseModalOpen = false" class="text-white hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-white/80 mt-1 text-sm">Buy using cryptocurrency with WalletConnect. Prices are set by admin.</p>
            </div>

            <!-- Modal body -->
            <div class="bg-white px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto divide-y divide-gray-100">
                @include('components.purchase-content', ['rates' => $rates, 'wallets' => $wallets, 'paymentsDisabled' => $paymentsDisabled])
            </div>
        </div>
    </div>
    
    @include('components.purchase-modals', ['rates' => $rates, 'wallets' => $wallets, 'paymentsDisabled' => $paymentsDisabled])
</div>

@push('scripts')
<script>
// Ensure function is globally accessible
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
        calculatedUsdtAmount: 0,
        usdtUsd: {{ (float) ($rates['usdtUsd'] ?? 1.0) }},
        usdtPkr: {{ (float) ($rates['usdtPkr'] ?? $rates['usdToPkr'] ?? 278) }},
        bonusPercentage: 0,
        bonusTokens: 0,
        isWalletConnected: false,
        connectedAddress: '',
        manualWalletAddress: '',
        isConnecting: false,
        isProcessingPayment: false,
        paymentStatus: '',
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
        walletProvider: null,
        toast: { visible: false, message: '', type: 'success' },
        submitAlert: { visible: false, message: '', type: 'success' },
        modalOpen: false,
        isSubmitting: false,
        rates: {
            tokenUsd: {{ $rates['tokenUsd'] }},
            tokenPkr: {{ (float) ($rates['tokenPkr'] ?? ($rates['tokenUsd'] * $rates['usdToPkr'])) }},
            usdToPkr: {{ $rates['usdToPkr'] }},
            btcUsd: {{ (float) ($rates['btcUsd'] ?? config('crypto.rates.btc_usd', 60000)) }},
            btcPkr: {{ (float) ($rates['btcPkr'] ?? (($rates['btcUsd'] ?? config('crypto.rates.btc_usd', 60000)) * ($rates['usdToPkr'] ?? 278))) }}
        },
        
        init() {
            this.formattedTokenQuantity = this.formatNumber(this.tokenQuantity);
            this.calculateAmounts();
            if (location.search.indexOf('open=purchase') > -1) {
                this.purchaseModalOpen = true;
            }
        },

        formatNumber(value) {
            if (isNaN(value) || value === null || value === '') return '';
            const num = parseFloat(value);
            if (num < 0.01 && num > 0) {
                return num.toFixed(7);
            }
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 7
            }).format(num);
        },

        formatNumberFixed(value, digits = 2) {
            if (isNaN(value) || value === null || value === '') return '0.00';
            const num = parseFloat(value);
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
            const num = String(this.usdAmount).replace(/[^0-9.]/g, '');
            return parseFloat(num || '0');
        },
        
        parseFormattedNumber(formattedString) {
            if (!formattedString || formattedString === '') return 0;
            const cleaned = formattedString.replace(/,/g, '').replace(/[^0-9.]/g, '');
            return cleaned === '' ? 0 : parseFloat(cleaned) || 0;
        },
        
        handleTokenQuantityInput() {
            const rawValue = this.formattedTokenQuantity;
            const parsedValue = this.parseFormattedNumber(rawValue);
            
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
            const quantity = parseFloat(this.tokenQuantity) || 0;
            const pkrValue = quantity * (this.rates.tokenPkr || (this.rates.tokenUsd * this.rates.usdToPkr));
            const usdValue = quantity * this.rates.tokenUsd;
            const usdtValue = usdValue / (this.usdtUsd || 1);
            const pkrValueLive = pkrValue;
            
            this.usdAmount = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 4
            }).format(usdValue);
            
            this.usdtAmount = 'USDT ' + this.formatNumberFixed(usdtValue, 2);
            this.pkrAmountLive = 'PKR ' + this.formatNumberFixed(pkrValueLive, 2);
            this.calculatedUsdtAmount = usdtValue;
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
            const canConnect = this.selectedNetwork && this.walletConnectEnabled;
            return canConnect;
        },

        canPay() {
            return (this.tokenQuantity >= this.minTokenQuantity) && this.selectedNetwork && this.walletConnectEnabled && this.isWalletConnected && !this.paymentsDisabled;
        },

        getConnectButtonTooltip() {
            if (this.isConnecting) return 'Connecting...';
            if (!this.selectedNetwork) return 'Please select a payment network (ERC20, BEP20, or TRC20)';
            if (!this.walletConnectEnabled) return 'WalletConnect is disabled. Check WALLETCONNECT_ENABLED in .env';
            return 'Click to connect your crypto wallet';
        },

        getPayButtonTooltip() {
            if (this.isProcessingPayment) return 'Processing payment...';
            if (!this.isWalletConnected) return 'Please connect your wallet first';
            if (this.tokenQuantity < this.minTokenQuantity) return 'Minimum purchase is ' + this.minTokenQuantity + ' tokens';
            if (!this.selectedNetwork) return 'Please select a payment network';
            const amount = this.getUsdtAmountForPayment();
            return `Click to send ${amount} USDT from your connected wallet`;
        },

        getUsdtAmountForPayment() {
            const tokenQty = parseFloat(this.tokenQuantity) || 0;
            const tokenPrice = this.rates.tokenUsd;
            const usdtPrice = this.usdtUsd || 1;
            const usdtAmount = (tokenQty * tokenPrice) / usdtPrice;
            return usdtAmount.toFixed(2);
        },

        getWalletAddress() {
            const wallets = {
                'ERC20': '{{ config("crypto.wallets.ERC20", "") }}',
                'BEP20': '{{ config("crypto.wallets.BEP20", config("crypto.wallets.ERC20", "")) }}',
                'TRC20': '{{ config("crypto.wallets.TRC20", "") }}',
                'BTC': '{{ config("crypto.wallets.BTC", "") }}'
            };
            return wallets[this.selectedNetwork] || '';
        },

        getContractAddress() {
            const contracts = {
                'ERC20': '{{ config("crypto.contracts.usdt_erc20", "") }}',
                'BEP20': '{{ config("crypto.contracts.usdt_bep20", "") }}',
                'TRC20': '{{ config("crypto.contracts.usdt_trc20", "") }}'
            };
            return contracts[this.selectedNetwork] || '';
        },

        getChainId() {
            const chainIds = {
                'ERC20': '0x1',
                'BEP20': '0x38',
                'TRC20': null
            };
            return chainIds[this.selectedNetwork];
        },
        
        getQrCode() {
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
            if (!this.canConnectWallet()) {
                if (!this.selectedNetwork) {
                    this.showToast('Please select a payment network first', 'warning');
                } else if (!this.walletConnectEnabled) {
                    this.showToast('WalletConnect is currently disabled. Please contact support.', 'error');
                }
                return;
            }
            
            try {
                this.isConnecting = true;
                if (window.ethereum && typeof window.ethereum.request === 'function') {
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
                        const message = String(mmError && mmError.message || '').toLowerCase();
                        if (message.includes('user rejected')) {
                            this.showToast('Wallet connection was cancelled in MetaMask.', 'warning');
                            this.isConnecting = false;
                            return;
                        }
                    }
                }

                if (!window.walletConnectLoaded || !window.walletConnectModal) {
                    window.initializeWalletConnect();
                    if (!window.walletConnectLoaded || !window.walletConnectModal) {
                        this.isConnecting = false;
                    }
                }

                if (window.walletConnectLoaded && window.walletConnectModal) {
                    try {
                        const session = await window.walletConnectModal.open();
                        if (session && session.accounts && session.accounts.length > 0) {
                            this.isWalletConnected = true;
                            this.connectedAddress = session.accounts[0];
                            if (window.ethereum) {
                                this.walletProvider = window.ethereum;
                            } else if (window.walletConnectModal && window.walletConnectModal.getProvider) {
                                this.walletProvider = window.walletConnectModal.getProvider();
                            } else if (session.provider) {
                                this.walletProvider = session.provider;
                            }
                            await this.saveWalletAddress(this.connectedAddress);
                            this.showToast('Wallet connected successfully!', 'success');
                            this.isConnecting = false;
                            return;
                        } else {
                            this.showToast('No wallet was connected. Please try again.', 'warning');
                            this.isConnecting = false;
                            return;
                        }
                    } catch (wcError) {
                        console.log('WalletConnect modal failed, trying MetaMask directly')
                    }
                }
                
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
                        if (mmError.code === 4001) {
                            this.showToast('Wallet connection was cancelled.', 'warning');
                        } else {
                            this.showToast('Failed to connect MetaMask. Please check your wallet and try again.', 'error');
                        }
                    }
                } else if (!this.isWalletConnected) {
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
            
            const address = this.manualWalletAddress.trim();
            if (address.length < 20 || (!address.startsWith('0x') && !address.startsWith('T') && !address.startsWith('1') && !address.startsWith('3') && !address.startsWith('bc1'))) {
                this.showToast('Enter a valid wallet address (0x..., T..., 1/3/bc1...)', 'warning');
                return;
            }
            
            this.isWalletConnected = true;
            this.connectedAddress = address;
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
            if (!this.canPay() || !this.isWalletConnected) {
                this.showToast('Please connect your wallet first', 'warning');
                return;
            }

            if (this.selectedNetwork === 'TRC20') {
                this.showToast('TRC20 payments require manual transfer. Please use the manual payment method.', 'info');
                return;
            }

            if (!this.walletProvider) {
                this.showToast('Wallet provider not available. Please reconnect your wallet.', 'error');
                return;
            }

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

            this.paymentConfirmData = {
                tokens: this.formatNumber(this.tokenQuantity),
                tokenPrice: tokenPricePkr,
                usdtAmount: usdtAmountFormatted,
                pkrAmount: pkrAmount,
                network: this.selectedNetwork,
                toAddress: toAddress
            };

            this.paymentConfirmationModal = true;
        },

        cancelPaymentConfirmation() {
            this.paymentConfirmationModal = false;
            this.isProcessingPayment = false;
            this.paymentStatusModal = false;
        },

        async proceedWithPayment() {
            this.paymentConfirmationModal = false;
            await this.executePaymentTransaction();
        },

        async executePaymentTransaction() {
            if (!this.canPay() || !this.isWalletConnected) {
                this.showToast('Please connect your wallet first', 'warning');
                return;
            }

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

                if (chainId) {
                    try {
                        await this.walletProvider.request({
                            method: 'wallet_switchEthereumChain',
                            params: [{ chainId: chainId }],
                        });
                    } catch (switchError) {
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
                
                let tokenDecimals = 6;
                try {
                    const decimalsData = '0x313ce567';
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
                    if (this.selectedNetwork === 'BEP20') {
                        tokenDecimals = 18;
                    } else if (this.selectedNetwork === 'ERC20') {
                        tokenDecimals = 6;
                    }
                    console.log('Using default decimals for', this.selectedNetwork + ':', tokenDecimals)
                }
                
                const tokenQty = parseFloat(this.tokenQuantity) || 0;
                const tokenPrice = parseFloat(this.rates.tokenUsd) || 0;
                const usdtPrice = parseFloat(this.usdtUsd) || 1;
                
                if (tokenQty <= 0 || tokenPrice <= 0) {
                    throw new Error('Invalid token quantity or price. Please check your input.');
                }
                
                const usdValue = tokenQty * tokenPrice;
                const usdtAmount = usdValue / usdtPrice;
                
                if (isNaN(usdtAmount) || usdtAmount <= 0) {
                    throw new Error('Invalid USDT amount calculated. Please check your token quantity.');
                }
                
                const decimalsMultiplier = Math.pow(10, tokenDecimals);
                const usdtAmountInSmallestUnit = Math.round(usdtAmount * decimalsMultiplier);
                
                if (usdtAmountInSmallestUnit <= 0 || usdtAmountInSmallestUnit < 1) {
                    throw new Error('Amount too small. Minimum purchase is ' + this.minTokenQuantity + ' tokens.');
                }
                
                const usdtAmountBigInt = BigInt(usdtAmountInSmallestUnit);
                const usdtAmountFormatted = usdtAmount.toFixed(6);

                if (!toAddress || !toAddress.match(/^0x[a-fA-F0-9]{40}$/)) {
                    throw new Error('Invalid recipient wallet address format');
                }
                if (!contractAddress || !contractAddress.match(/^0x[a-fA-F0-9]{40}$/)) {
                    throw new Error('Invalid USDT contract address format');
                }
                
                const transferFunctionSignature = 'a9059cbb';
                const cleanToAddress = toAddress.startsWith('0x') ? toAddress.slice(2) : toAddress;
                const toAddressLower = cleanToAddress.toLowerCase();
                
                if (toAddressLower.length !== 40) {
                    throw new Error('Invalid recipient address length. Expected 40 hex characters.');
                }
                
                const toAddressPadded = toAddressLower.padStart(64, '0');
                const amountHex = usdtAmountBigInt.toString(16).padStart(64, '0');
                const data = transferFunctionSignature + toAddressPadded + amountHex;

                let gasPrice = null;
                try {
                    const gasPriceHex = await this.walletProvider.request({
                        method: 'eth_gasPrice',
                        params: []
                    });
                    gasPrice = gasPriceHex;
                } catch (gasPriceError) {
                    console.warn('Gas price fetch failed, MetaMask will use default:', gasPriceError)
                }

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
                    await new Promise(resolve => setTimeout(resolve, 300));
                } catch (simError) {
                    console.log('Transaction simulation note:', simError.message || 'OK')
                    await new Promise(resolve => setTimeout(resolve, 300));
                }

                let gasLimit = '0x186a0';
                try {
                    const gasEstimate = await this.walletProvider.request({
                        method: 'eth_estimateGas',
                        params: [{
                            from: this.connectedAddress,
                            to: contractAddress,
                            value: '0x0',
                            data: '0x' + data,
                        }],
                    });
                    const estimatedGas = BigInt(gasEstimate);
                    const bufferedGas = estimatedGas * BigInt(120) / BigInt(100);
                    gasLimit = '0x' + bufferedGas.toString(16);
                } catch (gasError) {
                    console.warn('Gas estimation failed, using default:', gasError)
                    gasLimit = '0x186a0';
                }

                const completeData = '0x' + data;
                
                if (completeData.length !== 138) {
                    throw new Error('Transaction data format error. Please refresh and try again.');
                }
                
                const txParams = {
                    from: this.connectedAddress,
                    to: contractAddress,
                    value: '0x0',
                    data: completeData,
                    gas: gasLimit,
                };
                
                if (gasPrice) {
                    txParams.gasPrice = gasPrice;
                }
                
                const txHash = await this.walletProvider.request({
                    method: 'eth_sendTransaction',
                    params: [txParams],
                });

                this.lastTxHash = txHash;
                this.paymentStatus = 'success';
                
                const submitResult = await this.submitTxHashAfterPayment(txHash);
                
                if (submitResult && submitResult.success) {
                    this.showToast(`Payment of ${usdtAmountFormatted} USDT submitted successfully! Transaction hash saved to database.`, 'success');
                } else {
                    this.showToast(`Payment successful! Transaction hash: ${txHash.substring(0, 10)}... (Saving to database...)`, 'success');
                    // Best-effort retry while navigating
                    setTimeout(async () => { await this.submitTxHashAfterPayment(txHash); }, 1000);
                }

                // Close any status UI and navigate to purchase history for confirmation
                this.paymentStatusModal = false;
                this.purchaseModalOpen = false;
                setTimeout(() => { window.location.assign('{{ route('user.history') }}'); }, 1000);
                
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
                const extractNumeric = (str) => {
                    if (typeof str !== 'string') return String(str || '0');
                    return str.replace(/[^0-9.]/g, '') || '0';
                };
                
                const usdValue = extractNumeric(this.usdAmount);
                const pkrValue = extractNumeric(this.pkrAmountLive);
                const tokenQty = parseFloat(this.tokenQuantity) || 0;
                
                const response = await fetch('/api/submit-tx-hash', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        tx_hash: txHash,
                        network: this.selectedNetwork,
                        token_amount: tokenQty,
                        usd_amount: usdValue,
                        pkr_amount: pkrValue
                    })
                });
                
                const responseData = await response.json();
                
                if (!response.ok) {
                    throw new Error(responseData.message || 'Failed to save transaction hash');
                }
                
                return { success: true, data: responseData };
            } catch (error) {
                console.error('❌ Error submitting transaction hash:', error)
                return { success: false, error: error.message };
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
</script>
@endpush


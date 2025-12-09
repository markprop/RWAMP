<!-- Buy From Reseller Modal -->
<div x-data="buyFromResellerModal()" 
     @open-buy-from-reseller-modal.window="openModal()"
     x-show="buyFromResellerModal" 
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4" 
     style="display: none;"
     x-cloak>
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-montserrat font-bold text-gray-900">Buy Coins From Reseller</h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Step 1: Select Reseller -->
            <div x-show="step === 1">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Reseller (Optional)</label>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        @input.debounce.500ms="searchResellers()"
                        class="form-input w-full"
                        placeholder="Search by name, email, or referral code..."
                    >
                </div>

                <div x-show="loading" class="text-center py-8">
                    <p class="text-gray-600">Loading resellers...</p>
                </div>

                <div x-show="!loading && resellers.length > 0" class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                    <template x-for="reseller in resellers" :key="reseller.id">
                        <div 
                            @click="selectReseller(reseller)"
                            class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-colors"
                            :class="isSelected(reseller) ? 'border-primary bg-primary/5' : (reseller.is_linked ? 'border-green-500 bg-green-50' : '')"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold" x-text="reseller.name"></div>
                                        <span x-show="reseller.is_linked" class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded font-medium">Your Reseller</span>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="font-bold text-primary text-lg" x-html="formatPriceTag(reseller.coin_price || 0, {size: 'normal', class: 'inline'})"></div>
                                    <div class="text-xs text-gray-500">per coin</div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && resellers.length === 0" class="text-center py-8 text-gray-500">
                    <p>No resellers available.</p>
                </div>

                <div x-show="selectedReseller" class="mt-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="font-semibold text-blue-900">Selected Reseller:</p>
                        <p class="text-blue-800">
                            <span x-text="selectedReseller ? selectedReseller.name : ''"></span> - 
                            <span x-html="formatPriceTag(selectedReseller ? (selectedReseller.coin_price || 0) : 0, {size: 'small', class: 'inline'})"></span> per coin
                        </p>
                    </div>
                    <button @click="step = 2" class="btn-primary w-full">Continue</button>
                </div>
            </div>

            <!-- Step 2: Enter Quantity and OTP -->
            <div x-show="step === 2">
                <h4 class="text-lg font-montserrat font-bold mb-4">Enter Purchase Details</h4>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-600">Reseller: <strong><span x-text="selectedReseller ? selectedReseller.name : ''"></span></strong></p>
                    <p class="text-sm text-gray-600">Price: <strong><span x-html="formatPriceTag(selectedReseller ? (selectedReseller.coin_price || 0) : 0, {size: 'small', class: 'inline'})"></span></strong> per coin</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Coin Quantity</label>
                    <input 
                        type="number" 
                        x-model="coinQuantity"
                        @input="calculateTotal()"
                        min="1"
                        step="1"
                        class="form-input w-full"
                        placeholder="Enter number of coins"
                    >
                </div>

                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm font-semibold text-yellow-900">Total Amount:</p>
                    <p class="text-2xl font-bold text-yellow-800" x-html="formatPriceTag(totalAmount, {size: 'large'})"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        x-model="email"
                        value="{{ auth()->user()->email }}"
                        class="form-input w-full"
                        readonly
                    >
                    <p class="text-xs text-gray-500 mt-1">OTP will be sent to this email</p>
                </div>

                <div class="mb-4">
                    <button 
                        @click="sendOtp()"
                        :disabled="otpSent || sendingOtp"
                        class="btn-secondary w-full"
                    >
                        <span x-show="!sendingOtp && !otpSent">Send OTP</span>
                        <span x-show="sendingOtp">Sending...</span>
                        <span x-show="otpSent">OTP Sent ✓</span>
                    </button>
                </div>

                <div x-show="otpSent" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enter OTP</label>
                    <input 
                        type="text" 
                        x-model="otp"
                        maxlength="6"
                        class="form-input w-full text-center text-2xl tracking-widest"
                        placeholder="000000"
                    >
                </div>

                <div class="flex gap-3">
                    <button @click="step = 1" class="btn-secondary flex-1">Back</button>
                    <button 
                        @click="submitRequest()"
                        :disabled="!canSubmit || submitting"
                        class="btn-primary flex-1"
                    >
                        <span x-show="!submitting">Submit Request</span>
                        <span x-show="submitting">Submitting...</span>
                    </button>
                </div>
            </div>

            <!-- Success Message -->
            <div x-show="step === 3" class="text-center py-8">
                <div class="text-green-600 text-5xl mb-4">✓</div>
                <h4 class="text-xl font-montserrat font-bold mb-4">Request Submitted Successfully!</h4>
                <p class="text-gray-600 mb-6">Your buy request has been submitted. The reseller will review and approve it.</p>
                <button @click="closeModal()" class="btn-primary">Close</button>
            </div>

            <!-- Message Box -->
            <div x-show="message.show" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-2"
                 class="fixed top-4 right-4 z-[70] max-w-md w-full mx-4"
                 style="display: none;"
                 x-cloak>
                <div class="bg-white rounded-lg shadow-2xl border-l-4 p-4"
                     :class="{
                         'border-green-500': message.type === 'success',
                         'border-red-500': message.type === 'error',
                         'border-yellow-500': message.type === 'warning',
                         'border-blue-500': message.type === 'info'
                     }">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg x-show="message.type === 'success'" class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg x-show="message.type === 'error'" class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg x-show="message.type === 'warning'" class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <svg x-show="message.type === 'info'" class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900" x-text="message.text"></p>
                            <div x-show="hasDebugInfo()" class="mt-2 text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                <p class="font-semibold mb-1">Debug Info:</p>
                                <pre x-text="JSON.stringify(message.debug, null, 2)" class="text-xs overflow-auto max-h-40"></pre>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button @click="message.show = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function buyFromResellerModal() {
    return {
        buyFromResellerModal: false,
        step: 1,
        searchQuery: '',
        resellers: [],
        loading: false,
        selectedReseller: null,
        coinQuantity: 1,
        totalAmount: 0,
        email: '{{ auth()->user()->email }}',
        otp: '',
        otpSent: false,
        sendingOtp: false,
        submitting: false,
        message: {
            show: false,
            type: 'info', // success, error, warning, info
            text: '',
            debug: null
        },

        hasDebugInfo() {
            if (!this.message || !this.message.debug) return false;
            const debug = this.message.debug;
            if (debug === null || debug === undefined) return false;
            // Check if it's an object with properties
            try {
                const keys = Object.keys(debug);
                return keys.length > 0;
            } catch (e) {
                return false;
            }
        },

        openModal() {
            this.buyFromResellerModal = true;
            this.loadAllResellers();
        },

        closeModal() {
            this.buyFromResellerModal = false;
            // Reset form after modal closes
            setTimeout(() => {
                this.step = 1;
                this.selectedReseller = null;
                this.coinQuantity = 1;
                this.totalAmount = 0;
                this.otp = '';
                this.otpSent = false;
                this.searchQuery = '';
            }, 300);
        },

        async loadAllResellers() {
            this.loading = true;
            try {
                const response = await fetch('/api/resellers/search?q=', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
,
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    // console.error('Response error:', response.status, errorText)
                    this.showMessage('error', 'Failed to load resellers. Please try again.');
                    this.resellers = [];
                    return;
                }
                
                const data = await response.json();
                this.resellers = Array.isArray(data) ? data : [];
                // console.log('Loaded resellers:', this.resellers.length)
            } catch (error) {
                // console.error('Error loading resellers:', error)
                this.showMessage('error', 'Failed to load resellers. Please try again.');
                this.resellers = [];
            } finally {
                this.loading = false;
            }
        },

        async searchResellers() {
            this.loading = true;
            try {
                const query = this.searchQuery.trim();
                const url = `/api/resellers/search?q=${encodeURIComponent(query)}`;
                
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
,
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    // console.error('Response error:', response.status, errorText)
                    this.showMessage('error', 'Failed to search resellers. Please try again.');
                    this.resellers = [];
                    return;
                }
                
                const data = await response.json();
                this.resellers = Array.isArray(data) ? data : [];
                // console.log('Search results:', this.resellers.length)
            } catch (error) {
                // console.error('Error searching resellers:', error)
                this.showMessage('error', 'Failed to search resellers. Please try again.');
                this.resellers = [];
            } finally {
                this.loading = false;
            }
        },

        isSelected(reseller) {
            if (!this.selectedReseller || !reseller) return false;
            return this.selectedReseller.id === reseller.id;
        },

        selectReseller(reseller) {
            if (!reseller) return;
            this.selectedReseller = reseller;
            this.calculateTotal();
        },

        calculateTotal() {
            if (this.selectedReseller && this.coinQuantity > 0) {
                const price = this.selectedReseller.coin_price || 0;
                this.totalAmount = this.coinQuantity * price;
            } else {
                this.totalAmount = 0;
            }
        },

        showMessage(type, text, debug = null) {
            this.message = {
                show: true,
                type: type,
                text: text,
                debug: debug
,
            };
            // Auto-hide after 5 seconds for success/info, 10 seconds for errors
            const duration = (type === 'error' || type === 'warning') ? 10000 : 5000;
            setTimeout(() => {
                this.message.show = false;
            }, duration);
        },

        async sendOtp() {
            this.sendingOtp = true;
            // console.log('=== Sending OTP ===')
            // console.log('Email:', this.email)
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const requestBody = {
                    email: this.email
,
                };
                // console.log('Request body:', requestBody)

                const response = await fetch('{{ route("buy.from.reseller.send-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(requestBody)
,
                });

                // console.log('Response status:', response.status)
                // console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                if (!response.ok) {
                    const errorText = await response.text();
                    // console.error('OTP response error:', response.status, errorText)
                    try {
                        const errorData = JSON.parse(errorText);
                        // console.error('Error data:', errorData)
                        this.showMessage('error', errorData.message || 'Failed to send OTP. Please try again.', errorData);
                    } catch (e) {
                        this.showMessage('error', `Failed to send OTP (${response.status}). Please try again.`, { raw: errorText });
                    }
                    return;
                }

                const data = await response.json();
                // console.log('Response data:', data)
                
                if (data.success) {
                    this.otpSent = true;
                    this.showMessage('success', 'OTP sent to your email. Please check your inbox.');
                    // console.log('OTP sent successfully')
                } else {
                    this.showMessage('error', data.message || 'Failed to send OTP. Please try again.', data);
                }
            } catch (error) {
                // console.error('Error sending OTP:', error)
                this.showMessage('error', 'Failed to send OTP. Please try again.', { error: error.message, stack: error.stack });
            } finally {
                this.sendingOtp = false;
            }
        },

        get canSubmit() {
            return this.selectedReseller && 
                   this.coinQuantity > 0 && 
                   this.otpSent && 
                   this.otp.length === 6;
        },

        async submitRequest() {
            if (!this.canSubmit) return;

            this.submitting = true;
            // console.log('=== Submitting Buy Request ===')
            
            const requestBody = {
                reseller_id: this.selectedReseller.id,
                coin_quantity: this.coinQuantity,
                otp: this.otp,
                email: this.email
,
            };
            // console.log('Request body:', requestBody)
            // console.log('OTP value:', this.otp)
            // console.log('OTP type:', typeof this.otp)
            // console.log('OTP length:', this.otp.length)
            // console.log('Email:', this.email)
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch('{{ route("buy.from.reseller.request") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(requestBody)
,
                });

                // console.log('Response status:', response.status)
                // console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                const responseText = await response.text();
                // console.log('Response text (raw):', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                    // console.log('Response data (parsed):', data);
                } catch (e) {
                    // console.error('Failed to parse JSON response:', e)
                    this.showMessage('error', 'Invalid response from server. Please try again.', { raw: responseText });
                    return;
                }

                if (data.success) {
                    this.step = 3;
                    this.showMessage('success', 'Buy request submitted successfully!');
                    // console.log('Request submitted successfully')
                } else {
                    // console.error('Request failed:', data)
                    this.showMessage('error', data.message || 'Failed to submit request. Please try again.', data.debug || null);
                }
            } catch (error) {
                // console.error('Error submitting request:', error)
                this.showMessage('error', 'Failed to submit request. Please try again.', { error: error.message, stack: error.stack });
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush


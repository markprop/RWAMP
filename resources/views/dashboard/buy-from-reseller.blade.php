@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="buyFromReseller()">
    <!-- Sidebar -->
    @include('components.investor-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Buy Coins From Reseller</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Purchase RWAMP tokens from a reseller</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8">
            <!-- Step 1: Search and Select Reseller -->
            <div x-show="step === 1">
                <h2 class="text-2xl font-montserrat font-bold mb-6">Step 1: Select Reseller</h2>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Reseller</label>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        @input.debounce.500ms="searchResellers()"
                        class="form-input w-full"
                        placeholder="Search by name, email, or referral code..."
                    >
                </div>

                <div x-show="searching" class="text-center py-4">
                    <p class="text-gray-600">Searching...</p>
                </div>

                <div x-show="!searching && resellers.length > 0" class="space-y-3 mb-6">
                    <div 
                        v-for="reseller in resellers" 
                        :key="reseller.id"
                        @click="selectReseller(reseller)"
                        class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-colors"
                        :class="selectedReseller?.id === reseller.id ? 'border-primary bg-primary/5' : ''"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold" x-text="reseller.name"></div>
                                <div class="text-sm text-gray-600" x-text="reseller.email"></div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Referral Code: <code class="bg-gray-100 px-1 rounded" x-text="reseller.referral_code"></code>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-primary">PKR <span x-text="reseller.coin_price.toFixed(2)"></span></div>
                                <div class="text-xs text-gray-500">per coin</div>
                                <div class="text-xs text-gray-500 mt-1">Balance: <span x-text="reseller.token_balance.toLocaleString()"></span> RWAMP</div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!searching && resellers.length === 0 && searchQuery.length > 0" class="text-center py-4 text-gray-500">
                    <p>No resellers found.</p>
                </div>

                <div x-show="selectedReseller" class="mt-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="font-semibold text-blue-900">Selected Reseller:</p>
                        <p class="text-blue-800"><span x-text="selectedReseller?.name"></span> - PKR <span x-text="selectedReseller?.coin_price?.toFixed(2)"></span> per coin</p>
                    </div>
                    <button @click="step = 2" class="btn-primary w-full">Continue</button>
                </div>
            </div>

            <!-- Step 2: Enter Quantity and OTP -->
            <div x-show="step === 2">
                <h2 class="text-2xl font-montserrat font-bold mb-6">Step 2: Enter Details</h2>
                
                <div class="mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600">Reseller: <strong><span x-text="selectedReseller?.name"></span></strong></p>
                        <p class="text-sm text-gray-600">Price: <strong>PKR <span x-text="selectedReseller?.coin_price?.toFixed(2)"></span></strong> per coin</p>
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
                        <p class="text-2xl font-bold text-yellow-800">PKR <span x-text="totalAmount.toFixed(2)"></span></p>
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
            </div>

            <!-- Success Message -->
            <div x-show="step === 3" class="text-center py-8">
                <div class="text-green-600 text-5xl mb-4">✓</div>
                <h2 class="text-2xl font-montserrat font-bold mb-4">Request Submitted Successfully!</h2>
                <p class="text-gray-600 mb-6">Your buy request has been submitted. The reseller will review and approve it.</p>
                <a href="{{ route('dashboard.investor') }}" class="btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function buyFromReseller() {
    return {
        step: 1,
        searchQuery: '',
        resellers: [],
        searching: false,
        selectedReseller: null,
        coinQuantity: 1,
        totalAmount: 0,
        email: '{{ auth()->user()->email }}',
        otp: '',
        otpSent: false,
        sendingOtp: false,
        submitting: false,

        async searchResellers() {
            if (this.searchQuery.length < 2) {
                this.resellers = [];
                return;
            }

            this.searching = true;
            try {
                const response = await fetch(`/api/resellers/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.resellers = data;
            } catch (error) {
                console.error('Error searching resellers:', error)
                alert('Failed to search resellers. Please try again.');
            } finally {
                this.searching = false;
            }
        },

        selectReseller(reseller) {
            this.selectedReseller = reseller;
            this.calculateTotal();
        },

        calculateTotal() {
            if (this.selectedReseller && this.coinQuantity > 0) {
                this.totalAmount = this.coinQuantity * this.selectedReseller.coin_price;
            } else {
                this.totalAmount = 0;
            }
        },

        async sendOtp() {
            this.sendingOtp = true;
            try {
                const response = await fetch('/api/reseller/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email: this.email
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.otpSent = true;
                    alert('OTP sent to your email. Please check your inbox.');
                } else {
                    alert(data.message || 'Failed to send OTP. Please try again.');
                }
            } catch (error) {
                console.error('Error sending OTP:', error)
                alert('Failed to send OTP. Please try again.');
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
            try {
                const response = await fetch('{{ route("buy.from.reseller.request") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        reseller_id: this.selectedReseller.id,
                        coin_quantity: this.coinQuantity,
                        otp: this.otp,
                        email: this.email
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.step = 3;
                } else {
                    alert(data.message || 'Failed to submit request. Please try again.');
                }
            } catch (error) {
                console.error('Error submitting request:', error)
                alert('Failed to submit request. Please try again.');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection


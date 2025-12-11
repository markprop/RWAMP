@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.admin-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Price Management</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Manage RWAMP token price - Enter PKR price and system auto-detects all cryptocurrency prices</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Current Prices Display -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-montserrat font-bold mb-6">Current Prices (Auto-Detected)</h2>
            
            <!-- RWAMP Token Prices -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">RWAMP Token Prices</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="text-sm text-purple-600 mb-1">RWAMP Price (PKR) - Admin Set</div>
                        <div class="text-3xl font-bold text-purple-800">₨{{ number_format($currentPrices['rwamp_pkr'], 2) }}</div>
                        <div class="text-xs text-purple-600 mt-1">Per token (admin controlled)</div>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <div class="text-sm text-indigo-600 mb-1">RWAMP Price (USD) - Auto Calculated</div>
                        <div class="text-3xl font-bold text-indigo-800">${{ number_format($currentPrices['rwamp_usd'], 4) }}</div>
                        <div class="text-xs text-indigo-600 mt-1">Calculated from PKR price</div>
                    </div>
                </div>
            </div>

            <!-- USDT Prices (Same for all networks) -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">USDT Prices (ERC20, BEP20, TRC20) - Auto Fetched</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="text-sm text-blue-600 mb-1">USDT Price (USD) - Live from API</div>
                        <div class="text-3xl font-bold text-blue-800">${{ number_format($currentPrices['usdt_usd'], 4) }}</div>
                        <div class="text-xs text-blue-600 mt-1">Fetched from CoinGecko</div>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="text-sm text-green-600 mb-1">USDT Price (PKR) - Auto Calculated</div>
                        <div class="text-3xl font-bold text-green-800">₨{{ number_format($currentPrices['usdt_pkr'], 2) }}</div>
                        <div class="text-xs text-green-600 mt-1">Calculated from USD price</div>
                    </div>
                </div>
                <div class="mt-4 grid md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-600 mb-1">ERC20 (Ethereum)</div>
                        <div class="text-sm font-semibold text-gray-800">${{ number_format($currentPrices['usdt_usd'], 4) }}</div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-600 mb-1">BEP20 (BNB Chain)</div>
                        <div class="text-sm font-semibold text-gray-800">${{ number_format($currentPrices['usdt_usd'], 4) }}</div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-600 mb-1">TRC20 (Tron)</div>
                        <div class="text-sm font-semibold text-gray-800">${{ number_format($currentPrices['usdt_usd'], 4) }}</div>
                    </div>
                </div>
            </div>

            <!-- BTC Prices -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Bitcoin (BTC) Prices - Auto Fetched</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="text-sm text-orange-600 mb-1">BTC Price (USD) - Live from API</div>
                        <div class="text-3xl font-bold text-orange-800">${{ number_format($currentPrices['btc_usd'], 2) }}</div>
                        <div class="text-xs text-orange-600 mt-1">Fetched from CoinGecko</div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="text-sm text-yellow-600 mb-1">BTC Price (PKR) - Auto Calculated</div>
                        <div class="text-3xl font-bold text-yellow-800">₨{{ number_format($currentPrices['btc_pkr'], 2) }}</div>
                        <div class="text-xs text-yellow-600 mt-1">Calculated from USD price</div>
                    </div>
                </div>
            </div>

            <!-- Reference Rate -->
            <div class="grid md:grid-cols-1 gap-6">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="text-sm text-gray-600 mb-1">USD to PKR Exchange Rate</div>
                    <div class="text-xl font-bold text-gray-800">₨{{ number_format($currentPrices['usd_pkr'], 2) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Base exchange rate (from config)</div>
                </div>
            </div>

            <!-- Reseller Rates -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Reseller Rates</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="text-sm text-green-600 mb-1">Reseller Commission Rate</div>
                        <div class="text-3xl font-bold text-green-800">{{ number_format($currentPrices['reseller_commission_rate'] * 100, 1) }}%</div>
                        <div class="text-xs text-green-600 mt-1">Commission earned by resellers on direct purchases</div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="text-sm text-blue-600 mb-1">Reseller Markup Rate</div>
                        <div class="text-3xl font-bold text-blue-800">{{ number_format($currentPrices['reseller_markup_rate'] * 100, 1) }}%</div>
                        <div class="text-xs text-blue-600 mt-1">Markup applied when users buy from reseller</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Update Form -->
        <div class="bg-white rounded-xl shadow-xl p-6">
            <h2 class="text-2xl font-montserrat font-bold mb-6">Update Prices & Reseller Rates</h2>
            <p class="text-gray-600 mb-6">
                Update RWAMP token price and/or reseller commission/markup rates. Changes apply immediately and dynamically across the system.
            </p>

            <form method="POST" action="{{ route('admin.prices.update') }}" class="space-y-6">
                @csrf
                
                <div class="max-w-md">
                    <label for="rwamp_pkr" class="block text-sm font-medium text-gray-700 mb-2">
                        RWAMP Token Price (PKR) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="rwamp_pkr" 
                        name="rwamp_pkr" 
                        value="{{ old('rwamp_pkr', $currentPrices['rwamp_pkr']) }}"
                        step="0.01" 
                        min="0.01" 
                        max="1000000"
                        required
                        class="form-input w-full text-lg"
                        placeholder="3.00"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        Price per RWAMP token in Pakistani Rupees (e.g., 3.00 means ₨3 per token)
                    </p>
                    @error('rwamp_pkr')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reseller Rates -->
                <div class="border-t pt-6 mt-6">
                    <h3 class="text-xl font-montserrat font-bold mb-4">Reseller Rates (Optional)</h3>
                    <p class="text-gray-600 mb-4 text-sm">
                        These rates can be updated independently. Leave blank to keep current values.
                    </p>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="reseller_commission_rate" class="block text-sm font-medium text-gray-700 mb-2">
                                Reseller Commission Rate (%)
                            </label>
                            <input 
                                type="number" 
                                id="reseller_commission_rate" 
                                name="reseller_commission_rate" 
                                value="{{ old('reseller_commission_rate', $currentPrices['reseller_commission_rate'] * 100) }}"
                                step="0.1" 
                                min="0" 
                                max="100"
                                class="form-input w-full text-lg"
                                placeholder="10.0"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Percentage commission resellers earn on direct purchases (e.g., 10.0 = 10%)
                            </p>
                            @error('reseller_commission_rate')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    
                    <div>
                        <label for="reseller_markup_rate" class="block text-sm font-medium text-gray-700 mb-2">
                            Reseller Markup Rate (%)
                        </label>
                        <input 
                            type="number" 
                            id="reseller_markup_rate" 
                            name="reseller_markup_rate" 
                            value="{{ old('reseller_markup_rate', $currentPrices['reseller_markup_rate'] * 100) }}"
                            step="0.1" 
                            min="0" 
                            max="100"
                            class="form-input w-full text-lg"
                            placeholder="5.0"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Percentage markup when users buy tokens from reseller (e.g., 5.0 = 5%)
                        </p>
                        @error('reseller_markup_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        </div>
                    </div>
                </div>

                <!-- Auto-calculated preview -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-blue-800 mb-2">Auto-Calculated Prices (Preview)</h3>
                    <div class="grid md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-blue-600">RWAMP/USD:</span>
                            <span class="font-mono font-semibold text-blue-800 ml-2" id="preview_rwamp_usd">
                                ${{ number_format($currentPrices['rwamp_pkr'] / $currentPrices['usd_pkr'], 4) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-blue-600">USDT/USD:</span>
                            <span class="font-mono font-semibold text-blue-800 ml-2">
                                ${{ number_format($currentPrices['usdt_usd'], 4) }} (Live)
                            </span>
                        </div>
                        <div>
                            <span class="text-blue-600">BTC/USD:</span>
                            <span class="font-mono font-semibold text-blue-800 ml-2">
                                ${{ number_format($currentPrices['btc_usd'], 2) }} (Live)
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Important Notes</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Only RWAMP PKR price is required - all other prices are auto-calculated</li>
                                    <li>USDT and BTC prices are fetched live from CoinGecko API</li>
                                    <li>USDT price applies to all networks (ERC20, BEP20, TRC20) since it's the same token</li>
                                    <li>Prices are updated immediately and affect all new purchases</li>
                                    <li>Existing pending payments will use the price at the time of submission</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('dashboard.admin') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Update Prices</button>
                </div>
            </form>
        </div>

        <!-- Price Calculation Example -->
        <div class="bg-white rounded-xl shadow-xl p-6 mt-8">
            <h2 class="text-2xl font-montserrat font-bold mb-4">Price Calculation Example</h2>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-700 mb-2">
                    <strong>Example:</strong> User wants to buy 1,000 RWAMP tokens
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Token Quantity:</span>
                        <span class="font-mono font-semibold">1,000 RWAMP</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Token Price (PKR):</span>
                        <span class="font-mono font-semibold">₨{{ number_format($currentPrices['rwamp_pkr'], 2) }} per token</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Token Price (USD):</span>
                        <span class="font-mono font-semibold">${{ number_format($currentPrices['rwamp_usd'], 4) }} per token</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-600">Total PKR Value:</span>
                        <span class="font-mono font-semibold">₨{{ number_format(1000 * $currentPrices['rwamp_pkr'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total USD Value:</span>
                        <span class="font-mono font-semibold">${{ number_format(1000 * $currentPrices['rwamp_usd'], 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-600">USDT Price (USD):</span>
                        <span class="font-mono font-semibold">${{ number_format($currentPrices['usdt_usd'], 4) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-600">USDT Amount Required (ERC20/BEP20/TRC20):</span>
                        <span class="font-mono font-semibold text-primary">${{ number_format((1000 * $currentPrices['rwamp_usd']) / $currentPrices['usdt_usd'], 2) }} USDT</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">PKR Amount:</span>
                        <span class="font-mono font-semibold text-primary">₨{{ number_format((1000 * $currentPrices['rwamp_usd'] / $currentPrices['usdt_usd']) * $currentPrices['usdt_pkr'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-update preview when RWAMP PKR price changes
    document.getElementById('rwamp_pkr')?.addEventListener('input', function(e) {
        const rwampPkr = parseFloat(e.target.value) || 0;
        const usdPkr = {{ $currentPrices['usd_pkr'] }};
        if (rwampPkr > 0 && usdPkr > 0) {
            const rwampUsd = rwampPkr / usdPkr;
            document.getElementById('preview_rwamp_usd').textContent = '$' + rwampUsd.toFixed(4);
        }
    });
</script>
@endsection

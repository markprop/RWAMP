@extends('layouts.app')

@php
    use App\Helpers\PriceHelper;
    use Illuminate\Support\Facades\Storage;
    $rates = [
        'tokenUsd' => PriceHelper::getRwampUsdPrice(),
        'tokenPkr' => PriceHelper::getRwampPkrPrice(),
        'usdToPkr' => (float) config('crypto.rates.usd_pkr', 278),
        'usdtUsd' => PriceHelper::getUsdtUsdPrice(),
        'usdtPkr' => PriceHelper::getUsdtPkrPrice(),
        'btcUsd' => PriceHelper::getBtcUsdPrice(),
        'btcPkr' => PriceHelper::getBtcPkrPrice(),
    ];
    $wallets = [
        'TRC20' => (string) config('crypto.wallets.TRC20', ''),
        'ERC20' => (string) config('crypto.wallets.ERC20', ''),
        'BEP20' => (string) config('crypto.wallets.BEP20', config('crypto.wallets.ERC20', '')),
        'BTC' => (string) config('crypto.wallets.BTC', ''),
    ];
    $paymentsDisabled = (bool) (config('crypto.features.payments_enabled') === false);
@endphp

@section('content')
<div class="min-h-screen bg-white" x-data x-init="
    @if(request()->query('open') === 'purchase')
        $nextTick(() => window.dispatchEvent(new CustomEvent('open-purchase-modal')))
    @endif
" x-cloak>
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Reseller Dashboard</h1>
                    <p class="text-white/80">Welcome, {{ auth()->user()->name }}.</p>
                    @if(auth()->user()->referral_code)
                        <div class="mt-3 bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2 border border-white/20 inline-block">
                            <span class="text-xs text-white/70 uppercase tracking-wide">Your Referral Code:</span>
                            <span class="text-lg font-mono font-bold text-white ml-2">{{ auth()->user()->referral_code }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg px-6 py-3 border border-white/20">
                        <div class="text-xs text-white/70 uppercase tracking-wide mb-1">Token Balance</div>
                        <div class="text-2xl font-bold text-white">
                            {{ number_format($metrics['token_balance'] ?? 0, 0) }} RWAMP
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Metrics Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('reseller.users') }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block w-full text-left cursor-pointer hover:shadow-2xl transition-all duration-300">
                <div class="text-sm text-gray-600 mb-1">My Users</div>
                <div class="text-3xl font-bold text-primary">{{ $metrics['total_users'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-2">Users under you</div>
            </a>
            <a href="{{ route('reseller.payments') }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block w-full text-left cursor-pointer hover:shadow-2xl transition-all duration-300">
                <div class="text-sm text-gray-600 mb-1">Pending Payments</div>
                <div class="text-3xl font-bold text-yellow-600">{{ $metrics['pending_payments'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-2">Awaiting approval</div>
            </a>
            <a href="{{ route('reseller.payments', ['status' => 'approved']) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block w-full text-left cursor-pointer hover:shadow-2xl transition-all duration-300">
                <div class="text-sm text-gray-600 mb-1">Total Payments</div>
                <div class="text-3xl font-bold text-blue-600">{{ $metrics['total_payments'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-2">All time payments</div>
            </a>
            <a href="{{ route('reseller.transactions', ['type' => 'commission']) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block w-full text-left cursor-pointer hover:shadow-2xl transition-all duration-300">
                <div class="text-sm text-gray-600 mb-1">Total Commission</div>
                <div class="text-3xl font-bold text-green-600">{{ number_format($metrics['total_commission'] ?? 0, 0) }}</div>
                <div class="text-xs text-gray-500 mt-2">RWAMP tokens earned</div>
            </a>
        </div>

        <!-- Quick Actions Section -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <!-- Buy Coins Card -->
            <div class="bg-gradient-to-br from-primary to-red-600 rounded-xl shadow-xl p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-montserrat font-bold text-xl mb-2 text-white">Buy Coins</h3>
                        <p class="text-white/90 text-sm">Purchase RWAMP tokens directly from the platform</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <button 
                    @click="$dispatch('open-purchase-modal')" 
                    class="w-full bg-white text-primary hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 shadow-lg hover:shadow-xl"
                >
                    Buy Coins Now
                </button>
            </div>

            <!-- Sell Coins Card -->
            <div class="bg-gradient-to-br from-primary to-red-600 rounded-xl shadow-xl p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-montserrat font-bold text-xl mb-2 text-white">Sell Coins</h3>
                        <p class="text-white/90 text-sm">Sell RWAMP tokens to your users</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('reseller.sell') }}" class="inline-block w-full text-center bg-white text-primary hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 shadow-lg hover:shadow-xl">
                    Sell Coins Now
                </a>
            </div>
        </div>

        <!-- Coin Price Management Section -->
        <div id="coin-price" class="bg-white rounded-xl shadow-xl p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Coin Price Management</h3>
                    <p class="text-gray-600 text-sm mt-1">Set your custom price per coin for users buying from you</p>
                </div>
            </div>
            
            @php
                $defaultPrice = \App\Helpers\PriceHelper::getRwampPkrPrice();
                $resellerPrice = auth()->user()->coin_price;
            @endphp
            
            <!-- Price Display -->
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Super-Admin Price</span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">System Default</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">PKR {{ number_format($defaultPrice, 2) }}</div>
                    <p class="text-xs text-gray-500 mt-1">This is the default system price set by super-admin</p>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Your Custom Price</span>
                        @if($resellerPrice)
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Active</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Not Set</span>
                        @endif
                    </div>
                    <div class="text-2xl font-bold text-yellow-800">
                        @if($resellerPrice)
                            PKR {{ number_format($resellerPrice, 2) }}
                        @else
                            <span class="text-gray-400">Not Set</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        @if($resellerPrice)
                            Users will see this price when buying from you
                        @else
                            Default price (PKR {{ number_format($defaultPrice, 2) }}) will be used
                        @endif
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('reseller.update-coin-price') }}" class="max-w-md">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Your Custom Price per Coin (PKR)
                        <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                    </label>
                    <input 
                        type="number" 
                        name="coin_price" 
                        value="{{ old('coin_price', $resellerPrice ?? '') }}" 
                        step="0.01"
                        min="0.01"
                        class="form-input w-full"
                        placeholder="Enter your custom price per coin in PKR"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        @if($resellerPrice)
                            Current custom price: <strong>PKR {{ number_format($resellerPrice, 2) }}</strong> per coin
                        @else
                            Leave empty to use super-admin default price (PKR {{ number_format($defaultPrice, 2) }})
                        @endif
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        <strong>Note:</strong> You can only update your own custom price. Super-admin price cannot be changed from here.
                    </p>
                    @error('coin_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Update My Price</button>
                    @if($resellerPrice)
                        <button 
                            type="button" 
                            onclick="if(confirm('Are you sure you want to remove your custom price and use the default price?')) { document.getElementById('remove-price-form').submit(); }"
                            class="btn-secondary"
                        >
                            Remove Custom Price
                        </button>
                    @endif
                </div>
            </form>
            
            @if($resellerPrice)
                <form id="remove-price-form" method="POST" action="{{ route('reseller.update-coin-price') }}" class="hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="coin_price" value="">
                </form>
            @endif
        </div>

        <!-- My Users Section -->
        <div id="my-users" class="bg-white rounded-xl shadow-xl p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">My Users</h3>
                    <p class="text-gray-600 text-sm mt-1">Manage users who registered with your referral code</p>
                </div>
                <a href="{{ route('reseller.users') }}" class="btn-secondary">
                    View All Users
                </a>
            </div>
            
            @if($myUsers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Payments</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myUsers->take(5) as $user)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">{{ $user->name }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $user->email }}</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                            {{ $user->crypto_payments_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                            {{ $user->transactions_count ?? 0 }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($myUsers->count() > 5)
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600">Showing 5 of {{ $myUsers->total() }} users</p>
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No users yet. Share your referral code to get started!</p>
                </div>
            @endif
        </div>

        <!-- Pending Payments Section -->
        <div id="pending-payments" class="bg-white rounded-xl shadow-xl p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Pending Payments</h3>
                    <p class="text-gray-600 text-sm mt-1">Approve or reject crypto payments from your users</p>
                </div>
                <a href="{{ route('reseller.payments') }}" class="btn-secondary">
                    View All Payments
                </a>
            </div>
            
            @if($pendingPayments->count() > 0)
                <div class="space-y-4">
                    @foreach($pendingPayments as $payment)
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div class="flex-1">
                                    <div class="font-semibold">{{ $payment->user->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $payment->user->email }}</div>
                                    <div class="mt-2 flex gap-4 text-sm">
                                        <span><strong>Amount:</strong> {{ number_format($payment->token_amount, 0) }} RWAMP</span>
                                        <span><strong>Network:</strong> {{ strtoupper($payment->network) }}</span>
                                        <span><strong>TX Hash:</strong> <code class="text-xs">{{ substr($payment->tx_hash, 0, 20) }}...</code></span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button 
                                        onclick="approvePayment({{ $payment->id }})"
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                                    >
                                        Approve
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No pending payments at the moment.</p>
                </div>
            @endif
        </div>

        <!-- Recent Transactions -->
        <div id="transactions" class="bg-white rounded-xl shadow-xl p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Recent Transactions</h3>
                    <p class="text-gray-600 text-sm mt-1">Your recent token transactions</p>
                </div>
            </div>
            
            @if($recentTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Coins</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Coin Price</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Payment</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                                @php
                                    // Get display name based on transaction type
                                    $displayName = '—';
                                    $displayType = str_replace('_', ' ', $transaction->type);
                                    
                                    if ($transaction->type === 'reseller_sell') {
                                        // Reseller sold to user - show recipient (user)
                                        $displayName = $transaction->recipient ? $transaction->recipient->name . ' (ID: ' . $transaction->recipient->id . ')' : '—';
                                        $displayType = 'Sold to User';
                                    } elseif ($transaction->type === 'admin_transfer_credit') {
                                        // Admin sent to reseller - show sender (admin)
                                        $displayName = 'Admin';
                                        $displayType = 'Received from Admin';
                                    } elseif ($transaction->type === 'debit' || ($transaction->type === 'reseller_sell' && !$transaction->recipient)) {
                                        // Handle old debit/reseller_sell transactions
                                        // First try recipient_id if available
                                        if ($transaction->recipient_id) {
                                            $user = \App\Models\User::find($transaction->recipient_id);
                                            if ($user) {
                                                $displayName = $user->name . ' (ID: ' . $user->id . ')';
                                                $displayType = 'Sold to User';
                                            }
                                        } elseif ($transaction->reference) {
                                            // Try to extract user ID from reference
                                            // Reference format: SELL-USER-{id} or SELL-{id} or SELL-{id}-{timestamp}
                                            if (preg_match('/SELL(?:-USER)?-(\d+)(?:-|$)/', $transaction->reference, $matches)) {
                                                $userId = $matches[1];
                                                $user = \App\Models\User::find($userId);
                                                if ($user) {
                                                    $displayName = $user->name . ' (ID: ' . $user->id . ')';
                                                    $displayType = 'Sold to User';
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Calculate price per coin if missing (for old transactions)
                                    $pricePerCoin = $transaction->price_per_coin;
                                    $displayAmount = abs($transaction->amount);
                                    
                                    if (!$pricePerCoin && $transaction->total_price && $displayAmount > 0) {
                                        $pricePerCoin = $transaction->total_price / $displayAmount;
                                    } elseif (!$pricePerCoin && ($transaction->type === 'debit' || $transaction->type === 'reseller_sell') && $displayAmount > 0) {
                                        // For old transactions without price, try to get reseller's coin_price at transaction time
                                        // Or use current default price as fallback
                                        $reseller = auth()->user();
                                        $pricePerCoin = $reseller->coin_price ?? \App\Helpers\PriceHelper::getRwampPkrPrice();
                                    }
                                    
                                    // Determine signs for Amount and Coins based on transaction type
                                    // When reseller sells: Amount (PKR received) = +, Coins (RWAMP given) = -
                                    // When reseller receives from admin: Amount = +, Coins = +
                                    if ($transaction->type === 'reseller_sell' || ($transaction->type === 'debit' && $displayType === 'Sold to User')) {
                                        // Reseller sold coins - received money (+), gave coins (-)
                                        $amountSign = '+';
                                        $coinsSign = '-';
                                        $amountColor = 'text-green-600';
                                        $coinsColor = 'text-red-600';
                                    } elseif ($transaction->type === 'admin_transfer_credit') {
                                        // Admin sent to reseller - received both money and coins (+)
                                        $amountSign = '+';
                                        $coinsSign = '+';
                                        $amountColor = 'text-green-600';
                                        $coinsColor = 'text-green-600';
                                    } elseif (in_array($transaction->type, ['commission', 'credit'])) {
                                        // Credits - both positive
                                        $amountSign = '+';
                                        $coinsSign = '+';
                                        $amountColor = 'text-green-600';
                                        $coinsColor = 'text-green-600';
                                    } else {
                                        // Default: use transaction amount sign
                                        $amountSign = $transaction->amount >= 0 ? '+' : '-';
                                        $coinsSign = $transaction->amount >= 0 ? '+' : '-';
                                        $amountColor = $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600';
                                        $coinsColor = $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600';
                                    }
                                @endphp
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-700">
                                        {{ $displayName }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="capitalize text-xs">{{ $displayType }}</span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold {{ $amountColor }}">
                                        @php
                                            $calculatedTotalPrice = $transaction->total_price;
                                            if (!$calculatedTotalPrice && $pricePerCoin && $displayAmount > 0) {
                                                $calculatedTotalPrice = $pricePerCoin * $displayAmount;
                                            }
                                        @endphp
                                        @if($calculatedTotalPrice)
                                            {{ $amountSign }}{{ number_format($calculatedTotalPrice, 2) }} PKR
                                        @else
                                            {{ $amountSign }}0.00 PKR
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-semibold {{ $coinsColor }}">
                                        {{ $coinsSign }}{{ number_format($displayAmount, 0) }} RWAMP
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        @if($pricePerCoin)
                                            {{ number_format($pricePerCoin, 2) }} PKR
                                        @elseif($transaction->total_price && $displayAmount > 0)
                                            {{ number_format($transaction->total_price / $displayAmount, 2) }} PKR
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 text-xs">
                                        @if($transaction->payment_type)
                                            <div class="space-y-1">
                                                <div class="font-medium capitalize">{{ ucfirst($transaction->payment_type) }}</div>
                                                @if($transaction->payment_type === 'usdt' && $transaction->payment_hash)
                                                    <div class="text-gray-500 break-all">{{ substr($transaction->payment_hash, 0, 20) }}...</div>
                                                    <a href="javascript:void(0)" onclick="copyToClipboard('{{ $transaction->payment_hash }}')" class="text-blue-600 hover:underline text-xs">Copy Full Hash</a>
                                                @elseif($transaction->payment_type === 'bank' && $transaction->payment_receipt)
                                                    <a href="{{ Storage::url($transaction->payment_receipt) }}" target="_blank" class="text-blue-600 hover:underline text-xs">View Receipt</a>
                                                @elseif($transaction->payment_type === 'cash')
                                                    <div class="text-gray-500 text-xs">Cash Payment</div>
                                                @endif
                                                @if($transaction->display_payment_status)
                                                    <div class="text-xs">
                                                        <span class="px-1.5 py-0.5 rounded {{ $transaction->display_payment_status === 'verified' ? 'bg-green-100 text-green-800' : ($transaction->display_payment_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                            {{ ucfirst($transaction->display_payment_status) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-xs font-medium {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 text-xs">
                                        {{ $transaction->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No transactions yet.</p>
                </div>
            @endif
        </div>

        <!-- Full Users List (Hidden by default, shown when scrolled to) -->
        <div id="my-users-list" class="bg-white rounded-xl shadow-xl p-6 mb-8">
            <h3 class="font-montserrat font-bold text-xl mb-6">All My Users</h3>
            @if($myUsers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Phone</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Payments</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Transactions</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myUsers as $user)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">{{ $user->name }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $user->email }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $user->phone ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                            {{ $user->crypto_payments_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                            {{ $user->transactions_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $myUsers->links() }}
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No users yet. Share your referral code to get started!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Sell Coins Modal -->
<div id="sellModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold">Sell Coins to User</h3>
            <button onclick="closeSellModal()" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <form id="sellForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Select User</label>
                <select id="sellUserId" class="form-input" required>
                    <option value="">Choose a user...</option>
                    @foreach($myUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Amount (RWAMP)</label>
                <input type="number" id="sellAmount" class="form-input" min="1" step="1" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Your Email (for OTP)</label>
                <input type="email" id="sellEmail" value="{{ auth()->user()->email }}" class="form-input" required>
            </div>
            <div id="otpSection" class="hidden">
                <label class="block text-sm font-medium mb-2">OTP Code</label>
                <input type="text" id="sellOtp" class="form-input" maxlength="6" placeholder="Enter 6-digit OTP">
                <button type="button" onclick="sendOtp()" class="text-sm text-primary mt-1">Resend OTP</button>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeSellModal()" class="btn-secondary flex-1">Cancel</button>
                <button type="button" onclick="sendOtp()" id="sendOtpBtn" class="btn-primary flex-1">Send OTP</button>
                <button type="submit" id="submitSellBtn" class="btn-primary flex-1 hidden">Confirm Transfer</button>
            </div>
        </form>
    </div>
</div>

<script>
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        const offset = 100; // Offset for fixed header
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}

function openSellModal() {
    document.getElementById('sellModal').style.display = 'flex';
}

function closeSellModal() {
    document.getElementById('sellModal').style.display = 'none';
    document.getElementById('sellForm').reset();
    document.getElementById('otpSection').classList.add('hidden');
    document.getElementById('sendOtpBtn').classList.remove('hidden');
    document.getElementById('submitSellBtn').classList.add('hidden');
}

async function sendOtp() {
    const email = document.getElementById('sellEmail').value;
    if (!email) {
        alert('Please enter your email');
        return;
    }

    try {
        const response = await fetch('/api/reseller/send-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email })
        });

        const data = await response.json();
        if (data.success) {
            document.getElementById('otpSection').classList.remove('hidden');
            document.getElementById('sendOtpBtn').classList.add('hidden');
            document.getElementById('submitSellBtn').classList.remove('hidden');
            alert('OTP sent to your email');
        } else {
            alert(data.message || 'Failed to send OTP');
        }
    } catch (error) {
        alert('Error sending OTP');
    }
}

document.getElementById('sellForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const userId = document.getElementById('sellUserId').value;
    const amount = document.getElementById('sellAmount').value;
    const otp = document.getElementById('sellOtp').value;
    const email = document.getElementById('sellEmail').value;

    if (!userId || !amount || !otp || !email) {
        alert('Please fill all fields');
        return;
    }

    try {
        const response = await fetch('/api/reseller/sell', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_id: userId, amount, otp, email })
        });

        const data = await response.json();
        if (data.success) {
            alert('Tokens transferred successfully!');
            closeSellModal();
            location.reload();
        } else {
            alert(data.message || 'Transfer failed');
        }
    } catch (error) {
        alert('Error processing transfer');
    }
});

async function approvePayment(paymentId) {
    if (!confirm('Are you sure you want to approve this payment?')) return;

    try {
        const response = await fetch(`/api/reseller/crypto-payments/${paymentId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        if (data.success) {
            alert('Payment approved successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to approve payment');
        }
    } catch (error) {
        alert('Error approving payment');
    }
}
</script>

    <!-- Contact Us Section -->
    <div class="max-w-7xl mx-auto px-4 pb-10">
        <div class="bg-gradient-to-br from-primary to-red-600 rounded-xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-xl mb-2 text-white">Need Help?</h3>
                    <p class="text-white text-sm opacity-95">Contact our support team for assistance</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('contact') }}" class="inline-block w-full sm:w-auto text-center bg-white text-primary hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 shadow-lg hover:shadow-xl">
                Contact Us
            </a>
        </div>
    </div>

    <!-- Purchase Modal -->
    @include('components.purchase-modal', ['rates' => $rates, 'wallets' => $wallets, 'paymentsDisabled' => $paymentsDisabled])

    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Transaction hash copied to clipboard!');
        }).catch(() => {
            alert('Failed to copy. Hash: ' + text);
        });
    }
    </script>
@endsection

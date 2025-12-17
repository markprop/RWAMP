@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.reseller-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Transaction Details</h1>
                        <p class="text-gray-500 text-sm mt-1.5">View complete transaction information</p>
                    </div>
                    <div class="flex gap-3">
                        <a 
                            href="{{ route('reseller.transactions') }}" 
                            class="btn-secondary inline-flex items-center gap-2"
                        >
                            ← Back to Transactions
                        </a>
                        <button 
                            onclick="shareTransactionDetails()" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm inline-flex items-center gap-2"
                        >
                            📋 <span>Share Details</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <!-- Transaction Information Card -->
            <div class="bg-white rounded-xl shadow-xl p-6 space-y-8">
                <div data-transaction-details style="display: none;">
Transaction Details:
Type: {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
Amount: {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} RWAMP
Status: {{ ucfirst($transaction->status) }}
Reference: {{ $transaction->reference ?? 'N/A' }}
Seller: {{ optional($transaction->sender)->name ?? 'N/A' }} (ID: {{ optional($transaction->sender)->id ?? 'N/A' }})
Buyer: {{ optional($transaction->recipient)->name ?? 'N/A' }} (ID: {{ optional($transaction->recipient)->id ?? 'N/A' }})
Price per Coin: {{ $transaction->price_per_coin ? 'PKR ' . number_format($transaction->price_per_coin, 4) : 'N/A' }}
Total Price: {{ $transaction->total_price ? 'PKR ' . number_format($transaction->total_price, 2) : 'N/A' }}
Date: {{ $transaction->created_at->format('F d, Y H:i:s') }}
Last Updated: {{ $transaction->updated_at->format('F d, Y H:i:s') }}

Shared from RWAMP Reseller Dashboard
                </div>

                <!-- Transaction summary -->
                <div>
                    <h2 class="text-2xl font-bold mb-6">Transaction Information</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Transaction Type</label>
                            <p class="text-lg font-semibold capitalize">{{ str_replace('_', ' ', $transaction->type) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Amount</label>
                            <p class="text-lg font-semibold {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} RWAMP
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                            <p class="text-lg">
                                <span class="px-3 py-1 rounded text-sm font-semibold 
                                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Reference</label>
                            <p class="text-sm font-mono text-gray-700">{{ $transaction->reference ?? '—' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Transaction Date</label>
                            <p class="text-lg">{{ $transaction->created_at->format('F d, Y H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Last Updated</label>
                            <p class="text-lg">{{ $transaction->updated_at->format('F d, Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Parties involved -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Parties</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Seller</p>
                            <p class="text-sm font-bold text-gray-900">
                                {{ optional($transaction->sender)->name ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                User ID: <span class="font-mono">{{ optional($transaction->sender)->id ?? 'N/A' }}</span>
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Buyer</p>
                            <p class="text-sm font-bold text-gray-900">
                                {{ optional($transaction->recipient)->name ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                User ID: <span class="font-mono">{{ optional($transaction->recipient)->id ?? 'N/A' }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pricing details -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Price per Coin</label>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($transaction->price_per_coin)
                                    PKR {{ number_format($transaction->price_per_coin, 4) }}
                                @else
                                    <span class="text-gray-500 text-sm">Not recorded</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Total Sale Value</label>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($transaction->total_price)
                                    PKR {{ number_format($transaction->total_price, 2) }}
                                @else
                                    <span class="text-gray-500 text-sm">Not recorded</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<script>
function shareTransactionDetails() {
    const details = `Transaction Details:
Type: {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
Amount: {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} RWAMP
Status: {{ ucfirst($transaction->status) }}
Reference: {{ $transaction->reference ?? 'N/A' }}
Date: {{ $transaction->created_at->format('F d, Y H:i:s') }}
Last Updated: {{ $transaction->updated_at->format('F d, Y H:i:s') }}

Shared from RWAMP Reseller Dashboard`;

    navigator.clipboard.writeText(details).then(() => {
        alert('Transaction details copied to clipboard!');
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = details;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Transaction details copied to clipboard!');
    });
}
</script>
@endsection


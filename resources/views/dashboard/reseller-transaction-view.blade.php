@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.reseller-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Transaction Details</h1>
                        <p class="text-gray-500 text-sm mt-1.5">View complete transaction information</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="shareTransactionDetails()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                            📋 Share Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">
        <!-- Transaction Information Card -->
        <div class="bg-white rounded-xl shadow-xl p-6">
            <div data-transaction-details style="display: none;">
Transaction Details:
Type: {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
Amount: {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} RWAMP
Status: {{ ucfirst($transaction->status) }}
Reference: {{ $transaction->reference ?? 'N/A' }}
Date: {{ $transaction->created_at->format('F d, Y H:i:s') }}
Last Updated: {{ $transaction->updated_at->format('F d, Y H:i:s') }}

Shared from RWAMP Reseller Dashboard
            </div>
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
                    <p class="text-lg font-mono text-sm">{{ $transaction->reference ?? '—' }}</p>
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


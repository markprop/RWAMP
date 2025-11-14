@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Transaction Details</h1>
                    <p class="text-white/80">View complete transaction information</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="shareTransactionDetails()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        📋 Share Details
                    </button>
                    <a href="{{ route('reseller.transactions') }}" class="btn-secondary">
                        ← Back to Transactions
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
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


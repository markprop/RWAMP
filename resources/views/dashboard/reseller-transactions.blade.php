@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Transactions</h1>
                    <p class="text-white/80">View all your token transactions</p>
                </div>
                <a href="{{ route('dashboard.reseller') }}" class="btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <form method="GET" action="{{ route('reseller.transactions') }}" class="flex gap-4 flex-wrap">
                <div class="flex-1 min-w-[250px]">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search by reference or type..." 
                        class="form-input w-full"
                    />
                </div>
                <select name="type" class="form-input">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="commission" {{ request('type') === 'commission' ? 'selected' : '' }}>Commission</option>
                    <option value="crypto_purchase" {{ request('type') === 'crypto_purchase' ? 'selected' : '' }}>Crypto Purchase</option>
                </select>
                <select name="status" class="form-input">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <button type="submit" class="btn-primary">Filter</button>
                @if(request('search') || request('type') || request('status'))
                    <a href="{{ route('reseller.transactions') }}" class="btn-secondary">Clear</a>
                @endif
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Type</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Reference</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-4 px-6 capitalize">{{ str_replace('_', ' ', $transaction->type) }}</td>
                                    <td class="py-4 px-6 font-semibold {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} RWAMP
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded text-sm font-semibold 
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">{{ $transaction->reference ?? '—' }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-600">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-2">
                                            <a href="{{ route('reseller.transactions.view', $transaction) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                View
                                            </a>
                                            <button onclick="shareTransactionDetails({{ $transaction->id }})" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                                Share
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg">No transactions found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
async function shareTransactionDetails(transactionId) {
    try {
        const response = await fetch(`/dashboard/reseller/transactions/${transactionId}`);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const details = doc.querySelector('[data-transaction-details]')?.textContent || 'Transaction details not available';
        
        await navigator.clipboard.writeText(details);
        alert('Transaction details copied to clipboard!');
    } catch (error) {
        console.error('Error sharing:', error)
        alert('Failed to copy details. Please try again.');
    }
}
</script>
@endsection


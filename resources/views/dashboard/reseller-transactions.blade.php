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
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Transactions</h1>
                        <p class="text-gray-500 text-sm mt-1.5">View all your token transactions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6" x-data="resellerTransactionsFilters()">
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-xl p-5 sm:p-6 mb-6 border border-gray-100">
                <form 
                    method="GET" 
                    action="{{ route('reseller.transactions') }}" 
                    class="grid grid-cols-1 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto] gap-3 md:gap-4 items-stretch"
                    @submit.prevent="submit($event)"
                >
                    <div class="w-full">
                        <label for="transactions-search" class="sr-only">Search transactions</label>
                        <input 
                            id="transactions-search"
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search by reference or type..." 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Search transactions"
                        />
                    </div>
                    <div class="w-full">
                        <label for="transactions-type" class="sr-only">Type</label>
                        <select 
                            id="transactions-type"
                            name="type" 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Filter by transaction type"
                        >
                            <option value="">All Types</option>
                            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="commission" {{ request('type') === 'commission' ? 'selected' : '' }}>Commission</option>
                            <option value="crypto_purchase" {{ request('type') === 'crypto_purchase' ? 'selected' : '' }}>Crypto Purchase</option>
                        </select>
                    </div>
                    <div class="w-full">
                        <label for="transactions-status" class="sr-only">Status</label>
                        <select 
                            id="transactions-status"
                            name="status" 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Filter by transaction status"
                        >
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <button 
                        type="submit" 
                        class="btn-primary w-full md:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold"
                        x-bind:disabled="isLoading"
                    >
                        <span x-show="!isLoading">Filter</span>
                        <span x-show="isLoading">Filtering…</span>
                    </button>
                    @if(request('search') || request('type') || request('status'))
                        <a 
                            href="{{ route('reseller.transactions') }}" 
                            class="btn-secondary w-full md:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold text-center"
                        >
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Transactions Table -->
            <div id="resellerTransactionsTable" class="bg-white rounded-xl shadow-xl overflow-hidden animate-fadeInUp">
            @if($transactions->count() > 0)
                <div class="rw-table-scroll overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Type</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Reference</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 sm:px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 sm:px-6 capitalize">{{ str_replace('_', ' ', $transaction->type) }}</td>
                                    <td class="py-3 px-4 sm:px-6 font-semibold {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} RWAMP
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <span class="px-3 py-1 rounded text-sm font-semibold 
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 text-xs sm:text-sm text-gray-600 break-all">{{ $transaction->reference ?? '—' }}</td>
                                    <td class="py-3 px-4 sm:px-6 text-xs sm:text-sm text-gray-600">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="flex flex-wrap gap-2">
                                            @if($transaction && $transaction->id)
                                                @php
                                                    // Use ULID if available, otherwise fallback to ID
                                                    $transactionParam = $transaction->ulid ?? $transaction->id;
                                                @endphp
                                                <a href="{{ route('reseller.transactions.view', $transactionParam) }}" class="btn-primary btn-small">
                                                    View
                                                </a>
                                                <button type="button" onclick="shareTransactionDetails({{ $transaction->id }})" class="btn-secondary btn-small">
                                                    Share
                                                </button>
                                            @else
                                                <span class="text-gray-400 text-sm">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 sm:p-6 border-t">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="text-center py-10 sm:py-12 text-gray-500">
                    <p class="text-base sm:text-lg">No transactions found.</p>
                </div>
            @endif
            </div>

            <!-- Toast -->
            <div
                x-show="toast.open"
                x-transition
                class="fixed bottom-4 right-4 z-50 max-w-sm w-full px-4"
                role="alert"
                aria-live="assertive"
            >
                <div
                    class="rounded-lg shadow-lg px-4 py-3 text-sm"
                    :class="toast.type === 'error'
                        ? 'bg-red-600 text-white'
                        : 'bg-gray-900 text-white'"
                >
                    <span x-text="toast.message"></span>
                </div>
            </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('resellerTransactionsFilters', () => ({
        isLoading: false,
        toast: { open: false, message: '', type: 'info' },

        showToast(message, type = 'info') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.open = true;
            setTimeout(() => { this.toast.open = false }, 3000);
        },

        async submit(event) {
            this.isLoading = true;
            try {
                const form = event.target;
                const params = new URLSearchParams(new FormData(form)).toString();
                const url = form.action + (params ? ('?' + params) : '');

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Server error: ' + response.status);

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#resellerTransactionsTable');
                const current = document.querySelector('#resellerTransactionsTable');

                if (incoming && current) {
                    current.innerHTML = incoming.innerHTML;
                    current.classList.remove('animate-fadeInUp');
                    void current.offsetWidth;
                    current.classList.add('animate-fadeInUp');
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', url);
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load transactions. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        }
    }));
});

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


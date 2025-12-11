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
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Buy Transactions</h1>
                        <p class="text-gray-500 text-sm mt-1.5">View all your coin purchase transactions and their status</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6" x-data="buyTransactionsFilters()">
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-xl p-5 sm:p-6 mb-6 border border-gray-100">
                <form 
                    method="GET" 
                    action="{{ route('reseller.buy-transactions') }}" 
                    class="grid grid-cols-1 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto] gap-3 md:gap-4 items-stretch"
                    @submit.prevent="submit($event)"
                >
                    <div class="w-full">
                        <label for="buy-transactions-search" class="sr-only">Search transactions</label>
                        <input 
                            id="buy-transactions-search"
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search by TX hash, network, or amount..." 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Search buy transactions"
                        />
                    </div>
                    <div class="w-full">
                        <label for="buy-transactions-network" class="sr-only">Network</label>
                        <select 
                            id="buy-transactions-network"
                            name="network" 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Filter by network"
                        >
                            <option value="">All Networks</option>
                            <option value="TRC20" {{ request('network') === 'TRC20' ? 'selected' : '' }}>TRC20</option>
                            <option value="ERC20" {{ request('network') === 'ERC20' ? 'selected' : '' }}>ERC20</option>
                            <option value="BEP20" {{ request('network') === 'BEP20' ? 'selected' : '' }}>BEP20</option>
                            <option value="BTC" {{ request('network') === 'BTC' ? 'selected' : '' }}>BTC</option>
                            <option value="BNB" {{ request('network') === 'BNB' ? 'selected' : '' }}>BNB</option>
                        </select>
                    </div>
                    <div class="w-full">
                        <label for="buy-transactions-status" class="sr-only">Status</label>
                        <select 
                            id="buy-transactions-status"
                            name="status" 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Filter by status"
                        >
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending / Waiting for Admin Approval</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                    @if(request('search') || request('network') || request('status'))
                        <a 
                            href="{{ route('reseller.buy-transactions') }}" 
                            class="btn-secondary w-full md:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold text-center"
                        >
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Transactions Table -->
            <div id="buyTransactionsTable" class="bg-white rounded-xl shadow-xl overflow-hidden animate-fadeInUp">
            @if($payments->count() > 0)
                <div class="rw-table-scroll overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Transaction Hash</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Network</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Token Amount</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Payment Amount</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 sm:px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                @php
                                    $transaction = $transactions[$payment->tx_hash] ?? null;
                                    $displayStatus = $payment->status === 'pending' ? 'Waiting for Admin Approval' : ucfirst($payment->status);
                                    $statusColor = $payment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                  ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                @endphp
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs sm:text-sm text-gray-900 font-mono break-all">{{ Str::limit($payment->tx_hash, 20) }}</span>
                                            <button 
                                                onclick="copyToClipboard('{{ $payment->tx_hash }}')"
                                                class="text-gray-400 hover:text-primary transition-colors"
                                                title="Copy transaction hash"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                            {{ $payment->network === 'TRC20' ? 'bg-green-100 text-green-800' : 
                                               ($payment->network === 'ERC20' ? 'bg-blue-100 text-blue-800' : 
                                               ($payment->network === 'BEP20' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($payment->network === 'BTC' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'))) }}">
                                            {{ $payment->network }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 font-semibold text-gray-900">
                                        {{ number_format((float)$payment->token_amount, 0) }} RWAMP
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="text-sm">
                                            <div class="font-semibold text-gray-900">${{ number_format((float)$payment->usd_amount, 2) }} USD</div>
                                            <div class="text-gray-600 text-xs">PKR {{ number_format((float)$payment->pkr_amount, 2) }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                            {{ $displayStatus }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 text-xs sm:text-sm text-gray-600">
                                        {{ $payment->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('reseller.buy-transactions.view', $payment) }}" class="btn-primary btn-small">
                                                View Details
                                            </a>
                                            @if($payment->tx_hash)
                                                <a 
                                                    href="https://{{ $payment->network === 'TRC20' ? 'tronscan.org' : ($payment->network === 'ERC20' ? 'etherscan.io' : ($payment->network === 'BEP20' ? 'bscscan.com' : 'blockchain.com')) }}/tx/{{ $payment->tx_hash }}" 
                                                    target="_blank"
                                                    class="btn-secondary btn-small"
                                                >
                                                    View on Explorer
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 sm:p-6 border-t">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-10 sm:py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-base sm:text-lg font-semibold mb-2">No buy transactions found.</p>
                    <p class="text-sm text-gray-400">Your coin purchase transactions will appear here once you make a purchase.</p>
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
    Alpine.data('buyTransactionsFilters', () => ({
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
                const incoming = doc.querySelector('#buyTransactionsTable');
                const current = document.querySelector('#buyTransactionsTable');

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

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50';
        toast.textContent = 'Transaction hash copied to clipboard!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>
@endsection


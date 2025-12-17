@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="userHistoryFilters()" x-cloak>
    <!-- Sidebar -->
    @include('components.investor-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Your Purchase History</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Only shows activity for {{ auth()->user()->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-10">
        <!-- Payment Submissions -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Payment Submissions</h3>
                    <p class="text-gray-600 text-sm">Submission price is stored to preserve your exact buy rate.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rw-badge">{{ $payments->total() }} total</span>
                    <a href="{{ route('payments.submit') }}" class="btn-secondary btn-small">
                        Submit Offline Payment Receipt
                    </a>
                </div>
            </div>

            <!-- Payment Filters -->
            <div class="mb-4 pb-4 border-b">
                <form 
                    method="GET" 
                    action="{{ route('user.history') }}" 
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4"
                    x-ref="paymentsForm"
                    @submit.prevent="submitPayments($refs.paymentsForm)"
                >
                    <input type="hidden" name="transactions_page" value="{{ request('transactions_page') }}">
                    <input type="hidden" name="transaction_search" value="{{ request('transaction_search') }}">
                    <input type="hidden" name="transaction_type" value="{{ request('transaction_type') }}">
                    <input type="hidden" name="transaction_status" value="{{ request('transaction_status') }}">
                    <input type="hidden" name="transaction_sort" value="{{ request('transaction_sort') }}">
                    <input type="hidden" name="transaction_dir" value="{{ request('transaction_dir') }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input 
                            type="text" 
                            name="payment_search" 
                            value="{{ request('payment_search') }}" 
                            placeholder="TX hash"
                            class="form-input w-full"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="payment_status" class="form-input w-full">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('payment_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('payment_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Network</label>
                        <select name="payment_network" class="form-input w-full">
                            <option value="">All Networks</option>
                            <option value="TRC20" {{ request('payment_network') === 'TRC20' ? 'selected' : '' }}>TRC20</option>
                            <option value="ERC20" {{ request('payment_network') === 'ERC20' ? 'selected' : '' }}>ERC20</option>
                            <option value="BEP20" {{ request('payment_network') === 'BEP20' ? 'selected' : '' }}>BEP20</option>
                            <option value="BTC" {{ request('payment_network') === 'BTC' ? 'selected' : '' }}>BTC</option>
                            <option value="BNB" {{ request('payment_network') === 'BNB' ? 'selected' : '' }}>BNB</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button 
                            type="submit" 
                            class="btn-primary flex-1 min-h-[44px] flex items-center justify-center text-sm font-semibold"
                            x-bind:disabled="isLoadingPayments"
                        >
                            <span x-show="!isLoadingPayments">Filter</span>
                            <span x-show="isLoadingPayments">Filtering…</span>
                        </button>
                        <button 
                            type="button" 
                            @click="clearPayments" 
                            class="btn-secondary min-h-[44px] flex items-center justify-center text-sm font-semibold"
                        >
                            Clear
                        </button>
                    </div>
                </form>
            </div>

            <div id="userPaymentsTable" class="rw-table-scroll overflow-x-auto">
                <table class="min-w-[720px] whitespace-nowrap text-sm w-full">
                    <thead>
                        <tr class="text-left text-gray-600 border-b">
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'created_at', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Date @if(request('payment_sort') === 'created_at') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'token_amount', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Tokens @if(request('payment_sort') === 'token_amount') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">Price</th>
                            <th class="py-3 pr-6">Total</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'network', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Network @if(request('payment_sort') === 'network') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">TX Hash</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'status', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Status @if(request('payment_sort') === 'status') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr class="border-b">
                                <td class="py-3 pr-6">{{ $p->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3 pr-6">{{ number_format($p->token_amount) }}</td>
                                <td class="py-3 pr-6">
                                    @include('components.price-tag', [
                                        'pkr' => $p->coin_price_rs ?? ($p->token_amount ? ($p->pkr_amount / $p->token_amount) : 0),
                                        'size' => 'small'
                                    ])
                                </td>
                                <td class="py-3 pr-6">
                                    @include('components.price-tag', [
                                        'pkr' => ($p->coin_price_rs ?? 0) * $p->token_amount,
                                        'size' => 'small'
                                    ])
                                </td>
                                <td class="py-3 pr-6"><span class="rw-badge">{{ $p->network }}</span></td>
                                <td class="py-3 pr-6"><span class="break-all font-mono text-xs">{{ \Illuminate\Support\Str::limit($p->tx_hash, 30) }}</span></td>
                                <td class="py-3 pr-6">
                                    @php
                                        $statusLower = strtolower($p->status ?? '');
                                        if ($statusLower === 'pending') {
                                            $statusLabel = 'Wait for Admin Approval';
                                        } elseif ($statusLower === 'approved') {
                                            $statusLabel = 'Approved';
                                        } elseif ($statusLower === 'rejected') {
                                            $statusLabel = 'Rejected';
                                        } else {
                                            $statusLabel = ucfirst($p->status ?? 'Pending');
                                        }
                                        
                                        if ($statusLower === 'approved') {
                                            $statusClass = 'bg-green-100 text-green-800';
                                        } elseif ($statusLower === 'rejected') {
                                            $statusClass = 'bg-red-100 text-red-800';
                                        } else {
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                        }
                                    @endphp
                                    <span class="rw-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-6 text-center text-gray-500">No submissions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $payments->links() }}</div>
        </div>

        <!-- Bank Transfer Receipts (manual submissions) -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Bank Transfer Receipts</h3>
                    <p class="text-gray-600 text-sm">Offline payments submitted with receipts.</p>
                </div>
                <span class="rw-badge">{{ $bankSubmissions->total() }} total</span>
            </div>

            <div class="rw-table-scroll overflow-x-auto">
                <table class="min-w-[720px] whitespace-nowrap text-sm w-full">
                    <thead>
                        <tr class="text-left text-gray-600 border-b">
                            <th class="py-3 pr-6">Date</th>
                            <th class="py-3 pr-6">Tokens</th>
                            <th class="py-3 pr-6">Amount</th>
                            <th class="py-3 pr-6">Reseller</th>
                            <th class="py-3 pr-6">Coin Price (Rs)</th>
                            <th class="py-3 pr-6">Status</th>
                            <th class="py-3 pr-6">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bankSubmissions as $s)
                            <tr class="border-b">
                                <td class="py-3 pr-6">{{ $s->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3 pr-6">{{ number_format($s->token_amount) }} RWAMP</td>
                                <td class="py-3 pr-6">{{ $s->currency }} {{ number_format($s->fiat_amount, 2) }}</td>
                                <td class="py-3 pr-6">
                                    @if($s->recipient_type === 'reseller' && $s->recipient)
                                        {{ $s->recipient->name }}
                                    @else
                                        Admin
                                    @endif
                                </td>
                                <td class="py-3 pr-6">
                                    @php
                                        $pricePerCoin = $s->token_amount ? ($s->fiat_amount / $s->token_amount) : 0;
                                    @endphp
                                    PKR {{ number_format($pricePerCoin, 2) }}
                                </td>
                                <td class="py-3 pr-6">
                                    @php
                                        $statusLower = strtolower($s->status);
                                        $cls = $statusLower === 'approved'
                                            ? 'bg-green-100 text-green-800'
                                            : ($statusLower === 'rejected'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800');
                                    @endphp
                                    <span class="rw-badge {{ $cls }}">{{ ucfirst($s->status) }}</span>
                                </td>
                                <td class="py-3 pr-6 text-xs text-gray-600">
                                    {{ $s->admin_notes ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-gray-500 text-sm">
                                    No bank receipts submitted yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $bankSubmissions->links() }}</div>
        </div>

        <!-- Token Transactions -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Token Transactions</h3>
                    <p class="text-gray-600 text-sm">All credits/debits affecting your balance.</p>
                </div>
                <span class="rw-badge">{{ $transactions->total() }} total</span>
            </div>

            <!-- Transaction Filters -->
            <div class="mb-4 pb-4 border-b">
                <form 
                    method="GET" 
                    action="{{ route('user.history') }}" 
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4"
                    x-ref="transactionsForm"
                    @submit.prevent="submitTransactions($refs.transactionsForm)"
                >
                    <input type="hidden" name="payments_page" value="{{ request('payments_page') }}">
                    <input type="hidden" name="payment_search" value="{{ request('payment_search') }}">
                    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                    <input type="hidden" name="payment_network" value="{{ request('payment_network') }}">
                    <input type="hidden" name="payment_sort" value="{{ request('payment_sort') }}">
                    <input type="hidden" name="payment_dir" value="{{ request('payment_dir') }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input 
                            type="text" 
                            name="transaction_search" 
                            value="{{ request('transaction_search') }}" 
                            placeholder="Type or reference"
                            class="form-input w-full"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="transaction_type" class="form-input w-full">
                            <option value="">All Types</option>
                            <option value="crypto_purchase" {{ request('transaction_type') === 'crypto_purchase' ? 'selected' : '' }}>Crypto Purchase</option>
                            <option value="token_credit" {{ request('transaction_type') === 'token_credit' ? 'selected' : '' }}>Token Credit</option>
                            <option value="token_debit" {{ request('transaction_type') === 'token_debit' ? 'selected' : '' }}>Token Debit</option>
                            <option value="credit" {{ request('transaction_type') === 'credit' ? 'selected' : '' }}>Credit</option>
                            <option value="debit" {{ request('transaction_type') === 'debit' ? 'selected' : '' }}>Debit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="transaction_status" class="form-input w-full">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('transaction_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('transaction_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('transaction_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button 
                            type="submit" 
                            class="btn-primary flex-1 min-h-[44px] flex items-center justify-center text-sm font-semibold"
                            x-bind:disabled="isLoadingTransactions"
                        >
                            <span x-show="!isLoadingTransactions">Filter</span>
                            <span x-show="isLoadingTransactions">Filtering…</span>
                        </button>
                        <button 
                            type="button" 
                            @click="clearTransactions" 
                            class="btn-secondary min-h-[44px] flex items-center justify-center text-sm font-semibold"
                        >
                            Clear
                        </button>
                    </div>
                </form>
            </div>

            <div id="userTransactionsTable" class="rw-table-scroll overflow-x-auto">
                <table class="min-w-[720px] whitespace-nowrap text-sm w-full">
                    <thead>
                        <tr class="text-left text-gray-600 border-b">
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'created_at', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Date @if(request('transaction_sort') === 'created_at') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'type', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Type @if(request('transaction_sort') === 'type') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'amount', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Amount @if(request('transaction_sort') === 'amount') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">Price (Rs)</th>
                            <th class="py-3 pr-6">Reference</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'status', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Status @if(request('transaction_sort') === 'status') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr class="border-b">
                                <td class="py-3 pr-6">{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3 pr-6">{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
                                <td class="py-3 pr-6 font-semibold">{{ number_format($t->amount) }}</td>
                                <td class="py-3 pr-6">
                                    @if($t->price_per_coin && $t->price_per_coin > 0)
                                        {{ number_format($t->price_per_coin, 2) }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-6">
                                    @php
                                        $ref = trim((string)($t->reference ?? ''));
                                        if ($ref === '') {
                                            $ref = $t->type === 'credit' ? 'Token credit (wallet purchase)' : ($t->type === 'debit' ? 'Token debit' : 'Transaction');
                                        } else {
                                            // If reference is a transaction hash, truncate it for display
                                            if (strlen($ref) > 24 && (substr($ref, 0, 2) === '0x' || strlen($ref) === 64)) {
                                                $ref = substr($ref, 0, 12) . '...' . substr($ref, -8);
                                            }
                                        }
                                    @endphp
                                    <span class="break-all font-mono text-xs">{{ $ref }}</span>
                                </td>
                                <td class="py-3 pr-6">
                                    @if($t->status === 'completed')
                                        <span class="rw-badge bg-green-100 text-green-800">Completed</span>
                                    @elseif($t->status === 'failed')
                                        <span class="rw-badge bg-red-100 text-red-800">Failed</span>
                                    @else
                                        <span class="rw-badge bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-gray-500">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>

        <!-- Buy Request Submissions -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Request Submission History</h3>
                    <p class="text-gray-600 text-sm">Your buy requests submitted to resellers.</p>
                </div>
                <span class="rw-badge">{{ $buyRequests->total() }} total</span>
            </div>

            <!-- Buy Request Filters -->
            <div class="mb-4 pb-4 border-b">
                <form 
                    method="GET" 
                    action="{{ route('user.history') }}" 
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4"
                    x-ref="buyRequestsForm"
                    @submit.prevent="submitBuyRequests($refs.buyRequestsForm)"
                >
                    <input type="hidden" name="payments_page" value="{{ request('payments_page') }}">
                    <input type="hidden" name="payment_search" value="{{ request('payment_search') }}">
                    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                    <input type="hidden" name="payment_network" value="{{ request('payment_network') }}">
                    <input type="hidden" name="payment_sort" value="{{ request('payment_sort') }}">
                    <input type="hidden" name="payment_dir" value="{{ request('payment_dir') }}">
                    <input type="hidden" name="transactions_page" value="{{ request('transactions_page') }}">
                    <input type="hidden" name="transaction_search" value="{{ request('transaction_search') }}">
                    <input type="hidden" name="transaction_type" value="{{ request('transaction_type') }}">
                    <input type="hidden" name="transaction_status" value="{{ request('transaction_status') }}">
                    <input type="hidden" name="transaction_sort" value="{{ request('transaction_sort') }}">
                    <input type="hidden" name="transaction_dir" value="{{ request('transaction_dir') }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input 
                            type="text" 
                            name="buy_request_search" 
                            value="{{ request('buy_request_search') }}" 
                            placeholder="Reseller name or email"
                            class="form-input w-full"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="buy_request_status" class="form-input w-full">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('buy_request_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('buy_request_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('buy_request_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="completed" {{ request('buy_request_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button 
                            type="submit" 
                            class="btn-primary flex-1 min-h-[44px] flex items-center justify-center text-sm font-semibold"
                            x-bind:disabled="isLoadingBuyRequests"
                        >Filter</button>
                        <button type="button" @click="clearBuyRequests" class="btn-secondary min-h-[44px] flex items-center justify-center text-sm font-semibold">Clear</button>
                    </div>
                </form>
            </div>

            <div id="userBuyRequestsTable" class="rw-table-scroll overflow-x-auto">
                <table class="min-w-[720px] whitespace-nowrap text-sm w-full">
                    <thead>
                        <tr class="text-left text-gray-600 border-b">
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['buy_request_sort', 'buy_request_dir', 'buy_requests_page']), ['buy_request_sort' => 'created_at', 'buy_request_dir' => request('buy_request_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Date @if(request('buy_request_sort') === 'created_at') {{ request('buy_request_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">Reseller Name</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['buy_request_sort', 'buy_request_dir', 'buy_requests_page']), ['buy_request_sort' => 'coin_quantity', 'buy_request_dir' => request('buy_request_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Coin Quantity @if(request('buy_request_sort') === 'coin_quantity') {{ request('buy_request_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">Coin Price (Rs)</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['buy_request_sort', 'buy_request_dir', 'buy_requests_page']), ['buy_request_sort' => 'total_amount', 'buy_request_dir' => request('buy_request_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Total Price (Rs) @if(request('buy_request_sort') === 'total_amount') {{ request('buy_request_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['buy_request_sort', 'buy_request_dir', 'buy_requests_page']), ['buy_request_sort' => 'status', 'buy_request_dir' => request('buy_request_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Status @if(request('buy_request_sort') === 'status') {{ request('buy_request_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($buyRequests as $br)
                            <tr class="border-b">
                                <td class="py-3 pr-6">{{ $br->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3 pr-6">
                                    @if($br->reseller)
                                        <div class="font-semibold">{{ $br->reseller->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $br->reseller->email }}</div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-6 font-semibold">{{ number_format($br->coin_quantity, 0) }} RWAMP</td>
                                <td class="py-3 pr-6">PKR {{ number_format($br->coin_price, 2) }}</td>
                                <td class="py-3 pr-6 font-semibold">PKR {{ number_format($br->total_amount, 2) }}</td>
                                <td class="py-3 pr-6">
                                    @php
                                        $statusLower = strtolower($br->status ?? '');
                                        if ($statusLower === 'pending') {
                                            $statusLabel = 'Pending Approval';
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                        } elseif ($statusLower === 'approved') {
                                            $statusLabel = 'Approved';
                                            $statusClass = 'bg-blue-100 text-blue-800';
                                        } elseif ($statusLower === 'rejected') {
                                            $statusLabel = 'Rejected';
                                            $statusClass = 'bg-red-100 text-red-800';
                                        } elseif ($statusLower === 'completed') {
                                            $statusLabel = 'Completed';
                                            $statusClass = 'bg-green-100 text-green-800';
                                        } else {
                                            $statusLabel = ucfirst($br->status ?? 'Pending');
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                        }
                                    @endphp
                                    <span class="rw-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                    @if($br->rejection_reason && $statusLower === 'rejected')
                                        <div class="text-xs text-red-600 mt-1" title="{{ $br->rejection_reason }}">
                                            {{ \Illuminate\Support\Str::limit($br->rejection_reason, 30) }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-gray-500">No buy requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $buyRequests->links() }}</div>
        </div>
    </div>
        <!-- Toast (shared) -->
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
    Alpine.data('userHistoryFilters', () => ({
        isLoadingPayments: false,
        isLoadingTransactions: false,
        isLoadingBuyRequests: false,
        toast: { open: false, message: '', type: 'info' },

        showToast(message, type = 'info') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.open = true;
            setTimeout(() => { this.toast.open = false }, 3000);
        },

        async submitPayments(form) {
            this.isLoadingPayments = true;
            try {
                const params = new URLSearchParams(new FormData(form)).toString();
                const url = form.action + (params ? ('?' + params) : '');

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Server error: ' + response.status);

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#userPaymentsTable');
                const current = document.querySelector('#userPaymentsTable');

                if (incoming && current) {
                    current.innerHTML = incoming.innerHTML;
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', url);
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load payments. Please try again.', 'error');
            } finally {
                this.isLoadingPayments = false;
            }
        },

        clearPayments() {
            const form = this.$refs.paymentsForm;
            if (!form) return;

            // clear visible filters
            ['payment_search', 'payment_status', 'payment_network'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });
            // reset payment-specific paging/sort
            ['payments_page', 'payment_sort', 'payment_dir'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });

            this.submitPayments(form);
        },

        async submitTransactions(form) {
            this.isLoadingTransactions = true;
            try {
                const params = new URLSearchParams(new FormData(form)).toString();
                const url = form.action + (params ? ('?' + params) : '');

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Server error: ' + response.status);

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#userTransactionsTable');
                const current = document.querySelector('#userTransactionsTable');

                if (incoming && current) {
                    current.innerHTML = incoming.innerHTML;
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', url);
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load transactions. Please try again.', 'error');
            } finally {
                this.isLoadingTransactions = false;
            }
        },

        clearTransactions() {
            const form = this.$refs.transactionsForm;
            if (!form) return;

            ['transaction_search', 'transaction_type', 'transaction_status'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });
            ['transactions_page', 'transaction_sort', 'transaction_dir'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });

            this.submitTransactions(form);
        },

        async submitBuyRequests(form) {
            this.isLoadingBuyRequests = true;
            try {
                const params = new URLSearchParams(new FormData(form)).toString();
                const url = form.action + (params ? ('?' + params) : '');

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Server error: ' + response.status);

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#userBuyRequestsTable');
                const current = document.querySelector('#userBuyRequestsTable');

                if (incoming && current) {
                    current.innerHTML = incoming.innerHTML;
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', url);
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load buy requests. Please try again.', 'error');
            } finally {
                this.isLoadingBuyRequests = false;
            }
        },

        clearBuyRequests() {
            const form = this.$refs.buyRequestsForm;
            if (!form) return;

            ['buy_request_search', 'buy_request_status'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });
            ['buy_requests_page', 'buy_request_sort', 'buy_request_dir'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });

            this.submitBuyRequests(form);
        }
    }));
});
</script>
@endsection

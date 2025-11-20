@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-montserrat font-bold">Transaction History</h1>
                    <p class="text-white/80 text-sm sm:text-base mt-1">Complete audit trail of crypto payment submissions and token credits/debits.</p>
                </div>
                <a href="{{ route('dashboard.admin') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 whitespace-nowrap">Back to Dashboard</a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-10 space-y-6 sm:space-y-10">
        <!-- Payment Submissions -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-lg sm:text-xl">Payment Submissions</h3>
                    <p class="text-gray-600 text-xs sm:text-sm">Includes per‑purchase coin price in Rs for clear tracking.</p>
                </div>
                <span class="rw-badge text-xs sm:text-sm">{{ $payments->total() }} total</span>
            </div>

            <!-- Payment Filters -->
            <div class="mb-4 pb-4 border-b">
                <form method="GET" action="{{ route('admin.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
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
                            placeholder="User, email, or TX hash"
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
                        <button type="submit" class="btn-primary flex-1">Filter</button>
                        <a href="{{ route('admin.history') }}" class="btn-secondary">Clear</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-xs sm:text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-gray-600">
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'created_at', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Date @if(request('payment_sort') === 'created_at') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">User</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'token_amount', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Tokens @if(request('payment_sort') === 'token_amount') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden md:table-cell">Price (Rs)</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden lg:table-cell">Total (Rs)</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden sm:table-cell">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'network', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Network @if(request('payment_sort') === 'network') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden lg:table-cell">TX Hash</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['payment_sort', 'payment_dir', 'payments_page']), ['payment_sort' => 'status', 'payment_dir' => request('payment_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Status @if(request('payment_sort') === 'status') {{ request('payment_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden xl:table-cell">Profit (Rs)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($payments as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500">{{ $p->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm">
                                    <div class="font-medium text-gray-900">{{ $p->user->name ?? 'N/A' }}</div>
                                    <div class="text-gray-500 text-xs">{{ $p->user->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-900">{{ number_format($p->token_amount) }}</td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">{{ number_format($p->coin_price_rs ?? ($p->token_amount ? ($p->pkr_amount / $p->token_amount) : 0), 2) }}</td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden lg:table-cell">{{ number_format(($p->coin_price_rs ?? 0) * $p->token_amount, 2) }}</td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm hidden sm:table-cell"><span class="rw-badge text-xs">{{ $p->network }}</span></td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden lg:table-cell"><span class="break-all font-mono text-xs">{{ \Illuminate\Support\Str::limit($p->tx_hash, 20) }}</span></td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm">
                                    @if($p->status === 'approved')
                                        <span class="rw-badge bg-green-100 text-green-800 text-xs">Approved</span>
                                    @elseif($p->status === 'rejected')
                                        <span class="rw-badge bg-red-100 text-red-800 text-xs">Rejected</span>
                                    @else
                                        <span class="rw-badge bg-yellow-100 text-yellow-800 text-xs">Pending</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden xl:table-cell">
                                    @php
                                        $profit = (isset($currentCoinPrice) && $p->status === 'approved') ? ($currentCoinPrice - (float)($p->coin_price_rs ?? 0)) * (int) $p->token_amount : null;
                                    @endphp
                                    {{ $profit !== null ? number_format($profit, 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-3 sm:px-6 py-6 text-center text-xs sm:text-sm text-gray-500">No submissions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
            <div class="mt-4">{{ $payments->links() }}</div>
        </div>

        <!-- Token Transactions -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-lg sm:text-xl">Token Transactions</h3>
                    <p class="text-gray-600 text-xs sm:text-sm">All credits/debits to user token balances.</p>
                </div>
                <span class="rw-badge text-xs sm:text-sm">{{ $transactions->total() }} total</span>
            </div>

            <!-- Transaction Filters -->
            <div class="mb-4 pb-4 border-b">
                <form method="GET" action="{{ route('admin.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
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
                            placeholder="User, email, type, or reference"
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
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                        <button type="submit" class="btn-primary flex-1 text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3">Filter</button>
                        <a href="{{ route('admin.history') }}" class="btn-secondary text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3">Clear</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-xs sm:text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-gray-600">
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'created_at', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Date @if(request('transaction_sort') === 'created_at') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">User</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'type', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Type @if(request('transaction_sort') === 'type') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'amount', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Coins @if(request('transaction_sort') === 'amount') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden md:table-cell">Price/Coin (PKR)</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden lg:table-cell">Total Price (PKR)</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider hidden lg:table-cell">Reference</th>
                            <th class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-700 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(array_merge(request()->except(['transaction_sort', 'transaction_dir', 'transactions_page']), ['transaction_sort' => 'status', 'transaction_dir' => request('transaction_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-primary">
                                    Status @if(request('transaction_sort') === 'status') {{ request('transaction_dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500">{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm">
                                    <div class="font-medium text-gray-900">{{ $t->user->name ?? 'N/A' }}</div>
                                    <div class="text-gray-500 text-xs">{{ $t->user->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm font-semibold text-gray-900">{{ number_format(abs($t->amount)) }} RWAMP</td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                                    @if($t->price_per_coin)
                                        <span class="text-gray-900">PKR {{ number_format($t->price_per_coin, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden lg:table-cell">
                                    @if($t->total_price)
                                        <span class="font-semibold text-gray-900">PKR {{ number_format($t->total_price, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden lg:table-cell"><span class="break-all font-mono text-xs">{{ \Illuminate\Support\Str::limit($t->reference ?? '—', 30) }}</span></td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm">
                                    @if($t->status === 'completed')
                                        <span class="rw-badge bg-green-100 text-green-800 text-xs">Completed</span>
                                    @elseif($t->status === 'failed')
                                        <span class="rw-badge bg-red-100 text-red-800 text-xs">Failed</span>
                                    @else
                                        <span class="rw-badge bg-yellow-100 text-yellow-800 text-xs">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-3 sm:px-6 py-6 text-center text-xs sm:text-sm text-gray-500">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>

        <!-- Profit Calculator -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover" x-data="{ tokens: 100, buy: 1.00, current: {{ number_format($currentCoinPrice, 4) }}, profit: '0.00' }" x-effect="profit = ((((parseFloat(current)||0) - (parseFloat(buy)||0)) * (parseFloat(tokens)||0)).toFixed(2))">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">Profit Calculator</h3>
                <span class="rw-badge">Current Price: Rs {{ number_format($currentCoinPrice, 4) }}</span>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <label class="block">
                    <span class="text-sm text-gray-600">Tokens</span>
                    <input type="number" min="1" x-model.number="tokens" class="rw-input" />
                </label>
                <label class="block">
                    <span class="text-sm text-gray-600">Buy Price (Rs)</span>
                    <input type="number" step="0.0001" min="0" x-model.number="buy" class="rw-input" />
                </label>
                <label class="block">
                    <span class="text-sm text-gray-600">Current Price (Rs)</span>
                    <input type="number" step="0.0001" min="0" x-model.number="current" class="rw-input" />
                </label>
            </div>
            <div class="mt-4 text-lg">Estimated Profit: <span class="font-semibold">Rs <span x-text="profit"></span></span></div>
        </div>
    </div>
</div>
@endsection

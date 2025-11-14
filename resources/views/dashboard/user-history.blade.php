@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Your Purchase History</h1>
                    <p class="text-white/80">Only shows activity for {{ auth()->user()->name }}.</p>
                </div>
                <a href="{{ route('dashboard.investor') }}" class="btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10 space-y-10">
        <!-- Payment Submissions -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Payment Submissions</h3>
                    <p class="text-gray-600 text-sm">Submission price is stored to preserve your exact buy rate.</p>
                </div>
                <span class="rw-badge">{{ $payments->total() }} total</span>
            </div>

            <!-- Payment Filters -->
            <div class="mb-4 pb-4 border-b">
                <form method="GET" action="{{ route('user.history') }}" class="grid md:grid-cols-4 gap-4">
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
                        <button type="submit" class="btn-primary flex-1">Filter</button>
                        <a href="{{ route('user.history') }}" class="btn-secondary">Clear</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
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
                            <th class="py-3 pr-6">Price (Rs)</th>
                            <th class="py-3 pr-6">Total (Rs)</th>
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
                                <td class="py-3 pr-6">{{ number_format($p->coin_price_rs ?? ($p->token_amount ? ($p->pkr_amount / $p->token_amount) : 0), 2) }}</td>
                                <td class="py-3 pr-6">{{ number_format(($p->coin_price_rs ?? 0) * $p->token_amount, 2) }}</td>
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
                <form method="GET" action="{{ route('user.history') }}" class="grid md:grid-cols-4 gap-4">
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
                        <button type="submit" class="btn-primary flex-1">Filter</button>
                        <a href="{{ route('user.history') }}" class="btn-secondary">Clear</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
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
                            <tr><td colspan="5" class="py-6 text-center text-gray-500">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>
    </div>
</div>
@endsection

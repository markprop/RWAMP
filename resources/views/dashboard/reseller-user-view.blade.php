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
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">User Details</h1>
                        <p class="text-gray-500 text-sm mt-1.5">View complete user information</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="shareUserDetails()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                            📋 Share Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">
        <!-- User Information Card -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <h2 class="text-2xl font-bold mb-6">User Information</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                    <p class="text-lg font-semibold" data-user-name>{{ $user->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Email Address</label>
                    <p class="text-lg" data-user-email>{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Phone Number</label>
                    <p class="text-lg" data-user-phone>{{ $user->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Total Payments</label>
                    <p class="text-lg" data-user-payments>{{ $user->crypto_payments_count ?? 0 }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Total Transactions</label>
                    <p class="text-lg" data-user-transactions>{{ $user->transactions_count ?? 0 }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Joined Date</label>
                    <p class="text-lg" data-user-joined>{{ $user->created_at->format('F d, Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">KYC Status</label>
                    <p class="text-lg">
                        <span class="px-3 py-1 rounded text-sm font-semibold 
                            {{ $user->kyc_status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($user->kyc_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($user->kyc_status ?? 'Not submitted') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Payments History -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <h2 class="text-2xl font-bold mb-6">Payment History</h2>
            @if($payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Network</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">TX Hash</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold">{{ number_format($payment->token_amount, 0) }} RWAMP</td>
                                    <td class="py-3 px-4">{{ strtoupper($payment->network) }}</td>
                                    <td class="py-3 px-4">
                                        <code class="text-xs">{{ substr($payment->tx_hash, 0, 20) }}...</code>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-sm 
                                            {{ $payment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                               ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('reseller.payments.view', $payment) }}" class="text-blue-600 hover:underline text-sm">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No payments found.</p>
            @endif
        </div>

        <!-- Transactions History -->
        <div class="bg-white rounded-xl shadow-xl p-6">
            <h2 class="text-2xl font-bold mb-6">Transaction History</h2>
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Reference</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 capitalize">{{ str_replace('_', ' ', $transaction->type) }}</td>
                                    <td class="py-3 px-4 font-semibold {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type === 'credit' || $transaction->type === 'commission' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} RWAMP
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-sm 
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $transaction->reference ?? '—' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('reseller.transactions.view', $transaction) }}" class="text-blue-600 hover:underline text-sm">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No transactions found.</p>
            @endif
        </div>
    </div>
</div>

<script>
function shareUserDetails() {
    const details = `User Details:
Name: {{ $user->name }}
Email: {{ $user->email }}
Phone: {{ $user->phone ?? 'Not provided' }}
Total Payments: {{ $user->crypto_payments_count ?? 0 }}
Total Transactions: {{ $user->transactions_count ?? 0 }}
Joined: {{ $user->created_at->format('F d, Y') }}
KYC Status: {{ ucfirst($user->kyc_status ?? 'Not submitted') }}

Shared from RWAMP Reseller Dashboard`;

    navigator.clipboard.writeText(details).then(() => {
        alert('User details copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = details;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('User details copied to clipboard!');
    });
}
</script>
@endsection


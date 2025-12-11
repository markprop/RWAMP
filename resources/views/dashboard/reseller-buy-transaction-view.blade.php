@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.reseller-sidebar')
    
    <!-- Main Content Area -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Buy Transaction Details</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Complete information about your coin purchase</p>
                    </div>
                    <a href="{{ route('reseller.buy-transactions') }}" class="btn-secondary">
                        ← Back to Buy Transactions
                    </a>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-100">
                <!-- Status Header -->
                <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Transaction Status</h2>
                            <p class="text-sm text-gray-600 mt-1">Current status of your purchase</p>
                        </div>
                        <span class="px-4 py-2 rounded-full text-sm font-semibold 
                            {{ $payment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $payment->status === 'pending' ? 'Waiting for Admin Approval' : ucfirst($payment->status) }}
                        </span>
                    </div>
                </div>

                <!-- Transaction Details -->
                <div class="p-6 space-y-6">
                    <!-- Transaction Hash Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Transaction Hash</h3>
                        <div class="flex items-center space-x-3">
                            <code class="flex-1 bg-gray-50 px-4 py-3 rounded-lg text-sm font-mono text-gray-900 break-all">
                                {{ $payment->tx_hash }}
                            </code>
                            <button 
                                onclick="copyToClipboard('{{ $payment->tx_hash }}')"
                                class="btn-secondary btn-small"
                                title="Copy transaction hash"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Copy
                            </button>
                            @if($payment->tx_hash)
                                <a 
                                    href="https://{{ $payment->network === 'TRC20' ? 'tronscan.org' : ($payment->network === 'ERC20' ? 'etherscan.io' : ($payment->network === 'BEP20' ? 'bscscan.com' : 'blockchain.com')) }}/tx/{{ $payment->tx_hash }}" 
                                    target="_blank"
                                    class="btn-primary btn-small"
                                >
                                    View on Explorer
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Information Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Network</h3>
                                <span class="inline-block px-4 py-2 rounded-lg text-sm font-semibold 
                                    {{ $payment->network === 'TRC20' ? 'bg-green-100 text-green-800' : 
                                       ($payment->network === 'ERC20' ? 'bg-blue-100 text-blue-800' : 
                                       ($payment->network === 'BEP20' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($payment->network === 'BTC' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'))) }}">
                                    {{ $payment->network }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Token Amount</h3>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ number_format((float)$payment->token_amount, 0) }} <span class="text-lg text-gray-600">RWAMP</span>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Payment Amount</h3>
                                <div class="space-y-2">
                                    <p class="text-xl font-bold text-gray-900">
                                        ${{ number_format((float)$payment->usd_amount, 2) }} <span class="text-base text-gray-600">USD</span>
                                    </p>
                                    <p class="text-lg text-gray-600">
                                        PKR {{ number_format((float)$payment->pkr_amount, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Purchase Date</h3>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $payment->created_at->format('F d, Y') }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $payment->created_at->format('h:i A') }}
                                </p>
                            </div>

                            @if($payment->coin_price_rs)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Price per Token</h3>
                                <p class="text-lg font-semibold text-gray-900">
                                    PKR {{ number_format((float)$payment->coin_price_rs, 4) }}
                                </p>
                            </div>
                            @endif

                            @if($transaction)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Transaction Record Status</h3>
                                <span class="inline-block px-4 py-2 rounded-lg text-sm font-semibold 
                                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Your Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Name</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $payment->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Email</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $payment->user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Messages -->
                    @if($payment->status === 'pending')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-yellow-800">Waiting for Admin Approval</h4>
                                <p class="text-sm text-yellow-700 mt-1">Your payment is pending admin verification. Once approved, tokens will be credited to your account.</p>
                            </div>
                        </div>
                    </div>
                    @elseif($payment->status === 'approved')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-green-800">Payment Approved</h4>
                                <p class="text-sm text-green-700 mt-1">Your payment has been verified and tokens have been credited to your account.</p>
                            </div>
                        </div>
                    </div>
                    @elseif($payment->status === 'rejected')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-red-800">Payment Rejected</h4>
                                <p class="text-sm text-red-700 mt-1">Your payment has been rejected. Please contact support if you believe this is an error.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($payment->notes)
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Admin Notes</h3>
                        <p class="text-sm text-gray-700 bg-gray-50 px-4 py-3 rounded-lg">{{ $payment->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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


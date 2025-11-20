@extends('layouts.app')

@php
    use App\Helpers\PriceHelper;
    $rates = [
        'tokenUsd' => PriceHelper::getRwampUsdPrice(),
        'tokenPkr' => PriceHelper::getRwampPkrPrice(),
        'usdToPkr' => (float) config('crypto.rates.usd_pkr', 278),
        'usdtUsd' => PriceHelper::getUsdtUsdPrice(),
        'usdtPkr' => PriceHelper::getUsdtPkrPrice(),
        'btcUsd' => PriceHelper::getBtcUsdPrice(),
        'btcPkr' => PriceHelper::getBtcPkrPrice(),
    ];
    $wallets = [
        'TRC20' => (string) config('crypto.wallets.TRC20', ''),
        'ERC20' => (string) config('crypto.wallets.ERC20', ''),
        'BEP20' => (string) config('crypto.wallets.BEP20', config('crypto.wallets.ERC20', '')),
        'BTC' => (string) config('crypto.wallets.BTC', ''),
    ];
    $paymentsDisabled = (bool) (config('crypto.features.payments_enabled') === false);
@endphp

@push('head')
@php($wcEnabled = config('crypto.features.walletconnect_enabled'))
@if($wcEnabled)
<!-- WalletConnect v2 Modal (only if project ID is configured) -->
<script>
    (function loadWalletConnect() {
        const sources = [
            'https://unpkg.com/@walletconnect/modal@2.7.3/dist/index.umd.js',
            'https://cdn.jsdelivr.net/npm/@walletconnect/modal@2.7.3/dist/index.umd.js'
        ];
        function tryNext(i){
            if(i>=sources.length){
                window.walletConnectFallback = true;
                window.walletConnectCDNFailed = true;
                return;
            }
            const s=document.createElement('script');
            s.src=sources[i];
            s.async=true;
            s.onload=function(){
                window.WalletConnectModalGlobal = (window.WalletConnectModal && (window.WalletConnectModal.default||window.WalletConnectModal)) || null;
                if(!window.WalletConnectModalGlobal){
                    window.walletConnectFallback = true;
                }
            };
            s.onerror=function(){
                tryNext(i+1);
            };
            document.head.appendChild(s);
        }
        tryNext(0);
    })();
</script>
<script>
    window.walletConnectModal = window.walletConnectModal || null;
    window.walletConnectLoaded = false;
    
    window.initializeWalletConnect = function initializeWalletConnect() {
        try {
            if (window.WalletConnectModalGlobal) {
                const projectId = '{{ config("crypto.walletconnect_project_id", "") }}';
                if (!projectId || projectId === 'your-project-id' || projectId === '') {
                    window.walletConnectLoaded = false;
                    window.walletConnectFallback = true;
                    return;
                }
                
                window.walletConnectModal = new window.WalletConnectModalGlobal({
                    projectId: projectId,
                    chains: ['eip155:1', 'eip155:56', 'eip155:11155111'],
                    optionalChains: ['eip155:137', 'eip155:43114'],
                    enableNetworkSwitching: true,
                    enableExplorer: true,
                    explorerRecommendedWalletIds: [
                        'c57ca95b47569778a828d19178114f4db188b89b',
                        '4622a2b2d6af1c9844944291e5e7351a6aa24cd7'
                    ],
                    explorerExcludedWalletIds: 'ALL',
                    themeMode: 'light',
                    themeVariables: {
                        '--wcm-z-index': '1000'
                    }
                });
                window.walletConnectLoaded = true;
            } else {
                window.walletConnectLoaded = false;
                window.walletConnectFallback = true;
            }
        } catch (error) {
            window.walletConnectLoaded = false;
            window.walletConnectFallback = true;
        }
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(window.initializeWalletConnect, 1000);
    });
    
    window.addEventListener('load', function() {
        if (!window.walletConnectLoaded && !window.walletConnectFallback) {
            setTimeout(window.initializeWalletConnect, 500);
        }
    });
</script>
@endif
@endpush

@section('content')
<div class="min-h-screen bg-white" x-data="investorDashboard" x-cloak>
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-start justify-between flex-wrap gap-6">
                <div class="flex-1">
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold mb-2">Investor Dashboard</h1>
                    <p class="text-white/80 mb-4">Welcome, {{ auth()->user()->name }}.</p>
                    @if(auth()->user()->referral_code)
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2 border border-white/20 inline-block">
                            <span class="text-xs text-white/70 uppercase tracking-wide">Your Referral Code:</span>
                            <span class="text-lg font-mono font-bold text-white ml-2">{{ auth()->user()->referral_code }}</span>
                        </div>
                    @endif
                </div>
                
                <!-- Portfolio Cards Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full md:w-auto">
                    <!-- Token Balance Card -->
                    <div class="bg-gradient-to-br from-primary to-red-600 rounded-xl px-6 py-5 border-2 border-white/40 shadow-2xl min-w-[200px]">
                        <div class="text-xs text-white uppercase tracking-wide mb-2 font-bold" style="color: #ffffff !important;">Token Balance</div>
                        <div class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg" style="color: #ffffff !important;">
                            {{ number_format($metrics['token_balance'] ?? auth()->user()->token_balance ?? 0, 0) }} <span class="text-xl md:text-2xl font-bold" style="color: #ffffff !important;">RWAMP</span>
                        </div>
                    </div>
                    
                    <!-- Portfolio Value Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl px-6 py-5 border-2 border-white/40 shadow-2xl min-w-[200px]">
                        <div class="text-xs text-white uppercase tracking-wide mb-2 font-bold" style="color: #ffffff !important;">Portfolio Value</div>
                        <div class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg mb-1" style="color: #ffffff !important;">
                            PKR {{ number_format($metrics['portfolio_value'] ?? 0, 0) }}
                        </div>
                        <div class="text-xs text-white" style="color: #ffffff !important;">Avg: PKR {{ number_format($metrics['average_purchase_price'] ?? 0, 2) }}/coin</div>
                    </div>
                    
                    <!-- Official Portfolio Value Card -->
                    <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl px-6 py-5 border-2 border-white/40 shadow-2xl min-w-[200px]">
                        <div class="text-xs text-white uppercase tracking-wide mb-2 font-bold" style="color: #ffffff !important;">Official Portfolio Value</div>
                        <div class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg mb-1" style="color: #ffffff !important;">
                            PKR {{ number_format($metrics['official_portfolio_value'] ?? 0, 0) }}
                        </div>
                        <div class="text-xs text-white" style="color: #ffffff !important;">Official: PKR {{ number_format($metrics['official_price'] ?? 0, 2) }}/coin</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="font-montserrat font-bold mb-2">Purchase History</h3>
            <p class="text-gray-600">View your recent token purchases.</p>
            <div class="mt-4">
                <a href="{{ route('user.history') }}" class="btn-secondary">View History</a>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="font-montserrat font-bold mb-2">Buy Coins</h3>
            <p class="text-gray-600">Purchase RWAMP tokens using cryptocurrency.</p>
            <div class="mt-4">
                <button @click="$dispatch('open-purchase-modal')" class="btn-primary w-full">Buy Coins</button>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="font-montserrat font-bold mb-2">Buy From Reseller</h3>
            <p class="text-gray-600">Purchase RWAMP tokens from a reseller.</p>
            <div class="mt-4">
                <button @click="$dispatch('open-buy-from-reseller-modal')" class="btn-primary w-full">Buy From Reseller</button>
            </div>
        </div>
    </div>

    {{-- CHAT SYSTEM DISABLED - See CHAT_REENABLE_GUIDE.md to re-enable --}}
    {{-- <!-- Chat Section -->
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-montserrat font-bold text-xl mb-1 text-gray-900">💬 Chat Dashboard</h3>
                        <p class="text-gray-600 text-sm">Communicate with resellers, bargain, and complete offline payments</p>
                    </div>
                </div>
                <a href="{{ route('chat.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 shadow-lg hover:shadow-xl">
                    Open Chat Dashboard
                </a>
            </div>
        </div>
    </div> --}}

    <!-- Pending Buy Requests Section -->
    @if(isset($pendingBuyRequests) && $pendingBuyRequests->count() > 0)
    <div class="max-w-7xl mx-auto px-4 pb-6">
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Pending Buy Requests</h3>
                    <p class="text-gray-600 text-sm mt-1">Your pending coin purchase requests waiting for approval</p>
                </div>
            </div>
            <div class="border rounded-lg overflow-hidden">
                <div class="max-h-96 overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left px-4 py-2">Date</th>
                                <th class="text-left px-4 py-2">Seller</th>
                                <th class="text-left px-4 py-2">Coins</th>
                                <th class="text-left px-4 py-2">Price per Coin</th>
                                <th class="text-left px-4 py-2">Total Amount</th>
                                <th class="text-left px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingBuyRequests as $buyRequest)
                                <tr class="border-t">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $buyRequest->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2">
                                        <div class="font-semibold">
                                            @if($buyRequest->reseller)
                                                {{ $buyRequest->reseller->name }} <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">Reseller</span>
                                            @else
                                                <span class="text-gray-500">Unknown</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">{{ number_format($buyRequest->coin_quantity, 0) }} RWAMP</td>
                                    <td class="px-4 py-2">PKR {{ number_format($buyRequest->coin_price, 2) }}</td>
                                    <td class="px-4 py-2 font-semibold">PKR {{ number_format($buyRequest->total_amount, 2) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="rw-badge bg-yellow-100 text-yellow-800">
                                            Pending Approval
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="max-w-7xl mx-auto px-4 pb-12 grid md:grid-cols-2 gap-6">
        <!-- Recent Payment Submissions -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">Recent Payment Submissions</h3>
                <a href="{{ route('user.history') }}" class="text-sm underline">View all</a>
            </div>
            <div class="border rounded-lg overflow-hidden">
                <div class="max-h-96 overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left px-4 py-2">Date</th>
                                <th class="text-left px-4 py-2">Tokens</th>
                                <th class="text-left px-4 py-2">Network</th>
                                <th class="text-left px-4 py-2">TX Hash</th>
                                <th class="text-left px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($paymentsRecent ?? []) as $p)
                                <tr class="border-t">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $p->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2">{{ number_format($p->token_amount) }}</td>
                                    <td class="px-4 py-2">{{ $p->network }}</td>
                                    <td class="px-4 py-2"><span class="break-all">{{ \Illuminate\Support\Str::limit($p->tx_hash, 24) }}</span></td>
                                    <td class="px-4 py-2">
                                        @php($status = strtolower($p->status ?? 'pending'))
                                        <span class="rw-badge">
                                            @if($status === 'pending')
                                                Wait for Admin Approval
                                            @elseif($status === 'approved')
                                                Approved
                                            @elseif($status === 'rejected')
                                                Rejected
                                            @else
                                                {{ ucfirst($p->status ?? 'Pending') }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No submissions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Token Transactions -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">Recent Token Transactions</h3>
                <a href="{{ route('user.history') }}" class="text-sm underline">View all</a>
            </div>
            <div class="border rounded-lg overflow-hidden">
                <div class="max-h-96 overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left px-4 py-2">Date</th>
                                <th class="text-left px-4 py-2">Type</th>
                                <th class="text-left px-4 py-2">Amount</th>
                                <th class="text-left px-4 py-2">Reference</th>
                                <th class="text-left px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($transactionsRecent ?? []) as $t)
                                <tr class="border-t">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $t->type }}</td>
                                    <td class="px-4 py-2">{{ number_format($t->amount) }}</td>
                                    <td class="px-4 py-2">
                                        @php($ref = trim((string)($t->reference ?? '')))
                                        @if($ref === '')
                                            @php($ref = $t->type === 'credit' ? 'Token credit (wallet purchase)' : ($t->type === 'debit' ? 'Token debit' : 'Transaction'))
                                        @elseif(strlen($ref) > 24 && (substr($ref, 0, 2) === '0x' || strlen($ref) === 64))
                                            @php($ref = substr($ref, 0, 12) . '...' . substr($ref, -8))
                                        @endif
                                        <span class="break-all">{{ $ref }}</span>
                                    </td>
                                    <td class="px-4 py-2"><span class="rw-badge">{{ ucfirst($t->status ?? 'completed') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Purchase Modal -->
    @include('components.purchase-modal', ['rates' => $rates, 'wallets' => $wallets, 'paymentsDisabled' => $paymentsDisabled])
    
    <!-- Buy From Reseller Modal -->
    @include('components.buy-from-reseller-modal')
</div>
@endsection


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
<div class="min-h-screen bg-gray-50" x-data="investorDashboard({{ ($isInGame ?? false) ? 'true' : 'false' }}, {{ ($hasPin ?? false) ? 'true' : 'false' }})" x-cloak>
    @include('components.game-modals')
    
    <!-- Sidebar -->
    @include('components.investor-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar with User Info -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Investor Dashboard</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Welcome back, <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span></p>
                    </div>
                    <!-- User Avatar Dropdown (Top Right) -->
                    <div class="flex items-center space-x-4">
                        @if(auth()->user()->referral_code)
                            <div class="hidden md:block bg-gray-100 rounded-lg px-4 py-2 border border-gray-200">
                                <span class="text-xs text-gray-600 uppercase tracking-wide">Referral Code:</span>
                                <span class="text-sm font-mono font-bold text-gray-900 ml-2">{{ auth()->user()->referral_code }}</span>
                            </div>
                        @endif
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-red-600 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200"
                                 style="display: none;">
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6 rw-page-shell">
            <!-- Portfolio Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 rw-card-grid">
                <!-- Token Balance Card -->
                <div class="bg-gradient-to-br from-primary to-red-600 rounded-xl px-6 py-6 shadow-xl min-h-[140px] flex flex-col justify-between">
                    <div class="text-xs text-white/90 uppercase tracking-wide mb-3 font-semibold">Token Balance</div>
                    <div class="flex items-baseline gap-2 flex-wrap">
                        <span class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg leading-tight">
                            {{ number_format($metrics['token_balance'] ?? auth()->user()->token_balance ?? 0, 0) }}
                        </span>
                        <span class="text-lg md:text-xl font-bold text-white/95">RWAMP</span>
                    </div>
                </div>
                
                <!-- Portfolio Value Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl px-6 py-6 shadow-xl min-h-[140px] flex flex-col justify-between">
                    <div class="text-xs text-white/90 uppercase tracking-wide mb-3 font-semibold">Portfolio Value</div>
                    <div>
                        <div class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg mb-2 leading-tight">
                            @include('components.price-tag', [
                                'pkr' => $metrics['portfolio_value'] ?? 0,
                                'size' => 'large',
                                'variant' => 'dark'
                            ])
                        </div>
                        <div class="text-xs text-white/90 font-medium">
                            Avg: @include('components.price-tag', [
                                'pkr' => $metrics['average_purchase_price'] ?? 0,
                                'size' => 'small',
                                'variant' => 'dark',
                                'class' => 'inline'
                            ])/coin
                        </div>
                    </div>
                </div>
                
                <!-- Official Portfolio Value Card -->
                <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl px-6 py-6 shadow-xl min-h-[140px] flex flex-col justify-between">
                    <div class="text-xs text-white/90 uppercase tracking-wide mb-3 font-semibold">Official Portfolio Value</div>
                    <div>
                        <div class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg mb-2 leading-tight">
                            @include('components.price-tag', [
                                'pkr' => $metrics['official_portfolio_value'] ?? 0,
                                'size' => 'large',
                                'variant' => 'dark'
                            ])
                        </div>
                        <div class="text-xs text-white/90 font-medium">
                            Official: @include('components.price-tag', [
                                'pkr' => $metrics['official_price'] ?? 0,
                                'size' => 'small',
                                'variant' => 'dark',
                                'class' => 'inline'
                            ])/coin
                        </div>
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
            <div class="mb-8">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-montserrat font-bold text-xl text-gray-900">Pending Buy Requests</h3>
                            <p class="text-gray-500 text-sm mt-1.5">Your pending coin purchase requests waiting for approval</p>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-lg rw-table-scroll overflow-x-auto">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-[720px] whitespace-nowrap text-sm w-full">
                                <thead class="bg-gray-50 sticky border-b border-gray-200" style="top: 28px;">
                                    <tr>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Seller</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Coins</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Price per Coin</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Amount</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pendingBuyRequests as $buyRequest)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $buyRequest->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">
                                                    @if($buyRequest->reseller)
                                                        {{ $buyRequest->reseller->name }} <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-medium">Reseller</span>
                                                    @else
                                                        <span class="text-gray-500">Unknown</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 font-medium">{{ number_format($buyRequest->coin_quantity, 0) }} RWAMP</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">PKR {{ number_format($buyRequest->coin_price, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">PKR {{ number_format($buyRequest->total_amount, 2) }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
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
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Recent Payment Submissions -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                        <h3 class="font-montserrat font-bold text-xl text-gray-900">Recent Payment Submissions</h3>
                        <a href="{{ route('user.history') }}" class="btn-secondary btn-small self-start sm:self-auto">View all</a>
                    </div>
                    <div class="border border-gray-200 rounded-lg rw-table-scroll">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full text-sm w-full">
                                <thead class="bg-gray-50 sticky border-b border-gray-200" style="top: 28px;">
                                    <tr>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Tokens</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider sm:table-cell hidden">Network</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider lg:table-cell hidden">TX Hash</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse(($paymentsRecent ?? []) as $p)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                {{ $p->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                                {{ number_format($p->token_amount) }}
                                                <div class="block sm:hidden text-xs text-gray-500 mt-1">
                                                    <span class="font-medium">{{ $p->network }}</span>
                                                    @if($p->tx_hash)
                                                        <span class="mx-1">•</span>
                                                        <span class="font-mono break-all">
                                                            {{ \Illuminate\Support\Str::limit($p->tx_hash, 18) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 hidden sm:table-cell">
                                                {{ $p->network }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 font-mono hidden lg:table-cell">
                                                <span class="break-all">
                                                    {{ \Illuminate\Support\Str::limit($p->tx_hash, 24) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @php($status = strtolower($p->status ?? 'pending'))
                                                @if($status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Wait for Admin Approval</span>
                                                @elseif($status === 'approved')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                                @elseif($status === 'rejected')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($p->status ?? 'Pending') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No submissions yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
        </div>

                <!-- Recent Token Transactions -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                        <h3 class="font-montserrat font-bold text-xl text-gray-900">Recent Token Transactions</h3>
                        <a href="{{ route('user.history') }}" class="btn-secondary btn-small self-start sm:self-auto">View all</a>
                    </div>
                    <div class="border border-gray-200 rounded-lg rw-table-scroll">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full text-sm w-full">
                                <thead class="bg-gray-50 sticky border-b border-gray-200" style="top: 28px;">
                                    <tr>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Amount</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider sm:table-cell hidden">Reference</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse(($transactionsRecent ?? []) as $t)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                {{ $t->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium capitalize">
                                                {{ $t->type }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">
                                                {{ number_format($t->amount) }}
                                                <div class="block sm:hidden text-xs text-gray-500 mt-1">
                                                    @php($refMobile = trim((string)($t->reference ?? '')))
                                                    @if($refMobile === '')
                                                        @php($refMobile = $t->type === 'credit' ? 'Token credit (wallet purchase)' : ($t->type === 'debit' ? 'Token debit' : 'Transaction'))
                                                    @elseif(strlen($refMobile) > 24 && (substr($refMobile, 0, 2) === '0x' || strlen($refMobile) === 64))
                                                        @php($refMobile = substr($refMobile, 0, 12) + '...' + substr($refMobile, -8))
                                                    @endif
                                                    <span class="break-all">{{ $refMobile }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 hidden sm:table-cell">
                                                @php($ref = trim((string)($t->reference ?? '')))
                                                @if($ref === '')
                                                    @php($ref = $t->type === 'credit' ? 'Token credit (wallet purchase)' : ($t->type === 'debit' ? 'Token debit' : 'Transaction'))
                                                @elseif(strlen($ref) > 24 && (substr($ref, 0, 2) === '0x' || strlen($ref) === 64))
                                                    @php($ref = substr($ref, 0, 12) . '...' . substr($ref, -8))
                                                @endif
                                                <span class="break-all">{{ $ref }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ ucfirst($t->status ?? 'completed') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No transactions yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
        </div>
        
        <!-- Purchase Modal -->
        @include('components.purchase-modal', ['rates' => $rates, 'wallets' => $wallets, 'paymentsDisabled' => $paymentsDisabled])
        
        <!-- Buy From Reseller Modal -->
        @include('components.buy-from-reseller-modal')
        </div>
    </div>
</div>
@endsection


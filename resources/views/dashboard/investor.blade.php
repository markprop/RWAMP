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
<div class="min-h-screen bg-white" x-data="{ 
    purchaseModalOpen: false,
    init() {
        @if(request()->query('open') === 'purchase')
        var self = this;
        setTimeout(function() {
            self.purchaseModalOpen = true;
        }, 0);
        @endif
    }
}" x-cloak>
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Investor Dashboard</h1>
            <p class="text-white/80">Welcome, {{ auth()->user()->name }}.</p>
        </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
            <h3 class="font-montserrat font-bold mb-2">Token Balance</h3>
            <div class="text-3xl font-bold">{{ number_format(auth()->user()->token_balance ?? 0) }} RWAMP</div>
        </div>
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


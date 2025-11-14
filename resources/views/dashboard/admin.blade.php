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

@section('content')
<div class="min-h-screen bg-white" x-data="{}" x-init="
    @if(request()->query('open') === 'purchase')
        $nextTick(() => window.dispatchEvent(new CustomEvent('open-purchase-modal')));
    @endif
">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Admin Dashboard</h1>
                    <p class="text-white/80">Welcome, {{ auth()->user()->name }}.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg px-6 py-3 border border-white/20">
                        <div class="text-xs text-white/70 uppercase tracking-wide mb-1">Coin Price</div>
                        <div class="text-2xl font-bold text-white">
                            Rs {{ number_format($metrics['coin_price'] ?? 0, 2) }}
                        </div>
                    </div>
                    <a href="{{ route('admin.users') }}" class="btn-primary">Manage Users</a>
                    <a href="{{ route('admin.sell') }}" class="btn-secondary">Sell Coins</a>
                </div>
            </div>
        </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Metrics -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('admin.users') }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Total Users</div>
                <div class="text-3xl font-bold">{{ $metrics['users'] ?? '—' }}</div>
            </a>
            <a href="{{ route('admin.users', ['role' => 'investor']) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Investors</div>
                <div class="text-3xl font-bold">{{ $metrics['investors'] ?? '—' }}</div>
            </a>
            <a href="{{ route('admin.users', ['role' => 'reseller']) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Resellers</div>
                <div class="text-3xl font-bold">{{ $metrics['resellers'] ?? '—' }}</div>
            </a>
            <a href="{{ route('admin.applications', ['status' => 'pending']) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Pending Applications</div>
                <div class="text-3xl font-bold">{{ $metrics['pending_applications'] ?? '—' }}</div>
            </a>
        </div>

        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('admin.users', ['days' => 7]) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">New Users (7d)</div>
                <div class="text-3xl font-bold">{{ $metrics['new_users_7'] ?? '—' }}</div>
            </a>
            <a href="{{ route('admin.users', ['days' => 30]) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">New Users (30d)</div>
                <div class="text-3xl font-bold">{{ $metrics['new_users_30'] ?? '—' }}</div>
            </a>
            <a href="{{ route('admin.kyc.list', ['status' => 'pending']) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Pending KYC</div>
                <div class="text-3xl font-bold">{{ $metrics['pending_kyc'] ?? '—' }}</div>
            </a>
            <a href="{{ route('admin.crypto.payments') }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Crypto Payments</div>
                <div class="text-3xl font-bold">{{ $metrics['crypto_payments'] ?? '—' }}</div>
            </a>
        </div>

        <!-- Crypto Payments Link -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover mb-8">
            <div class="flex items-center justify-between">
                <h3 class="font-montserrat font-bold text-xl">Crypto Payments</h3>
                <a href="{{ route('admin.crypto.payments') }}" class="btn-secondary">View Pending</a>
            </div>
        </div>

        <!-- KYC Review Link -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">KYC Review</h3>
                    <p class="text-gray-600 text-sm mt-1">Review and approve or reject KYC submissions</p>
                </div>
                <a href="{{ route('admin.kyc.list') }}" class="btn-secondary">View KYC Submissions</a>
            </div>
        </div>

        <!-- Transaction History Link -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Transaction History</h3>
                    <p class="text-gray-600 text-sm mt-1">View all payment submissions and token transactions</p>
                </div>
                <a href="{{ route('admin.history') }}" class="btn-secondary">View History</a>
            </div>
        </div>

        <!-- Price Management Link -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Price Management</h3>
                    <p class="text-gray-600 text-sm mt-1">Set and update USDT prices for payment calculations</p>
                </div>
                <a href="{{ route('admin.prices') }}" class="btn-secondary">Manage Prices</a>
            </div>
        </div>

        <!-- Reseller Applications Link -->
        <div class="bg-white rounded-xl shadow-xl p-6 card-hover mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-montserrat font-bold text-xl">Reseller Applications</h3>
                    <p class="text-gray-600 text-sm mt-1">Review, approve, or reject reseller applications</p>
                </div>
                <a href="{{ route('admin.applications') }}" class="btn-secondary">View Applications</a>
            </div>
        </div>

        <!-- 2FA Management (Admins) -->
        <div class="bg-white rounded-xl shadow-xl p-6 mt-8 card-hover animate-fadeInUp">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">Two‑Factor Authentication</h3>
            </div>

            @if(auth()->user()->two_factor_secret)
                <p class="text-gray-700 mb-4">Two‑factor authentication is <span class="font-semibold">enabled</span> on your account.</p>

                <form method="POST" action="{{ url('user/two-factor-recovery-codes') }}" class="inline-block mr-2">
                    @csrf
                    <button class="btn-secondary">Regenerate Recovery Codes</button>
                </form>

                <form method="POST" action="{{ url('user/two-factor-authentication') }}" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button class="btn-primary">Disable 2FA</button>
                </form>

                <div class="mt-6">
                    <h4 class="font-montserrat font-bold mb-2">Recovery Codes</h4>
                    @php
                        $codes = auth()->user()->recoveryCodes();
                    @endphp
                    <div class="grid md:grid-cols-2 gap-2">
                        @foreach($codes as $code)
                            <code class="bg-gray-100 rounded px-3 py-2 text-sm">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-gray-700 mb-4">Two‑factor authentication is <span class="font-semibold">disabled</span> on your account.</p>
                <form method="POST" action="{{ url('user/two-factor-authentication') }}">
                    @csrf
                    <button class="btn-primary">Enable 2FA</button>
                </form>

                @if (session('status') === 'two-factor-authentication-enabled')
                    <div class="mt-6">
                        <p class="text-gray-700 mb-2">Scan this QR code using your authenticator app and store recovery codes safely.</p>
                        <div class="bg-white p-4 rounded border inline-block">
                            {!! auth()->user()->twoFactorQrCodeSvg() !!}
                        </div>
                        <div class="mt-4">
                            <h4 class="font-montserrat font-bold mb-2">Recovery Codes</h4>
                            @php $codes = auth()->user()->recoveryCodes(); @endphp
                            <div class="grid md:grid-cols-2 gap-2">
                                @foreach($codes as $code)
                                    <code class="bg-gray-100 rounded px-3 py-2 text-sm">{{ $code }}</code>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    
    <!-- Purchase Modal -->
    @include('components.purchase-modal', ['rates' => $rates, 'wallets' => $wallets, 'paymentsDisabled' => $paymentsDisabled])
</div>
@endsection


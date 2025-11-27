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
    <section class="bg-gradient-to-r from-black to-secondary text-white py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-montserrat font-bold">Admin Dashboard</h1>
                    <p class="text-white/80 text-sm sm:text-base mt-1">Welcome, {{ auth()->user()->name }}.</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 sm:px-6 py-3 border border-white/20">
                        <div class="text-xs text-white/70 uppercase tracking-wide mb-1">Coin Price</div>
                        <div class="text-xl sm:text-2xl font-bold text-white">
                            Rs {{ number_format($metrics['coin_price'] ?? 0, 2) }}
                        </div>
                    </div>
                    <a href="{{ route('admin.users') }}" class="btn-primary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3">Manage Users</a>
                    <a href="{{ route('admin.sell') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3">Sell Coins</a>
                    {{-- CHAT SYSTEM DISABLED - See CHAT_REENABLE_GUIDE.md to re-enable --}}
                    {{-- <a href="{{ route('admin.chats.index') }}" class="bg-green-600 hover:bg-green-700 text-white text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 rounded-lg font-semibold transition-colors duration-200 shadow-lg hover:shadow-xl">
                        💬 View All Chats
                    </a> --}}
                </div>
            </div>
        </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 py-10">
        @if(isset($error))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800 font-semibold">Error: {{ $error }}</p>
                </div>
            </div>
        @endif
        <!-- Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
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
                <div class="mt-1 text-xs text-gray-500">
                    of {{ $metrics['total_applications'] ?? 0 }} total
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <a href="{{ route('admin.users', ['days' => 30]) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">New Users (30d)</div>
                <div class="text-3xl font-bold">{{ $metrics['new_users_30'] ?? '—' }}</div>
            </a>
            <a href="{{ route('admin.kyc.list', ['status' => 'pending']) }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Pending KYC</div>
                <div class="text-3xl font-bold">{{ $metrics['pending_kyc'] ?? '—' }}</div>
                <div class="mt-1 text-xs text-gray-500">
                    of {{ $metrics['total_kyc'] ?? 0 }} total
                </div>
            </a>
            <a href="{{ route('admin.crypto.payments') }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Crypto Payments</div>
                <div class="text-3xl font-bold">{{ $metrics['pending_crypto_payments'] ?? '—' }}</div>
                <div class="mt-1 text-xs text-gray-500">
                    pending of {{ $metrics['crypto_payments'] ?? 0 }} total
                </div>
            </a>
            <a href="{{ route('admin.withdrawals') }}" class="bg-white rounded-xl shadow-xl p-6 card-hover block">
                <div class="text-sm text-gray-600">Withdrawal Requests</div>
                <div class="text-3xl font-bold">{{ $metrics['pending_withdrawals'] ?? '—' }}</div>
                <div class="mt-1 text-xs text-gray-500">
                    pending of {{ $metrics['withdrawal_requests'] ?? 0 }} total
                </div>
            </a>
        </div>


        <!-- Crypto Payments Link -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <h3 class="font-montserrat font-bold text-lg sm:text-xl">Crypto Payments</h3>
                <a href="{{ route('admin.crypto.payments') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3">View Pending</a>
            </div>
        </div>

        <!-- Withdrawal Requests Link -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <h3 class="font-montserrat font-bold text-lg sm:text-xl">Withdrawal Requests</h3>
                    <p class="text-gray-600 text-xs sm:text-sm mt-1">Review and process user withdrawal requests</p>
                </div>
                <a href="{{ route('admin.withdrawals') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 whitespace-nowrap">Manage Withdrawals</a>
            </div>
        </div>

        <!-- KYC Review Link -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <h3 class="font-montserrat font-bold text-lg sm:text-xl">KYC Review</h3>
                    <p class="text-gray-600 text-xs sm:text-sm mt-1">Review and approve or reject KYC submissions</p>
                </div>
                <a href="{{ route('admin.kyc.list') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 whitespace-nowrap">View KYC Submissions</a>
            </div>
        </div>

        <!-- Transaction History Link -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <h3 class="font-montserrat font-bold text-lg sm:text-xl">Transaction History</h3>
                    <p class="text-gray-600 text-xs sm:text-sm mt-1">View all payment submissions and token transactions</p>
                </div>
                <a href="{{ route('admin.history') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 whitespace-nowrap">View History</a>
            </div>
        </div>

        <!-- Price Management Link -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <h3 class="font-montserrat font-bold text-lg sm:text-xl">Price Management</h3>
                    <p class="text-gray-600 text-xs sm:text-sm mt-1">Set and update USDT prices for payment calculations</p>
                </div>
                <a href="{{ route('admin.prices') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 whitespace-nowrap">Manage Prices</a>
            </div>
        </div>

        <!-- Reseller Applications Link -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <h3 class="font-montserrat font-bold text-lg sm:text-xl">Reseller Applications</h3>
                    <p class="text-gray-600 text-xs sm:text-sm mt-1">Review, approve, or reject reseller applications</p>
                </div>
                <a href="{{ route('admin.applications') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 whitespace-nowrap">View Applications</a>
            </div>
        </div>

        <!-- 2FA Management (Admins) -->
        <div class="bg-white rounded-xl shadow-xl p-6 mt-8 card-hover animate-fadeInUp">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">Two‑Factor Authentication</h3>
            </div>

            @if(auth()->user()->two_factor_secret)
                <p class="text-gray-700 mb-4">Two‑factor authentication is <span class="font-semibold">enabled</span> on your account.</p>

                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.regenerate-recovery-codes') }}" class="inline-block mr-2">
                    @csrf
                    <button type="submit" class="btn-secondary">Regenerate Recovery Codes</button>
                </form>

                <form method="POST" action="{{ url('user/two-factor-authentication') }}" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button class="btn-primary">Disable 2FA</button>
                </form>

                <div class="mt-6">
                    <h4 class="font-montserrat font-bold mb-2">Recovery Codes</h4>
                    @php
                        $codes = [];
                        try {
                            $user = auth()->user();
                            // Check if recovery codes exist and are not empty
                            if (!empty($user->two_factor_recovery_codes)) {
                                try {
                                    $codes = $user->recoveryCodes();
                                    // Ensure codes is an array
                                    if (!is_array($codes)) {
                                        $codes = [];
                                    }
                                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                                    // Recovery codes are corrupted/invalid - clear them
                                    $codes = [];
                                    \Log::warning('Invalid recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                                } catch (\Exception $e) {
                                    // Any other exception
                                    $codes = [];
                                    \Log::warning('Error retrieving recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                                }
                            }
                        } catch (\Exception $e) {
                            $codes = [];
                        }
                    @endphp
                    @if(!empty($codes))
                        <div class="grid md:grid-cols-2 gap-2">
                            @foreach($codes as $code)
                                <code class="bg-gray-100 rounded px-3 py-2 text-sm">{{ $code }}</code>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 text-sm mb-2">No recovery codes available. Please regenerate them using the button above.</p>
                    @endif
                </div>
            @else
                <p class="text-gray-700 mb-4">Two‑factor authentication is <span class="font-semibold">disabled</span> on your account.</p>
                <form method="POST" action="{{ url('user/two-factor-authentication') }}">
                    @csrf
                    <button class="btn-primary">Enable 2FA</button>
                </form>

                @if (session('status') === 'two-factor-authentication-enabled')
                    <div class="mt-6">
                        @php
                            $qrCodeError = false;
                            $qrCode = null;
                            try {
                                $qrCode = auth()->user()->twoFactorQrCodeSvg();
                            } catch (\Exception $e) {
                                $qrCodeError = true;
                                \Log::error('QR code generation error: ' . $e->getMessage());
                            }
                        @endphp
                        @if($qrCodeError)
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                                <p class="text-sm font-semibold">Error generating QR code.</p>
                                <p class="text-sm mt-1">Your 2FA secret may be corrupted. Please disable and re-enable 2FA.</p>
                            </div>
                        @else
                            <p class="text-gray-700 mb-2">Scan this QR code using your authenticator app and store recovery codes safely.</p>
                            <div class="bg-white p-4 rounded border inline-block">
                                {!! $qrCode !!}
                            </div>
                        @endif
                        <div class="mt-4">
                            <h4 class="font-montserrat font-bold mb-2">Recovery Codes</h4>
                            @php
                                $codes = [];
                                try {
                                    $user = auth()->user();
                                    // Check if recovery codes exist and are not empty
                                    if (!empty($user->two_factor_recovery_codes)) {
                                        try {
                                            $codes = $user->recoveryCodes();
                                            // Ensure codes is an array
                                            if (!is_array($codes)) {
                                                $codes = [];
                                            }
                                        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                                            // Recovery codes are corrupted/invalid - clear them
                                            $codes = [];
                                            \Log::warning('Invalid recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                                        } catch (\Exception $e) {
                                            // Any other exception
                                            $codes = [];
                                            \Log::warning('Error retrieving recovery codes for user ' . $user->id . ': ' . $e->getMessage());
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $codes = [];
                                }
                            @endphp
                            @if(!empty($codes))
                                <div class="grid md:grid-cols-2 gap-2">
                                    @foreach($codes as $code)
                                        <code class="bg-gray-100 rounded px-3 py-2 text-sm">{{ $code }}</code>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600 text-sm mb-2">No recovery codes available. They will be generated when you enable 2FA.</p>
                            @endif
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


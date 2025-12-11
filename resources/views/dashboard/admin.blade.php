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
<div class="min-h-screen bg-gray-50" x-data="{}" x-init="
    @if(request()->query('open') === 'purchase')
        $nextTick(() => window.dispatchEvent(new CustomEvent('open-purchase-modal')));
    @endif
">
    <!-- Sidebar -->
    @include('components.admin-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar with User Info -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Admin Dashboard</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Welcome back, <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span></p>
                    </div>
                    <!-- User Avatar Dropdown (Top Right) -->
                    <div class="flex items-center space-x-4">
                        <div class="hidden md:block bg-gray-100 rounded-lg px-4 py-2 border border-gray-200">
                            <span class="text-xs text-gray-600 uppercase tracking-wide">Coin Price:</span>
                            <span class="text-sm font-mono font-bold text-gray-900 ml-2">
                                @include('components.price-tag', [
                                    'pkr' => $metrics['coin_price'] ?? 0,
                                    'size' => 'small',
                                    'class' => 'inline'
                                ])
                            </span>
                        </div>
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
        <div class="px-4 sm:px-6 lg:px-8 py-6 rw-page-shell" x-data>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8 rw-card-grid">
                <a href="{{ route('admin.users') }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">Total Users</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['users'] ?? '—' }}</div>
                </a>
                <a href="{{ route('admin.users', ['role' => 'investor']) }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">Investors</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['investors'] ?? '—' }}</div>
                </a>
                <a href="{{ route('admin.users', ['role' => 'reseller']) }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">Resellers</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['resellers'] ?? '—' }}</div>
                </a>
                <a href="{{ route('admin.applications', ['status' => 'pending']) }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">Pending Applications</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['pending_applications'] ?? '—' }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        of {{ $metrics['total_applications'] ?? 0 }} total
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8 rw-card-grid">
                <a href="{{ route('admin.users', ['days' => 30]) }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">New Users (30d)</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['new_users_30'] ?? '—' }}</div>
                </a>
                <a href="{{ route('admin.kyc.list', ['status' => 'pending']) }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">Pending KYC</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['pending_kyc'] ?? '—' }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        of {{ $metrics['total_kyc'] ?? 0 }} total
                    </div>
                </a>
                <a href="{{ route('admin.crypto.payments') }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">Crypto Payments</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['pending_crypto_payments'] ?? '—' }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        pending of {{ $metrics['crypto_payments'] ?? 0 }} total
                    </div>
                </a>
                <a href="{{ route('admin.withdrawals') }}" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow block">
                    <div class="text-sm text-gray-600 mb-1">Withdrawal Requests</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $metrics['pending_withdrawals'] ?? '—' }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        pending of {{ $metrics['withdrawal_requests'] ?? 0 }} total
                    </div>
                </a>
            </div>


            <!-- Quick Actions Section -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8 rw-card-grid">
                <!-- Crypto Payments Link -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                    <h3 class="font-montserrat font-bold text-lg text-gray-900 mb-2">Crypto Payments</h3>
                    <p class="text-gray-600 text-sm mb-4">Review and approve crypto payment submissions</p>
                    <a href="{{ route('admin.crypto.payments') }}" class="inline-block btn-secondary btn-small">View Pending</a>
                </div>

                <!-- Withdrawal Requests Link -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                    <h3 class="font-montserrat font-bold text-lg text-gray-900 mb-2">Withdrawal Requests</h3>
                    <p class="text-gray-600 text-sm mb-4">Review and process user withdrawal requests</p>
                    <a href="{{ route('admin.withdrawals') }}" class="inline-block btn-secondary btn-small">Manage Withdrawals</a>
                </div>

                <!-- KYC Review Link -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                    <h3 class="font-montserrat font-bold text-lg text-gray-900 mb-2">KYC Review</h3>
                    <p class="text-gray-600 text-sm mb-4">Review and approve or reject KYC submissions</p>
                    <a href="{{ route('admin.kyc.list') }}" class="inline-block btn-secondary btn-small">View KYC</a>
                </div>

                <!-- Transaction History Link -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                    <h3 class="font-montserrat font-bold text-lg text-gray-900 mb-2">Transaction History</h3>
                    <p class="text-gray-600 text-sm mb-4">View all payment submissions and token transactions</p>
                    <a href="{{ route('admin.history') }}" class="inline-block btn-secondary btn-small">View History</a>
                </div>

                <!-- Price Management Link -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                    <h3 class="font-montserrat font-bold text-lg text-gray-900 mb-2">Price Management</h3>
                    <p class="text-gray-600 text-sm mb-4">Set and update USDT prices for payment calculations</p>
                    <a href="{{ route('admin.prices') }}" class="inline-block btn-secondary btn-small">Manage Prices</a>
                </div>

                <!-- Reseller Applications Link -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                    <h3 class="font-montserrat font-bold text-lg text-gray-900 mb-2">Reseller Applications</h3>
                    <p class="text-gray-600 text-sm mb-4">Review, approve, or reject reseller applications</p>
                    <a href="{{ route('admin.applications') }}" class="inline-block btn-secondary btn-small">View Applications</a>
                </div>

                <!-- Game Settings Quick Link -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                    <h3 class="font-montserrat font-bold text-lg text-gray-900 mb-2">Game Settings</h3>
                    <p class="text-gray-600 text-sm mb-4">Configure entry multiplier, exit divisor, fees, and timeout for the trading game.</p>
                    <button type="button"
                            id="adminGameSettingsBtn"
                            class="inline-block btn-secondary btn-small">
                        Open Game Settings
                    </button>
                </div>
            </div>

            <!-- 2FA Management (Admins) -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
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

                <form method="POST" action="{{ route('admin.regenerate-recovery-codes') }}" class="inline-block mr-2 mb-3">
                    @csrf
                    <button type="submit" class="btn-secondary btn-small">Regenerate Recovery Codes</button>
                </form>

                <form method="POST" action="{{ url('user/two-factor-authentication') }}" class="inline-block mb-3">
                    @csrf
                    @method('DELETE')
                    <button class="btn-primary btn-small">Disable 2FA</button>
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
                <form method="POST" action="{{ url('user/two-factor-authentication') }}" class="mt-2">
                    @csrf
                    <button class="btn-primary btn-small">Enable 2FA</button>
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
</div>
@endsection

@push('scripts')
<script>
(function () {
    const btn = document.getElementById('adminGameSettingsBtn');
    if (!btn) return;

    const modal = document.createElement('div');
    modal.id = 'adminGameSettingsPanel';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4 hidden';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-montserrat font-bold text-gray-900">Game Settings</h3>
                    <p class="text-xs text-gray-500 mt-1">Update trading game multipliers and timeout in real time.</p>
                </div>
                <button type="button"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                        data-gs-close>&times;</button>
            </div>
            <div class="px-6 py-4">
                <form id="gameSettingsForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Entry Multiplier
                        </label>
                        <input type="number" step="0.01" min="0.01"
                               name="entry_multiplier"
                               class="form-input w-full"
                               placeholder="e.g. 10.0">
                        <p class="text-xs text-gray-500 mt-1">
                            How many in-game coins (FOPI) a user receives per 1 RWAMP on game entry.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Exit Divisor
                        </label>
                        <input type="number" step="0.01" min="0.01"
                               name="exit_divisor"
                               class="form-input w-full"
                               placeholder="e.g. 100.0">
                        <p class="text-xs text-gray-500 mt-1">
                            Game balance will be divided by this value to compute RWAMP on exit.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Exit Fee Rate (%)
                        </label>
                        <input type="number" step="0.01" min="0" max="100"
                               name="exit_fee_rate"
                               class="form-input w-full"
                               placeholder="e.g. 2.0">
                        <p class="text-xs text-gray-500 mt-1">
                            Percentage fee applied on the RWAMP value at exit (e.g. 2.0 = 2%).
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Game Timeout (seconds, optional)
                        </label>
                        <input type="number" min="10" max="86400"
                               name="game_timeout_seconds"
                               class="form-input w-full"
                               placeholder="e.g. 120">
                        <p class="text-xs text-gray-500 mt-1">
                            Optional: auto-timeout duration for inactive game sessions.
                        </p>
                    </div>
                    <div id="gameSettingsFeedback" class="hidden text-xs mt-1"></div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-200 mt-4">
                        <button type="button"
                                class="btn-secondary"
                                data-gs-close>
                            Cancel
                        </button>
                        <button type="submit"
                                class="btn-primary"
                                id="gameSettingsSaveBtn">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    const form = modal.querySelector('#gameSettingsForm');
    const feedback = modal.querySelector('#gameSettingsFeedback');
    const saveBtn = modal.querySelector('#gameSettingsSaveBtn');

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loadSettings();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    modal.querySelectorAll('[data-gs-close]').forEach(function (el) {
        el.addEventListener('click', function () {
            closeModal();
        });
    });

    btn.addEventListener('click', function () {
        openModal();
    });

    async function loadSettings() {
        if (!window.fetch) return;
        try {
            const res = await fetch('{{ route('admin.game.settings.show') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            if (!res.ok) return;
            const json = await res.json();
            if (!json || !json.success || !json.data) return;

            const data = json.data;
            form.entry_multiplier.value = data.entry_multiplier ?? '';
            form.exit_divisor.value = data.exit_divisor ?? '';
            form.exit_fee_rate.value = data.exit_fee_rate ?? '';
            form.game_timeout_seconds.value = data.game_timeout_seconds ?? '';

            // Expose globally for any listeners (e.g. game UI)
            window.RWAMP_GAME_SETTINGS = data;
        } catch (e) {
            console.error('Failed to load game settings', e);
        }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!window.fetch) return;

        feedback.classList.remove('hidden', 'text-red-600', 'text-green-600');
        feedback.textContent = '';

        const formData = {
            entry_multiplier: parseFloat(form.entry_multiplier.value || '0'),
            exit_divisor: parseFloat(form.exit_divisor.value || '0'),
            exit_fee_rate: parseFloat(form.exit_fee_rate.value || '0'),
            game_timeout_seconds: form.game_timeout_seconds.value
                ? parseInt(form.game_timeout_seconds.value, 10)
                : null,
        };

        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-75', 'cursor-wait');

        try {
            const res = await fetch('{{ route('admin.game.settings.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(formData)
            });

            const json = await res.json().catch(() => null);

            if (res.ok && json && json.success) {
                feedback.classList.add('text-green-600');
                feedback.textContent = json.message || 'Settings updated.';

                if (json.data) {
                    window.RWAMP_GAME_SETTINGS = json.data;
                }
            } else {
                feedback.classList.add('text-red-600');
                if (json && json.message) {
                    feedback.textContent = json.message;
                } else if (json && json.errors) {
                    const firstError = Object.values(json.errors)[0];
                    feedback.textContent = Array.isArray(firstError) ? firstError[0] : firstError;
                } else {
                    feedback.textContent = 'Failed to update settings. Please try again.';
                }
            }
        } catch (err) {
            console.error('Failed to save game settings', err);
            feedback.classList.add('text-red-600');
            feedback.textContent = 'Network error. Please try again.';
        } finally {
            saveBtn.disabled = false;
            saveBtn.classList.remove('opacity-75', 'cursor-wait');
        }
    });

    // Listen for real-time updates via Echo, if available
    if (window.Echo && typeof window.Echo.private === 'function') {
        try {
            window.Echo.private('game.settings')
                .listen('.GameSettingsUpdated', (e) => {
                    if (!e) return;
                    const data = {
                        entry_multiplier: e.entry_multiplier,
                        exit_divisor: e.exit_divisor,
                        exit_fee_rate: e.exit_fee_rate,
                        game_timeout_seconds: e.game_timeout_seconds,
                    };
                    window.RWAMP_GAME_SETTINGS = data;

                    // If modal is open, update the form fields to reflect the latest values
                    if (!modal.classList.contains('hidden')) {
                        form.entry_multiplier.value = data.entry_multiplier ?? '';
                        form.exit_divisor.value = data.exit_divisor ?? '';
                        form.exit_fee_rate.value = data.exit_fee_rate ?? '';
                        form.game_timeout_seconds.value = data.game_timeout_seconds ?? '';
                    }
                });
        } catch (err) {
            console.warn('Unable to subscribe to game.settings channel', err);
        }
    }
})();
</script>
@endpush


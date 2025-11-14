@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Your Profile</h1>
                <p class="text-white/80 mt-2">Manage your account, wallet and security settings.</p>
            </div>
            @php
                $dashboardRoute = auth()->user()->role === 'admin'
                    ? route('dashboard.admin')
                    : (auth()->user()->role === 'reseller' ? route('dashboard.reseller') : route('dashboard.investor'));
            @endphp
            <div>
                <a href="{{ $dashboardRoute }}" class="btn-secondary">Go to Dashboard</a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Wallet and quick info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
                    <h2 class="text-xl font-montserrat font-bold mb-4">Wallet</h2>
                    <div class="text-sm text-gray-600 mb-1">Wallet Address</div>
                    <div class="font-mono break-all text-gray-900 mb-4">{{ $user->wallet_address ?? 'Not set' }}</div>
                    <button 
                        type="button" 
                        onclick="document.getElementById('wallet-modal').classList.remove('hidden')"
                        class="mb-4 w-full btn-secondary text-sm"
                    >
                        Update Wallet Address
                    </button>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="bg-black text-white rounded-lg p-4 text-center">
                            <div class="text-xs text-white/70">Token Balance</div>
                            <div class="text-2xl font-bold">{{ number_format($user->token_balance ?? 0) }}</div>
                        </div>
                        <div class="bg-accent text-black rounded-lg p-4 text-center">
                            <div class="text-xs text-black/70">Value (Rs)</div>
                            <div class="text-2xl font-bold">{{ number_format(($user->token_balance ?? 0) * 0.70, 2) }}</div>
                        </div>
                    </div>
                    @if($user->kyc_status === 'approved')
                        <a href="{{ route('purchase.create') }}" class="mt-4 inline-block btn-primary w-full text-center">Purchase Tokens</a>
                    @else
                        <a href="{{ route('kyc.show') }}" class="mt-4 inline-block btn-primary w-full text-center">Complete KYC to Purchase</a>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
                    <h3 class="text-lg font-montserrat font-bold mb-3">KYC Status</h3>
                    @if($user->kyc_status === 'approved')
                        <div class="mb-3">
                            <span class="rw-badge bg-green-100 text-green-800">✅ KYC Verified</span>
                        </div>
                        <p class="text-sm text-gray-600">Your KYC has been approved. You have full access to all features.</p>
                        @if($user->kyc_approved_at)
                            <p class="text-xs text-gray-500 mt-2">Approved on: {{ $user->kyc_approved_at->format('F d, Y') }}</p>
                        @endif
                    @elseif($user->kyc_status === 'pending')
                        <div class="mb-3">
                            <span class="rw-badge bg-yellow-100 text-yellow-800">⏳ Under Review</span>
                        </div>
                        <p class="text-sm text-gray-600">Your KYC submission is under review. Admins will notify you once it's processed.</p>
                        @if($user->kyc_submitted_at)
                            <p class="text-xs text-gray-500 mt-2">Submitted on: {{ $user->kyc_submitted_at->format('F d, Y') }}</p>
                        @endif
                    @elseif($user->kyc_status === 'rejected')
                        <div class="mb-3">
                            <span class="rw-badge bg-red-100 text-red-800">❌ Rejected</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Your KYC submission was rejected. Please review the requirements and resubmit.</p>
                        <a href="{{ route('kyc.show') }}" class="btn-primary text-sm px-4 py-2">Resubmit KYC</a>
                    @else
                        <div class="mb-3">
                            <span class="rw-badge bg-gray-100 text-gray-800">Not Started</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Complete KYC verification to access purchase and investor features.</p>
                        <a href="{{ route('kyc.show') }}" class="btn-primary text-sm px-4 py-2">Start KYC</a>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
                    <h3 class="text-lg font-montserrat font-bold mb-3">Email Verification</h3>
                    @if($user->email_verified_at)
                        <div class="mb-3">
                            <span class="rw-badge bg-green-100 text-green-800">✅ Verified</span>
                        </div>
                        <p class="text-sm text-gray-600">Your email address has been verified.</p>
                        @if($user->email_verified_at)
                            <p class="text-xs text-gray-500 mt-2">Verified on: {{ $user->email_verified_at->format('F d, Y') }}</p>
                        @endif
                    @else
                        <div class="mb-3">
                            <span class="rw-badge bg-yellow-100 text-yellow-800">⚠️ Not Verified</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Please verify your email address to access all features.</p>
                        <form method="POST" action="{{ route('email.verification.resend') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary text-sm px-4 py-2">Verify Email</button>
                        </form>
                    @endif
                </div>

                @if($user->reseller_id)
                <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
                    <h3 class="text-lg font-montserrat font-bold mb-3">Referral Information</h3>
                    @php
                        $reseller = $user->reseller;
                    @endphp
                    @if($reseller)
                        <div class="mb-3">
                            <span class="rw-badge bg-blue-100 text-blue-800">✓ Referred by Reseller</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Reseller:</strong> {{ $reseller->name }}
                        </p>
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Email:</strong> {{ $reseller->email }}
                        </p>
                        @if($reseller->referral_code)
                            <p class="text-sm text-gray-600">
                                <strong>Referral Code:</strong> <code class="bg-gray-100 px-2 py-1 rounded">{{ $reseller->referral_code }}</code>
                            </p>
                        @endif
                    @else
                        <p class="text-sm text-gray-600">Reseller information not available.</p>
                    @endif
                </div>
                @endif

                <div class="bg-white rounded-xl shadow-xl p-6 card-hover animate-fadeInUp">
                    <h3 class="text-lg font-montserrat font-bold mb-3">Security</h3>
                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                        <li>Use a strong password</li>
                        <li>Enable 2FA (coming soon)</li>
                    </ul>
                </div>
            </div>

            <!-- Right: Forms and history -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
                    <h2 class="text-xl font-montserrat font-bold mb-6">Account Details</h2>
                    <form method="POST" action="{{ route('account.update') }}" class="grid md:grid-cols-2 gap-6">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input name="name" type="text" value="{{ old('name', $user->name) }}" class="form-input" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input name="email" type="email" value="{{ old('email', $user->email) }}" class="form-input" required />
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
                    <h2 class="text-xl font-montserrat font-bold mb-6">Change Password</h2>
                    <form method="POST" action="{{ route('account.password') }}" class="grid md:grid-cols-3 gap-6">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                            <input name="current_password" type="password" class="form-input" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input name="password" type="password" class="form-input" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <input name="password_confirmation" type="password" class="form-input" required />
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-montserrat font-bold">Transaction History</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600 border-b">
                                    <th class="py-3 pr-6">Date</th>
                                    <th class="py-3 pr-6">Type</th>
                                    <th class="py-3 pr-6">Amount</th>
                                    <th class="py-3 pr-6">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-900">
                                @forelse(($transactions ?? []) as $t)
                                    <tr class="border-b">
                                        <td class="py-3 pr-6">{{ $t->created_at?->format('Y-m-d') }}</td>
                                        <td class="py-3 pr-6">{{ str_replace('_', ' ', ucfirst($t->type)) }}</td>
                                        <td class="py-3 pr-6">{{ number_format($t->amount, 2) }} RWAMP</td>
                                        <td class="py-3 pr-6"><span class="rw-badge">{{ ucfirst($t->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-6 text-center text-gray-500">No transactions yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="text-right">
                    @csrf
                    <button type="submit" class="btn-secondary">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Address Update Modal -->
<div id="wallet-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-montserrat font-bold">Update Wallet Address</h3>
            <button 
                type="button" 
                onclick="document.getElementById('wallet-modal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-2xl"
            >
                &times;
            </button>
        </div>
        <form method="POST" action="{{ route('wallet.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Wallet Address</label>
                <input 
                    name="wallet_address" 
                    type="text" 
                    value="{{ old('wallet_address', $user->wallet_address) }}" 
                    class="form-input w-full" 
                    placeholder="0x..." 
                    required 
                />
                <p class="text-xs text-gray-500 mt-1">Enter your cryptocurrency wallet address</p>
            </div>
            <div class="flex gap-3">
                <button 
                    type="button" 
                    onclick="document.getElementById('wallet-modal').classList.add('hidden')"
                    class="flex-1 btn-secondary"
                >
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection



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
                    @if($user->wallet_address)
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <span id="walletDisplay" class="font-mono bg-gray-100 px-2 py-1 rounded text-gray-900 break-all">
                                    {{ substr($user->wallet_address, 0, 4) }} **** **** {{ substr($user->wallet_address, -4) }}
                                </span>
                                <button 
                                    type="button" 
                                    onclick="copyText('{{ $user->wallet_address }}')" 
                                    class="ml-2 text-sm text-blue-600 hover:text-blue-800 font-medium px-2 py-1 hover:bg-blue-50 rounded transition-colors"
                                >
                                    Copy
                                </button>
                            </div>
                            <button 
                                type="button" 
                                onclick="toggleWalletAddress()" 
                                id="toggleWalletBtn"
                                class="text-xs text-gray-600 hover:text-gray-800 font-medium underline"
                            >
                                Show Full Address
                            </button>
                        </div>
                    @else
                        <div class="mb-4">
                            <div class="font-mono break-all text-gray-500 mb-3">Not set</div>
                            <form method="POST" action="{{ route('wallet.generate') }}" id="generateWalletForm">
                                @csrf
                                <button 
                                    type="submit" 
                                    id="generateWalletBtn"
                                    class="w-full btn-primary text-sm py-2.5 flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Generate Wallet Address
                                </button>
                            </form>
                        </div>
                    @endif
                    <p class="text-xs text-gray-500 mb-4">Your wallet address is auto-generated and used for secure internal transfers.</p>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="bg-black text-white rounded-lg p-4 text-center">
                            <div class="text-xs text-white/70">Token Balance</div>
                            <div class="text-2xl font-bold">{{ number_format($user->token_balance ?? 0) }}</div>
                        </div>
                        <div class="bg-accent text-black rounded-lg p-4 text-center">
                            <div class="text-xs text-black/70">Value (Rs)</div>
                            <div class="text-2xl font-bold">{{ number_format(($user->token_balance ?? 0) * ($officialPrice ?? 0.70), 2) }}</div>
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
                                    <th class="py-3 pr-6">Price (Rs)</th>
                                    <th class="py-3 pr-6">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-900">
                                @forelse(($transactions ?? []) as $t)
                                    <tr class="border-b">
                                        <td class="py-3 pr-6">{{ $t->created_at?->format('Y-m-d') }}</td>
                                        <td class="py-3 pr-6">{{ str_replace('_', ' ', ucfirst($t->type)) }}</td>
                                        <td class="py-3 pr-6">{{ number_format($t->amount, 2) }} RWAMP</td>
                                        <td class="py-3 pr-6">
                                            @if($t->price_per_coin && $t->price_per_coin > 0)
                                                {{ number_format($t->price_per_coin, 2) }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 pr-6"><span class="rw-badge">{{ ucfirst($t->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-6 text-center text-gray-500">No transactions yet.</td></tr>
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

<!-- Toast Notification -->
<div id="copyToast" class="hidden fixed top-4 right-4 z-50 max-w-sm w-full">
    <div id="copyToastContent" class="bg-white rounded-lg shadow-lg border p-4 flex items-center justify-between">
        <div class="flex items-center">
            <div id="copyToastIcon" class="mr-3"></div>
            <div>
                <p id="copyToastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
        </div>
        <button onclick="hideCopyToast()" class="ml-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<script>
let walletAddressVisible = false;
const fullWalletAddress = '{{ $user->wallet_address ?? "" }}';
const maskedWalletAddress = '{{ $user->wallet_address ? substr($user->wallet_address, 0, 4) . " **** **** " . substr($user->wallet_address, -4) : "" }}';

// Handle wallet generation form submission
document.addEventListener('DOMContentLoaded', function() {
    const generateWalletForm = document.getElementById('generateWalletForm');
    const generateWalletBtn = document.getElementById('generateWalletBtn');
    
    if (generateWalletForm && generateWalletBtn) {
        generateWalletForm.addEventListener('submit', function(e) {
            // Disable button and show loading state
            generateWalletBtn.disabled = true;
            generateWalletBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
            `;
        });
    }
});

function toggleWalletAddress() {
    const walletDisplay = document.getElementById('walletDisplay');
    const toggleBtn = document.getElementById('toggleWalletBtn');
    
    if (!walletDisplay || !toggleBtn) return;
    
    walletAddressVisible = !walletAddressVisible;
    
    if (walletAddressVisible) {
        walletDisplay.textContent = fullWalletAddress;
        toggleBtn.textContent = 'Hide Full Address';
    } else {
        walletDisplay.textContent = maskedWalletAddress;
        toggleBtn.textContent = 'Show Full Address';
    }
}

function showCopyToast(message, isSuccess = true) {
    const toast = document.getElementById('copyToast');
    const toastContent = document.getElementById('copyToastContent');
    const toastIcon = document.getElementById('copyToastIcon');
    const toastMessage = document.getElementById('copyToastMessage');
    
    if (!toast || !toastContent || !toastIcon || !toastMessage) return;
    
    // Set message
    toastMessage.textContent = message;
    
    // Set icon and styling based on success/error
    if (isSuccess) {
        toastIcon.innerHTML = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        toastContent.className = 'bg-white rounded-lg shadow-lg border border-green-200 p-4 flex items-center justify-between';
    } else {
        toastIcon.innerHTML = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        toastContent.className = 'bg-white rounded-lg shadow-lg border border-red-200 p-4 flex items-center justify-between';
    }
    
    // Show toast with animation
    toast.classList.remove('hidden');
    toast.classList.add('animate-fadeIn');
    
    // Auto-hide after 3 seconds
    setTimeout(function() {
        hideCopyToast();
    }, 3000);
}

function hideCopyToast() {
    const toast = document.getElementById('copyToast');
    if (toast) {
        toast.classList.add('hidden');
        toast.classList.remove('animate-fadeIn');
    }
}

function copyText(text) {
    if (!text) {
        showCopyToast('No wallet address to copy', false);
        return;
    }
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showCopyToast('Wallet address copied to clipboard!', true);
        }, function(err) {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopyToast('Wallet address copied to clipboard!', true);
        } else {
            showCopyToast('Failed to copy. Please copy manually.', false);
        }
    } catch (err) {
        console.error('Fallback copy failed: ', err);
        showCopyToast('Failed to copy. Please copy manually.', false);
    }
    
    document.body.removeChild(textArea);
}
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endsection



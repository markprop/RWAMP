@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-montserrat font-bold">Sell Coins</h1>
                    <p class="text-white/80 text-sm sm:text-base mt-1">Transfer tokens to users/resellers (OTP protected)</p>
                </div>
                <a href="{{ route('dashboard.admin') }}" class="btn-secondary text-center text-sm sm:text-base px-4 py-2 sm:px-6 sm:py-3 whitespace-nowrap">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Sell Form -->
            <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6">
                <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Transfer Tokens</h2>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                    <p class="text-xs sm:text-sm text-yellow-800">
                        <strong>Note:</strong> You can sell tokens to any user or reseller. An OTP will be sent to your email for security verification.
                    </p>
                </div>

                <!-- Coin Price Calculator -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                    <h3 class="font-semibold text-blue-900 mb-2 sm:mb-3 text-sm sm:text-base">Coin Price Calculator</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-blue-800 mb-1">Coin Price (PKR per coin)</label>
                            <div class="flex gap-2">
                                <input 
                                    type="number" 
                                    id="coinPriceInput" 
                                    value="{{ $defaultPrice }}" 
                                    step="0.01" 
                                    min="0.01"
                                    class="flex-1 form-input text-sm"
                                    placeholder="Enter price"
                                >
                            </div>
                            <p class="text-xs text-blue-600 mt-1">
                                Default: PKR {{ number_format($defaultPrice, 2) }} (Super-Admin Price)
                            </p>
                        </div>
                    </div>
                </div>

                <form id="sellForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Enter Wallet Address (16 digits) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="walletAddressInput" 
                                class="form-input w-full pr-10 font-mono" 
                                placeholder="Enter 16-digit wallet address..."
                                autocomplete="off"
                                maxlength="16"
                                pattern="[0-9]{16}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)"
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div id="walletLookupError" class="hidden mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800" id="walletLookupErrorMessage"></p>
                        </div>
                        <div id="walletLookupLoading" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">Looking up wallet address...</p>
                        </div>
                        <div id="selectedUser" class="hidden mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-green-900">✓ Found: <span id="selectedUserName"></span></p>
                                </div>
                                <button type="button" onclick="clearSelectedUser()" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="recipientId" required>
                        <p class="text-xs text-gray-500 mt-1">Enter the 16-digit wallet address of the recipient</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Coin Quantity (RWAMP) <span class="text-red-500">*</span></label>
                        <input 
                            type="number" 
                            id="coinQuantity" 
                            class="form-input" 
                            min="1" 
                            step="1" 
                            required
                            placeholder="Enter quantity"
                            oninput="calculateTotal()"
                        >
                    </div>

                    <!-- Total Price Display -->
                    <div id="totalPriceSection" class="hidden">
                        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-yellow-900">Total Price:</p>
                                    <p class="text-2xl font-bold text-yellow-700" id="totalPrice">PKR 0.00</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-yellow-600">Price per coin:</p>
                                    <p class="text-sm font-semibold text-yellow-800" id="pricePerCoin">PKR 0.00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information Section -->
                    <div id="paymentSection" class="hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-blue-900 mb-3">Payment Information</h4>
                            <p class="text-sm text-blue-800 mb-4">Have you received payment from the user/reseller?</p>
                            
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="paymentReceived" value="yes" class="mr-2" onchange="togglePaymentDetails(true)">
                                    <span class="text-sm font-medium">Yes, I have received payment</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="paymentReceived" value="no" class="mr-2" checked onchange="togglePaymentDetails(false)">
                                    <span class="text-sm font-medium">No, payment not received yet</span>
                                </label>
                            </div>

                            <!-- Payment Type Selection (shown when payment received = yes) -->
                            <div id="paymentTypeSection" class="hidden mt-4">
                                <label class="block text-sm font-medium mb-2">Payment Type <span class="text-red-500">*</span></label>
                                <select id="paymentType" class="form-input" onchange="handlePaymentTypeChange()">
                                    <option value="">Select payment type</option>
                                    <option value="cash">Cash</option>
                                    <option value="usdt">USDT (Wallet Connect)</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>

                                <!-- USDT Payment Section -->
                                <div id="usdtPaymentSection" class="hidden mt-4">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <p class="text-sm text-gray-700 mb-3">Fetching payment proof from user's account...</p>
                                        <div id="usdtProofLoading" class="text-center py-4">
                                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                            <p class="text-xs text-gray-600 mt-2">Loading transaction hash...</p>
                                        </div>
                                        <div id="usdtProofDisplay" class="hidden">
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                                                <p class="text-sm font-semibold text-green-900 mb-2">✓ Payment Proof Found</p>
                                                <div class="space-y-1 text-xs">
                                                    <p><span class="font-medium">Transaction Hash:</span> <span id="proofTxHash" class="font-mono break-all"></span></p>
                                                    <p><span class="font-medium">Network:</span> <span id="proofNetwork"></span></p>
                                                    <p><span class="font-medium">Amount:</span> <span id="proofAmount"></span> RWAMP</p>
                                                    <p><span class="font-medium">Date:</span> <span id="proofDate"></span></p>
                                                </div>
                                                <button type="button" onclick="copyToClipboard(document.getElementById('proofTxHash').textContent)" class="mt-2 text-xs text-blue-600 hover:underline">
                                                    Copy Transaction Hash
                                                </button>
                                            </div>
                                            <input type="hidden" id="paymentHash" value="">
                                        </div>
                                        <div id="usdtProofError" class="hidden bg-red-50 border border-red-200 rounded-lg p-3">
                                            <p class="text-sm text-red-800" id="usdtProofErrorMessage"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Payment Section -->
                                <div id="bankPaymentSection" class="hidden mt-4">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <p class="text-sm text-gray-700 mb-3">Fetching payment receipt from user's account...</p>
                                        <div id="bankProofLoading" class="text-center py-4">
                                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                            <p class="text-xs text-gray-600 mt-2">Loading receipt...</p>
                                        </div>
                                        <div id="bankProofDisplay" class="hidden">
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                                                <p class="text-sm font-semibold text-green-900 mb-2">✓ Payment Receipt Found</p>
                                                <div class="space-y-1 text-xs mb-3">
                                                    <p><span class="font-medium">Amount:</span> <span id="bankProofAmount"></span> RWAMP</p>
                                                    <p><span class="font-medium">Date:</span> <span id="bankProofDate"></span></p>
                                                </div>
                                                <div class="mt-3">
                                                    <a id="bankReceiptLink" href="" target="_blank" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                                        View Receipt/Screenshot
                                                    </a>
                                                </div>
                                            </div>
                                            <input type="hidden" id="paymentReceipt" value="">
                                        </div>
                                        <div id="bankProofError" class="hidden bg-red-50 border border-red-200 rounded-lg p-3">
                                            <p class="text-sm text-red-800" id="bankProofErrorMessage"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cash Payment Section -->
                                <div id="cashPaymentSection" class="hidden mt-4">
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                        <p class="text-sm text-green-800">✓ Cash payment selected. No additional details required.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Your Email (for OTP) <span class="text-red-500">*</span></label>
                        <input 
                            type="email" 
                            id="sellEmail" 
                            value="{{ auth()->user()->email }}" 
                            class="form-input" 
                            required
                            readonly
                        >
                        <p class="text-xs text-gray-500 mt-1">OTP will be sent to this email</p>
                    </div>
                    
                    <div id="otpSection" class="hidden">
                        <label class="block text-sm font-medium mb-2">OTP Code <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            id="sellOtp" 
                            class="form-input" 
                            maxlength="10" 
                            placeholder="Enter 6-digit OTP (spaces will be removed)"
                            oninput="this.value = this.value.replace(/\s+/g, '').slice(0, 6)"
                        >
                        <button type="button" onclick="sendOtp()" class="text-sm text-primary mt-1 hover:underline">
                            Resend OTP
                        </button>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="sendOtp()" id="sendOtpBtn" class="btn-primary flex-1">
                            Send OTP
                        </button>
                        <button type="button" id="submitSellBtn" class="btn-primary flex-1 hidden" onclick="document.getElementById('sellForm').dispatchEvent(new Event('submit'))">
                            Confirm Transfer
                        </button>
                    </div>
                </form>

                <div id="successMessage" class="hidden mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <p>Tokens transferred successfully!</p>
                </div>

                <div id="errorMessage" class="hidden mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <p id="errorText"></p>
                </div>

                <!-- Info/Notification Message -->
                <div id="infoMessage" class="hidden mt-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg flex items-center justify-between">
                    <p id="infoText"></p>
                    <button onclick="hideInfo()" class="text-blue-700 hover:text-blue-900 ml-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-white rounded-xl shadow-xl p-6">
                <h2 class="text-2xl font-bold mb-6">How to Sell Coins</h2>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
                        <div>
                            <h3 class="font-semibold mb-1">Search User/Reseller</h3>
                            <p class="text-sm text-gray-600">Search for any user or reseller by name, email, or user ID.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                        <div>
                            <h3 class="font-semibold mb-1">Set Price & Enter Quantity</h3>
                            <p class="text-sm text-gray-600">Set the coin price and enter the number of RWAMP tokens you want to transfer.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
                        <div>
                            <h3 class="font-semibold mb-1">Send OTP</h3>
                            <p class="text-sm text-gray-600">Click "Send OTP" button. A 6-digit code will be sent to your registered email address.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">4</div>
                        <div>
                            <h3 class="font-semibold mb-1">Enter OTP & Confirm</h3>
                            <p class="text-sm text-gray-600">Enter the OTP code you received and click "Confirm Transfer" to complete the transaction.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">Transaction Tracking</h3>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>All transactions are logged with sender and recipient details</li>
                        <li>Price per coin and total price are recorded</li>
                        <li>Transaction history is available for audit purposes</li>
                        <li>OTP verification required for all transfers</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store routes in variables to avoid Blade compilation issues
const searchUsersRoute = '{!! route('admin.search-users') !!}';
const sendOtpRoute = '{!! route('admin.send-otp') !!}';
const sellCoinsRoute = '{!! route('admin.sell-coins') !!}';
const adminDashboardRoute = '{!! route('dashboard.admin') !!}';

let searchTimeout;
let selectedUserData = null;
let coinPrice = {{ (float) $defaultPrice }};
let defaultPrice = {{ (float) $defaultPrice }};

// Utility function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Wallet lookup functionality - wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    const walletAddressInput = document.getElementById('walletAddressInput');
    if (!walletAddressInput) {
        console.error('Wallet address input not found!');
        return;
    }
    
    // Pre-select user if user_id is provided in URL (for backward compatibility)
    @if(isset($preSelectedUser) && $preSelectedUser)
        const preSelectedUser = {
            id: {{ $preSelectedUser->id }},
            name: {!! json_encode($preSelectedUser->name) !!},
            email: {!! json_encode($preSelectedUser->email) !!},
            token_balance: {{ (float) ($preSelectedUser->token_balance ?? 0) }},
            role: {!! json_encode($preSelectedUser->role ?? 'investor') !!}
        };
        
        // Pre-select the user
        selectUser(
            preSelectedUser.id,
            preSelectedUser.name,
            preSelectedUser.email,
            preSelectedUser.token_balance,
            preSelectedUser.role
        );
    @endif
    
    // Wallet lookup on blur
    walletAddressInput.addEventListener('blur', function(e) {
        const wallet = e.target.value.trim();
        
        // Only lookup if wallet is exactly 16 digits
        if (wallet.length === 16 && /^\d{16}$/.test(wallet)) {
            lookupWalletAddress(wallet);
        } else if (wallet.length > 0) {
            showWalletError('Wallet address must be exactly 16 digits');
        }
    });
    
    // Also lookup on Enter key
    walletAddressInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const wallet = e.target.value.trim();
            if (wallet.length === 16 && /^\d{16}$/.test(wallet)) {
                lookupWalletAddress(wallet);
            } else if (wallet.length > 0) {
                showWalletError('Wallet address must be exactly 16 digits');
            }
        }
    });
});

async function lookupWalletAddress(wallet) {
    // Hide previous errors and results
    hideWalletError();
    document.getElementById('selectedUser').classList.add('hidden');
    document.getElementById('walletLookupLoading').classList.remove('hidden');
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page.');
        }
        
        const response = await fetch('{{ route("api.users.lookup-by-wallet") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ wallet: wallet })
        });
        
        // Check content-type before parsing
        const contentType = response.headers.get('content-type');
        const isJson = contentType && contentType.includes('application/json');
        
        // Get response text first
        const responseText = await response.text();
        
        // Check if response is ok before parsing JSON
        if (!response.ok) {
            let errorMessage = 'Wallet address not found';
            
            if (isJson) {
                try {
                    const errorData = JSON.parse(responseText);
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    console.error('Error parsing JSON error response:', e);
                    errorMessage = response.status === 404 ? 'Wallet address not found' : 'Error looking up wallet address';
                }
            } else {
                // Response is HTML (likely an error page)
                console.error('Server returned HTML instead of JSON. Status:', response.status);
                console.error('Response preview:', responseText.substring(0, 200));
                
                if (response.status === 403) {
                    errorMessage = 'You do not have permission to lookup wallet addresses.';
                } else if (response.status === 401) {
                    errorMessage = 'Please log in to lookup wallet addresses.';
                } else if (response.status === 404) {
                    errorMessage = 'Wallet address not found';
                } else if (response.status === 422) {
                    errorMessage = 'Invalid wallet address format. Must be exactly 16 digits.';
                } else {
                    errorMessage = 'Server error. Please try again or contact support.';
                }
            }
            
            document.getElementById('walletLookupLoading').classList.add('hidden');
            showWalletError(errorMessage);
            return;
        }
        
        // Parse JSON only if response is ok and is JSON
        if (!isJson) {
            console.error('Server returned non-JSON response:', responseText.substring(0, 200));
            document.getElementById('walletLookupLoading').classList.add('hidden');
            showWalletError('Server returned invalid response. Please try again.');
            return;
        }
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Error parsing JSON response:', e);
            console.error('Response text:', responseText.substring(0, 500));
            document.getElementById('walletLookupLoading').classList.add('hidden');
            showWalletError('Error parsing server response. Please try again.');
            return;
        }
        
        document.getElementById('walletLookupLoading').classList.add('hidden');
        
        if (data.name && data.id) {
            // Success - user found
            selectedUserData = { id: data.id, name: data.name };
            document.getElementById('recipientId').value = data.id;
            document.getElementById('selectedUserName').textContent = data.name;
            document.getElementById('selectedUser').classList.remove('hidden');
            
            // Show payment section if quantity is already entered
            const quantity = parseFloat(document.getElementById('coinQuantity')?.value) || 0;
            if (quantity > 0) {
                document.getElementById('paymentSection')?.classList.remove('hidden');
            }
            
            // If payment type is already selected, fetch proof
            const paymentType = document.getElementById('paymentType')?.value;
            if (paymentType && (paymentType === 'usdt' || paymentType === 'bank')) {
                fetchPaymentProof(data.id, paymentType);
            }
        } else {
            // User not found
            showWalletError(data.error || 'Wallet address not found');
        }
    } catch (error) {
        console.error('Wallet lookup error:', error);
        document.getElementById('walletLookupLoading').classList.add('hidden');
        
        // Provide more specific error message
        if (error.message && error.message.includes('JSON')) {
            showWalletError('Server error. The server returned an invalid response. Please try again.');
        } else if (error.message && error.message.includes('CSRF')) {
            showWalletError('Security token expired. Please refresh the page and try again.');
        } else if (error.message && error.message.includes('fetch')) {
            showWalletError('Network error. Please check your internet connection and try again.');
        } else {
            showWalletError('Error looking up wallet address. Please try again.');
        }
    }
}

function showWalletError(message) {
    document.getElementById('walletLookupError').classList.remove('hidden');
    document.getElementById('walletLookupErrorMessage').textContent = message;
    document.getElementById('recipientId').value = '';
    selectedUserData = null;
}

function hideWalletError() {
    document.getElementById('walletLookupError').classList.add('hidden');
}

function selectUser(userId, userName, userEmail, userBalance, userRole) {
    selectedUserData = { id: userId, name: userName, email: userEmail, balance: userBalance, role: userRole };
    
    document.getElementById('recipientId').value = userId;
    document.getElementById('selectedUserName').textContent = userName;
    if (document.getElementById('selectedUserEmail')) {
        document.getElementById('selectedUserEmail').textContent = userEmail;
    }
    if (document.getElementById('selectedUserBalance')) {
        document.getElementById('selectedUserBalance').textContent = (userBalance || 0).toLocaleString();
    }
    if (document.getElementById('selectedUserRole')) {
        document.getElementById('selectedUserRole').textContent = (userRole || 'investor').charAt(0).toUpperCase() + (userRole || 'investor').slice(1);
    }
    
    document.getElementById('walletAddressInput').value = '';
    document.getElementById('selectedUser').classList.remove('hidden');
    
    // Show payment section if quantity is already entered
    const quantity = parseFloat(document.getElementById('coinQuantity')?.value) || 0;
    if (quantity > 0) {
        document.getElementById('paymentSection')?.classList.remove('hidden');
    }
    
    // If payment type is already selected, fetch proof
    const paymentType = document.getElementById('paymentType')?.value;
    if (paymentType && (paymentType === 'usdt' || paymentType === 'bank')) {
        fetchPaymentProof(userId, paymentType);
    }
}

function clearSelectedUser() {
    selectedUserData = null;
    document.getElementById('recipientId').value = '';
    document.getElementById('selectedUser').classList.add('hidden');
    document.getElementById('walletAddressInput').value = '';
    hideWalletError();
}

// Payment handling functions
let walletProvider = null;
let connectedWalletAddress = null;

function togglePaymentDetails(received) {
    const paymentTypeSection = document.getElementById('paymentTypeSection');
    if (received) {
        paymentTypeSection.classList.remove('hidden');
    } else {
        paymentTypeSection.classList.add('hidden');
        document.getElementById('paymentType').value = '';
        handlePaymentTypeChange();
    }
}

async function handlePaymentTypeChange() {
    const paymentType = document.getElementById('paymentType').value;
    const userId = document.getElementById('recipientId').value;
    
    // Hide all payment sections
    document.getElementById('usdtPaymentSection').classList.add('hidden');
    document.getElementById('bankPaymentSection').classList.add('hidden');
    document.getElementById('cashPaymentSection').classList.add('hidden');
    
    // Reset proof displays
    document.getElementById('usdtProofLoading').classList.add('hidden');
    document.getElementById('usdtProofDisplay').classList.add('hidden');
    document.getElementById('usdtProofError').classList.add('hidden');
    document.getElementById('bankProofLoading').classList.add('hidden');
    document.getElementById('bankProofDisplay').classList.add('hidden');
    document.getElementById('bankProofError').classList.add('hidden');
    
    // Show relevant section and fetch proof
    if (paymentType === 'usdt') {
        document.getElementById('usdtPaymentSection').classList.remove('hidden');
        if (userId) {
            await fetchPaymentProof(userId, 'usdt');
        }
    } else if (paymentType === 'bank') {
        document.getElementById('bankPaymentSection').classList.remove('hidden');
        if (userId) {
            await fetchPaymentProof(userId, 'bank');
        }
    } else if (paymentType === 'cash') {
        document.getElementById('cashPaymentSection').classList.remove('hidden');
    }
}

async function fetchPaymentProof(userId, paymentType) {
    if (!userId) {
        showError('Please select a user/reseller first');
        return;
    }

    try {
        // Show loading state
        if (paymentType === 'usdt') {
            document.getElementById('usdtProofLoading').classList.remove('hidden');
            document.getElementById('usdtProofDisplay').classList.add('hidden');
            document.getElementById('usdtProofError').classList.add('hidden');
        } else if (paymentType === 'bank') {
            document.getElementById('bankProofLoading').classList.remove('hidden');
            document.getElementById('bankProofDisplay').classList.add('hidden');
            document.getElementById('bankProofError').classList.add('hidden');
        }

        const response = await fetch('/api/admin/fetch-payment-proof', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                payment_type: paymentType
            })
        });

        const data = await response.json();

        if (data.success && data.proof) {
            // Display proof
            if (paymentType === 'usdt') {
                document.getElementById('usdtProofLoading').classList.add('hidden');
                document.getElementById('proofTxHash').textContent = data.proof.tx_hash;
                document.getElementById('proofNetwork').textContent = data.proof.network;
                document.getElementById('proofAmount').textContent = data.proof.amount;
                document.getElementById('proofDate').textContent = data.proof.date;
                document.getElementById('paymentHash').value = data.proof.tx_hash;
                document.getElementById('usdtProofDisplay').classList.remove('hidden');
            } else if (paymentType === 'bank') {
                document.getElementById('bankProofLoading').classList.add('hidden');
                document.getElementById('bankProofAmount').textContent = data.proof.amount;
                document.getElementById('bankProofDate').textContent = data.proof.date;
                document.getElementById('bankReceiptLink').href = '/storage/' + data.proof.screenshot;
                document.getElementById('paymentReceipt').value = data.proof.screenshot;
                document.getElementById('bankProofDisplay').classList.remove('hidden');
            }
        } else {
            // Show error
            if (paymentType === 'usdt') {
                document.getElementById('usdtProofLoading').classList.add('hidden');
                document.getElementById('usdtProofErrorMessage').textContent = data.message || 'Failed to fetch payment proof';
                document.getElementById('usdtProofError').classList.remove('hidden');
            } else if (paymentType === 'bank') {
                document.getElementById('bankProofLoading').classList.add('hidden');
                document.getElementById('bankProofErrorMessage').textContent = data.message || 'Failed to fetch payment proof';
                document.getElementById('bankProofError').classList.remove('hidden');
            }
        }
    } catch (error) {
        console.error('Error fetching payment proof:', error)
        if (paymentType === 'usdt') {
            document.getElementById('usdtProofLoading').classList.add('hidden');
            document.getElementById('usdtProofErrorMessage').textContent = 'Error fetching payment proof. Please try again.';
            document.getElementById('usdtProofError').classList.remove('hidden');
        } else if (paymentType === 'bank') {
            document.getElementById('bankProofLoading').classList.add('hidden');
            document.getElementById('bankProofErrorMessage').textContent = 'Error fetching payment proof. Please try again.';
            document.getElementById('bankProofError').classList.remove('hidden');
        }
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Transaction hash copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy. Hash: ' + text);
    });
}

async function connectWallet() {
    if (typeof window.ethereum === 'undefined') {
        showError('MetaMask or other Web3 wallet not found. Please install MetaMask.');
        return;
    }

    try {
        const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
        if (accounts && accounts.length > 0) {
            connectedWalletAddress = accounts[0];
            walletProvider = window.ethereum;
            
            document.getElementById('walletAddress').textContent = 'Address: ' + connectedWalletAddress.substring(0, 6) + '...' + connectedWalletAddress.substring(38);
            document.getElementById('walletConnectedInfo').classList.remove('hidden');
            document.getElementById('connectWalletBtn').textContent = 'Wallet Connected';
            document.getElementById('connectWalletBtn').disabled = true;
            document.getElementById('fetchTxHashBtn').classList.remove('hidden');
            
            showInfo('Wallet connected successfully!');
        }
    } catch (error) {
        console.error('Wallet connection error:', error)
        showError('Failed to connect wallet. Please try again.');
    }
}

async function fetchTransactionHash() {
    if (!connectedWalletAddress || !walletProvider) {
        showError('Please connect your wallet first.');
        return;
    }

    try {
        showInfo('Fetching latest transaction...');
        showInfo('Please enter the transaction hash manually. Automatic fetching will be available soon.');
        document.getElementById('paymentHash').readOnly = false;
        document.getElementById('paymentHash').placeholder = 'Enter USDT transaction hash';
    } catch (error) {
        console.error('Error fetching transaction:', error)
        showError('Could not fetch transaction automatically. Please enter hash manually.');
        document.getElementById('paymentHash').readOnly = false;
    }
}

function handleFileUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            showError('File size must be less than 5MB');
            input.value = '';
            return;
        }
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('receiptPreviewImg').src = e.target.result;
                document.getElementById('receiptPreview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('receiptPreview').classList.add('hidden');
        }
    }
}

function clearSelectedUser() {
    selectedUserData = null;
    document.getElementById('recipientId').value = '';
    document.getElementById('selectedUser').classList.add('hidden');
    document.getElementById('userSearch').value = '';
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('userSearch');
    const resultsDiv = document.getElementById('userSearchResults');
    
    if (searchInput && resultsDiv && !searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.classList.add('hidden');
    }
});

async function sendOtp() {
    const email = document.getElementById('sellEmail').value;
    const recipientId = document.getElementById('recipientId').value;
    const quantity = document.getElementById('coinQuantity').value;

    if (!email) {
        showError('Please enter your email');
        return;
    }

    if (!recipientId) {
        showError('Please search and select a user/reseller');
        return;
    }

    if (!quantity || quantity < 1) {
        showError('Please enter a valid quantity');
        return;
    }

    try {
        console.log('Sending OTP request to:', sendOtpRoute);
        console.log('Email:', email);
        
        const response = await fetch(sendOtpRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ email })
        });

        console.log('OTP Response status:', response.status);
        
        const responseText = await response.text();
        console.log('OTP Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('OTP Response data:', data);
        } catch (parseError) {
            console.error('Failed to parse OTP response:', parseError);
            showError('Invalid response from server. Please try again.');
            return;
        }

        if (data.success) {
            document.getElementById('otpSection').classList.remove('hidden');
            document.getElementById('sendOtpBtn').classList.add('hidden');
            document.getElementById('submitSellBtn').classList.remove('hidden');
            hideError();
            showInfo('OTP sent to your email! Please check your inbox.');
        } else {
            const errorMessage = data.message || data.error || 'Failed to send OTP';
            console.error('OTP send failed:', errorMessage);
            showError(errorMessage);
        }
    } catch (error) {
        console.error('OTP send error:', error);
        showError('Error sending OTP. Please try again.');
    }
}

// Calculate total function - must be in global scope for inline handlers
function calculateTotal() {
    const quantity = parseFloat(document.getElementById('coinQuantity').value) || 0;
    const totalPriceSection = document.getElementById('totalPriceSection');
    const totalPriceEl = document.getElementById('totalPrice');
    const pricePerCoinEl = document.getElementById('pricePerCoin');
    
    const currentPrice = parseFloat(document.getElementById('coinPriceInput').value) || defaultPrice;
    coinPrice = currentPrice;
    
    if (quantity <= 0) {
        totalPriceSection.classList.add('hidden');
        return;
    }
    
    const totalPrice = quantity * currentPrice;
    totalPriceEl.textContent = 'PKR ' + totalPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    pricePerCoinEl.textContent = 'PKR ' + currentPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    totalPriceSection.classList.remove('hidden');
    
    // Show payment section when quantity is entered and user is selected
    if (quantity > 0 && document.getElementById('recipientId').value) {
        document.getElementById('paymentSection').classList.remove('hidden');
    }
}

// Wait for DOM to be ready before attaching form submit handler
document.addEventListener('DOMContentLoaded', function() {
    const sellForm = document.getElementById('sellForm');
    if (!sellForm) {
        console.error('Sell form not found!');
        return;
    }
    
    // Add oninput handler for coin quantity
    const coinQuantityInput = document.getElementById('coinQuantity');
    if (coinQuantityInput) {
        coinQuantityInput.addEventListener('input', calculateTotal);
    }
    
    sellForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('=== Form Submit Started ===');
    
    const recipientId = document.getElementById('recipientId').value;
    const quantity = document.getElementById('coinQuantity').value;
    const pricePerCoin = parseFloat(document.getElementById('coinPriceInput').value) || defaultPrice;
    const rawOtp = document.getElementById('sellOtp').value;
    const email = document.getElementById('sellEmail').value;
    const paymentReceived = document.querySelector('input[name="paymentReceived"]:checked')?.value;
    const paymentType = document.getElementById('paymentType').value;
    const paymentHash = document.getElementById('paymentHash').value;
    const paymentReceipt = document.getElementById('paymentReceipt')?.value || '';

    // Clean OTP - remove all spaces
    const otp = rawOtp.replace(/\s+/g, '');

    console.log('Form Data:', {
        rawOtp: rawOtp,
        cleanedOtp: otp,
        otpLength: otp.length
    });

    if (!recipientId || !quantity || !otp || !email) {
        showError('Please fill all required fields');
        return false;
    }

    if (otp.length !== 6) {
        showError('OTP must be 6 digits');
        return false;
    }

    // Validate payment information if payment received
    if (paymentReceived === 'yes') {
        if (!paymentType) {
            showError('Please select payment type');
            return false;
        }
        
        if (paymentType === 'usdt' && !paymentHash) {
            showError('Payment proof not found. Please ensure user has an approved USDT payment.');
            return false;
        }
        
        if (paymentType === 'bank' && !paymentReceipt) {
            showError('Payment receipt not found. Please ensure user has an approved bank payment.');
            return false;
        }
        
        if (paymentType === 'cash') {
            // Cash payment doesn't need additional validation
        }
    }

    hideError();
    document.getElementById('submitSellBtn').disabled = true;
    document.getElementById('submitSellBtn').textContent = 'Processing...';

    try {
        // Prepare request body (no file upload needed anymore)
        const requestBody = {
            recipient_id: recipientId,
            coin_quantity: quantity,
            price_per_coin: pricePerCoin,
            otp: otp,
            email: email,
            payment_received: paymentReceived || 'no',
            payment_type: paymentType || '',
            payment_hash: paymentHash || '',
            payment_receipt: paymentReceipt || ''
        };

        console.log('=== Sending Request ===');
        console.log('Request URL:', sellCoinsRoute);
        console.log('Request Body:', requestBody);
        console.log('OTP being sent:', otp);
        console.log('OTP type:', typeof otp);
        console.log('OTP length:', otp.length);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (!csrfToken) {
            showError('CSRF token not found. Please refresh the page and try again.');
            document.getElementById('submitSellBtn').disabled = false;
            document.getElementById('submitSellBtn').textContent = 'Confirm Transfer';
            return false;
        }

        const response = await fetch(sellCoinsRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestBody)
        });

        console.log('Response Status:', response.status);
        // Note: Object.fromEntries may not be available in all browsers
        try {
            console.log('Response Headers:', Object.fromEntries(response.headers.entries()));
        } catch (e) {
            console.log('Response Headers: (unable to log)');
        }

        const responseText = await response.text();
        console.log('Response Text (raw):', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
            console.log('Response Data (parsed):', data);
        } catch (parseError) {
            console.error('Failed to parse JSON response:', parseError)
            showError('Invalid response from server. Please try again.');
            document.getElementById('submitSellBtn').disabled = false;
            document.getElementById('submitSellBtn').textContent = 'Confirm Transfer';
            return false;
        }

        if (data.success) {
            console.log('✅ Transaction successful!')
            document.getElementById('successMessage').classList.remove('hidden');
            document.getElementById('sellForm').reset();
            document.getElementById('otpSection').classList.add('hidden');
            document.getElementById('sendOtpBtn').classList.remove('hidden');
            document.getElementById('submitSellBtn').classList.add('hidden');
            document.getElementById('selectedUser').classList.add('hidden');
            document.getElementById('coinPriceInput').value = defaultPrice;
            setTimeout(() => {
                window.location.href = adminDashboardRoute;
            }, 2000);
        } else {
            console.error('❌ Transaction failed:', data)
            if (data.debug) {
                console.error('Debug Info:', data.debug)
                console.error('Submitted OTP:', data.debug.submitted_otp)
                console.error('Cached OTP:', data.debug.cached_otp)
                console.error('Cache exists:', data.debug.cache_exists)
                console.error('Cache key:', data.debug.cache_key)
            }
            showError(data.message || 'Transfer failed');
            document.getElementById('submitSellBtn').disabled = false;
            document.getElementById('submitSellBtn').textContent = 'Confirm Transfer';
        }
    } catch (error) {
        console.error('❌ Error processing transfer:', error)
        showError('Error processing transfer. Please try again.');
        document.getElementById('submitSellBtn').disabled = false;
        document.getElementById('submitSellBtn').textContent = 'Confirm Transfer';
    }
    
    return false;
    });
    
    // Coin price input listener
    const coinPriceInput = document.getElementById('coinPriceInput');
    if (coinPriceInput) {
        coinPriceInput.addEventListener('input', calculateTotal);
    }
});

function showError(message) {
    document.getElementById('errorMessage').classList.remove('hidden');
    document.getElementById('errorText').textContent = message;
    hideInfo();
}

function hideError() {
    document.getElementById('errorMessage').classList.add('hidden');
    document.getElementById('successMessage').classList.add('hidden');
}

function showInfo(message) {
    document.getElementById('infoMessage').classList.remove('hidden');
    document.getElementById('infoText').textContent = message;
    hideError();
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideInfo();
    }, 5000);
}

function hideInfo() {
    document.getElementById('infoMessage').classList.add('hidden');
}

</script>
@endsection


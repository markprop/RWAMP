@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Sell Coins</h1>
                    <p class="text-white/80">Transfer tokens to users/resellers (OTP protected)</p>
                </div>
                <a href="{{ route('dashboard.admin') }}" class="btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Sell Form -->
            <div class="bg-white rounded-xl shadow-xl p-6">
                <h2 class="text-2xl font-bold mb-6">Transfer Tokens</h2>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-yellow-800">
                        <strong>Note:</strong> You can sell tokens to any user or reseller. An OTP will be sent to your email for security verification.
                    </p>
                </div>

                <!-- Coin Price Calculator -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-3">Coin Price Calculator</h3>
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
                        <label class="block text-sm font-medium mb-2">Search User/Reseller <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="userSearch" 
                                class="form-input w-full pr-10" 
                                placeholder="Search by name, email, or user ID..."
                                autocomplete="off"
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div id="userSearchResults" class="hidden mt-2 border border-gray-200 rounded-lg bg-white shadow-lg max-h-60 overflow-y-auto z-10"></div>
                        <input type="hidden" id="recipientId" required>
                        <div id="selectedUser" class="hidden mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-green-900" id="selectedUserName"></p>
                                    <p class="text-sm text-green-700" id="selectedUserEmail"></p>
                                    <p class="text-xs text-green-600 mt-1">Balance: <span id="selectedUserBalance"></span> RWAMP | Role: <span id="selectedUserRole"></span></p>
                                </div>
                                <button type="button" onclick="clearSelectedUser()" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Search for any user or reseller by name, email, or user ID</p>
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
let searchTimeout;
let selectedUserData = null;
let coinPrice = {{ $defaultPrice }};
let defaultPrice = {{ $defaultPrice }};

// User search functionality
document.getElementById('userSearch').addEventListener('input', function(e) {
    const query = e.target.value.trim();
    const resultsDiv = document.getElementById('userSearchResults');
    
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(async () => {
        try {
            const url = `{{ route('admin.search-users') }}${query ? '?q=' + encodeURIComponent(query) : ''}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
,
            });
            
            if (!response.ok) {
                throw new Error('Search failed');
            }
            
            const users = await response.json();
            displaySearchResults(users, query);
        } catch (error) {
            console.error('Search error:', error)
            resultsDiv.innerHTML = '<div class="p-3 text-sm text-red-500">Error searching users. Please try again.</div>';
            resultsDiv.classList.remove('hidden');
        }
    }, 300);
});

document.getElementById('userSearch').addEventListener('focus', function(e) {
    const query = e.target.value.trim();
    if (query.length >= 1 || query.length === 0) {
        e.target.dispatchEvent(new Event('input'));
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function displaySearchResults(users, query = '') {
    const resultsDiv = document.getElementById('userSearchResults');
    
    if (users.length === 0) {
        const message = query 
            ? `No users found matching "${escapeHtml(query)}". Try a different search.`
            : 'No users found.';
        resultsDiv.innerHTML = `<div class="p-3 text-sm text-gray-500">${message}</div>`;
        resultsDiv.classList.remove('hidden');
        return;
    }
    
    resultsDiv.innerHTML = '';
    
    if (!query || query.length === 0) {
        const headerDiv = document.createElement('div');
        headerDiv.className = 'p-2 bg-gray-100 border-b border-gray-200';
        headerDiv.innerHTML = '<p class="text-xs font-semibold text-gray-600">All Users/Resellers (Click to select)</p>';
        resultsDiv.appendChild(headerDiv);
    }
    
    users.forEach(user => {
        const div = document.createElement('div');
        div.className = 'p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
        div.onclick = () => selectUser(user.id, user.name, user.email, user.token_balance, user.role);
        
        const roleColors = {
            'investor': 'bg-blue-100 text-blue-800',
            'reseller': 'bg-green-100 text-green-800',
            'user': 'bg-gray-100 text-gray-800'
        };
        const roleColor = roleColors[user.role] || 'bg-gray-100 text-gray-800';
        const roleLabel = (user.role || 'investor').charAt(0).toUpperCase() + (user.role || 'investor').slice(1);
        
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">${escapeHtml(user.name)}</p>
                    <p class="text-sm text-gray-600">${escapeHtml(user.email)}</p>
                    <p class="text-xs text-gray-500 mt-1">ID: ${user.id} | Balance: ${(user.token_balance || 0).toLocaleString()} RWAMP</p>
                </div>
                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded ${roleColor}">${escapeHtml(roleLabel)}</span>
            </div>
        `;
        
        resultsDiv.appendChild(div);
    });
    
    const footerDiv = document.createElement('div');
    footerDiv.className = 'p-2 bg-gray-50 border-t border-gray-200';
    footerDiv.innerHTML = `<p class="text-xs text-gray-500 text-center">Showing ${users.length} user${users.length !== 1 ? 's' : ''}</p>`;
    resultsDiv.appendChild(footerDiv);
    
    resultsDiv.classList.remove('hidden');
}

function selectUser(userId, userName, userEmail, userBalance, userRole) {
    selectedUserData = { id: userId, name: userName, email: userEmail, balance: userBalance, role: userRole };
    
    document.getElementById('recipientId').value = userId;
    document.getElementById('selectedUserName').textContent = userName;
    document.getElementById('selectedUserEmail').textContent = userEmail;
    document.getElementById('selectedUserBalance').textContent = (userBalance || 0).toLocaleString();
    document.getElementById('selectedUserRole').textContent = (userRole || 'investor').charAt(0).toUpperCase() + (userRole || 'investor').slice(1);
    
    document.getElementById('userSearch').value = '';
    document.getElementById('userSearchResults').classList.add('hidden');
    document.getElementById('selectedUser').classList.remove('hidden');
    
    // Show payment section if quantity is already entered
    const quantity = parseFloat(document.getElementById('coinQuantity').value) || 0;
    if (quantity > 0) {
        document.getElementById('paymentSection').classList.remove('hidden');
    }
    
    // If payment type is already selected, fetch proof
    const paymentType = document.getElementById('paymentType').value;
    if (paymentType && (paymentType === 'usdt' || paymentType === 'bank')) {
        fetchPaymentProof(userId, paymentType);
    }
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
,
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

document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('userSearch');
    const resultsDiv = document.getElementById('userSearchResults');
    
    if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
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
        const response = await fetch('{{ route("admin.send-otp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email })
        });

        const data = await response.json();
        if (data.success) {
            document.getElementById('otpSection').classList.remove('hidden');
            document.getElementById('sendOtpBtn').classList.add('hidden');
            document.getElementById('submitSellBtn').classList.remove('hidden');
            hideError();
            showInfo('OTP sent to your email! Please check your inbox.');
        } else {
            showError(data.message || 'Failed to send OTP');
        }
    } catch (error) {
        showError('Error sending OTP. Please try again.');
    }
}

document.getElementById('sellForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('=== Form Submit Started ===')
    
    const recipientId = document.getElementById('recipientId').value;
    const quantity = document.getElementById('coinQuantity').value;
    const pricePerCoin = parseFloat(document.getElementById('coinPriceInput').value) || defaultPrice;
    const rawOtp = document.getElementById('sellOtp').value;
    const email = document.getElementById('sellEmail').value;
    const paymentReceived = document.querySelector('input[name="paymentReceived"]:checked')?.value;
    const paymentType = document.getElementById('paymentType').value;
    const paymentHash = document.getElementById('paymentHash').value;
    const paymentReceipt = document.getElementById('paymentReceipt').files[0];

    // Clean OTP - remove all spaces
    const otp = rawOtp.replace(/\s+/g, '');

    console.log('Form Data:', {
        // recipientId,
        // quantity,
        // pricePerCoin,
        rawOtp: rawOtp,
        cleanedOtp: otp,
        otpLength: otp.length,
        // email,
        // paymentReceived,
        // paymentType,
        // paymentHash
    })

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
,
        };

        console.log('=== Sending Request ===')
        console.log('Request URL:', '{{ route("admin.sell-coins") }}');
        console.log('Request Body:', requestBody)
        console.log('OTP being sent:', otp)
        console.log('OTP type:', typeof otp)
        console.log('OTP length:', otp.length)

        // const response = await fetch('{{ route("admin.sell-coins") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestBody)
,
        });

        console.log('Response Status:', response.status)
        console.log('Response Headers:', Object.fromEntries(response.headers.entries()));

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
                window.location.href = '{{ route("dashboard.admin") }}';
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

document.getElementById('coinPriceInput').addEventListener('input', function() {
    calculateTotal();
});
</script>
@endsection


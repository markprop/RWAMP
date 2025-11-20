@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Sell Coins</h1>
                    <p class="text-white/80">Transfer tokens to your users (OTP protected)</p>
                </div>
                <a href="{{ route('dashboard.reseller') }}" class="btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Share Link Section - At Top -->
        <div class="mb-8">
            <div class="bg-gradient-to-br from-primary to-red-600 rounded-xl shadow-xl p-4 text-white">
                <div class="flex gap-2 items-center flex-wrap">
                    <span class="text-sm font-semibold whitespace-nowrap">Share Your Referral Link:</span>
                    <input 
                        type="text" 
                        id="shareLinkInput" 
                        value="{{ $shareLink }}" 
                        readonly 
                        class="flex-1 min-w-[200px] bg-white/20 border border-white/30 rounded-lg px-3 py-2 text-sm text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50"
                    >
                    <button 
                        onclick="copyShareLink()" 
                        class="bg-white text-primary hover:bg-gray-100 px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-200 shadow-md hover:shadow-lg whitespace-nowrap"
                    >
                        Copy
                    </button>
                </div>
                <div id="copySuccess" class="hidden mt-2 p-2 bg-green-500/20 border border-green-300/50 rounded text-xs">
                    <p class="text-white">✓ Link copied!</p>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Sell Form -->
            <div class="bg-white rounded-xl shadow-xl p-6">
                <h2 class="text-2xl font-bold mb-6">Transfer Tokens</h2>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-yellow-800">
                        <strong>Note:</strong> You can sell tokens to any user. An OTP will be sent to your email for security verification.
                    </p>
                </div>

                <!-- Coin Price Calculator -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-3">Coin Price Calculator</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-blue-800 mb-1">Your Coin Price (PKR per coin)</label>
                            <div class="flex gap-2">
                                <input 
                                    type="number" 
                                    id="coinPriceInput" 
                                    value="{{ auth()->user()->coin_price ?? \App\Helpers\PriceHelper::getRwampPkrPrice() }}" 
                                    step="0.01" 
                                    min="0.01"
                                    class="flex-1 form-input text-sm"
                                    placeholder="Enter price"
                                >
                                <button 
                                    type="button"
                                    onclick="updateCoinPrice()" 
                                    class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors whitespace-nowrap"
                                >
                                    Update Price
                                </button>
                            </div>
                            <p class="text-xs text-blue-600 mt-1">
                                Default: PKR {{ number_format(\App\Helpers\PriceHelper::getRwampPkrPrice(), 2) }} (Super-Admin Price)
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
                            <!-- Linked Receipt from Chat -->
                            <div id="chatReceiptSection" class="hidden mt-3 pt-3 border-t border-green-300">
                                <p class="text-xs font-semibold text-green-800 mb-2">📄 Receipt from Chat:</p>
                                <div id="chatReceiptDisplay" class="bg-white rounded p-2">
                                    <img id="chatReceiptImage" src="" alt="Chat Receipt" class="max-w-full rounded mb-2 hidden">
                                    <a id="chatReceiptLink" href="" target="_blank" class="text-xs text-blue-600 hover:underline hidden">View Full Receipt</a>
                                    <label class="flex items-center mt-2">
                                        <input type="checkbox" id="useChatReceipt" class="mr-2" onchange="toggleChatReceipt()">
                                        <span class="text-xs text-green-700">Use receipt from chat</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="sellUserId" required>
                        <p class="text-xs text-gray-500 mt-1">Enter the 16-digit wallet address of the recipient</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Coin Quantity (RWAMP) <span class="text-red-500">*</span></label>
                        <input 
                            type="number" 
                            id="sellAmount" 
                            class="form-input" 
                            min="1" 
                            step="1" 
                            required
                            placeholder="Enter quantity"
                            oninput="checkBalanceAndCalculate()"
                        >
                        <div class="mt-2 space-y-1">
                            <p class="text-xs text-gray-500">
                                Your current balance: <strong id="currentBalance" class="text-gray-700">{{ number_format(auth()->user()->token_balance, 0) }} RWAMP</strong>
                            </p>
                            <p id="remainingBalanceDisplay" class="hidden text-xs font-semibold">
                                Remaining balance after transfer: <strong id="remainingBalance" class="text-green-600">0 RWAMP</strong>
                            </p>
                        </div>
                        <div id="balanceError" class="hidden mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                            <p>❌ Not enough balance in your account. Please enter a lower quantity.</p>
                        </div>
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
                            <p class="text-sm text-blue-800 mb-4">Have you received payment from the user?</p>
                            
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
                                
                                <!-- Selected Payment Method Display -->
                                <div id="selectedPaymentMethod" class="hidden mt-2 p-2 bg-blue-50 border border-blue-200 rounded">
                                    <p class="text-sm text-blue-800">
                                        <span class="font-semibold">Selected:</span> 
                                        <span id="selectedPaymentTypeText" class="capitalize"></span>
                                    </p>
                                </div>

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
                            <h3 class="font-semibold mb-1">Enter Wallet Address</h3>
                            <p class="text-sm text-gray-600">Enter the 16-digit wallet address of the recipient. The system will automatically look up the user.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                        <div>
                            <h3 class="font-semibold mb-1">Enter Amount</h3>
                            <p class="text-sm text-gray-600">Enter the number of RWAMP tokens you want to transfer. Make sure you have sufficient balance.</p>
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
                    <h3 class="font-semibold text-blue-900 mb-2">Security Features</h3>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>OTP verification required for all transfers</li>
                        <li>You can transfer to any user</li>
                        <li>All transactions are logged and tracked</li>
                        <li>Insufficient balance protection</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let lookupTimeout;
let selectedUserData = null;
let currentBalance = {{ auth()->user()->token_balance ?? 0 }};
let coinPrice = {{ auth()->user()->coin_price ?? \App\Helpers\PriceHelper::getRwampPkrPrice() }};
let defaultPrice = {{ \App\Helpers\PriceHelper::getRwampPkrPrice() }};

// Wallet lookup functionality
document.getElementById('walletAddressInput').addEventListener('blur', function(e) {
    const wallet = e.target.value.trim();
    
    // Only lookup if wallet is exactly 16 digits
    if (wallet.length === 16 && /^\d{16}$/.test(wallet)) {
        lookupWalletAddress(wallet);
    } else if (wallet.length > 0) {
        showWalletError('Wallet address must be exactly 16 digits');
    }
});

// Also lookup on Enter key
document.getElementById('walletAddressInput').addEventListener('keypress', function(e) {
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
            selectedUserData = { id: data.id, name: data.name, receipt_screenshot: data.receipt_screenshot };
            document.getElementById('sellUserId').value = data.id;
            document.getElementById('selectedUserName').textContent = data.name;
            document.getElementById('selectedUser').classList.remove('hidden');
            
            // Check for linked receipt from chat
            if (data.receipt_screenshot) {
                const receiptSection = document.getElementById('chatReceiptSection');
                const receiptImage = document.getElementById('chatReceiptImage');
                const receiptLink = document.getElementById('chatReceiptLink');
                
                if (receiptSection && receiptImage && receiptLink) {
                    receiptImage.src = '/storage/' + data.receipt_screenshot;
                    receiptImage.classList.remove('hidden');
                    receiptLink.href = '/storage/' + data.receipt_screenshot;
                    receiptLink.classList.remove('hidden');
                    receiptSection.classList.remove('hidden');
                }
            } else {
                const receiptSection = document.getElementById('chatReceiptSection');
                if (receiptSection) {
                    receiptSection.classList.add('hidden');
                }
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
    document.getElementById('sellUserId').value = '';
    selectedUserData = null;
}

function hideWalletError() {
    document.getElementById('walletLookupError').classList.add('hidden');
}

function clearSelectedUser() {
    selectedUserData = null;
    document.getElementById('sellUserId').value = '';
    document.getElementById('selectedUser').classList.add('hidden');
    document.getElementById('walletAddressInput').value = '';
    document.getElementById('chatReceiptSection').classList.add('hidden');
    document.getElementById('useChatReceipt').checked = false;
    hideWalletError();
}

function toggleChatReceipt() {
    const useReceipt = document.getElementById('useChatReceipt').checked;
    if (useReceipt && selectedUserData?.receipt_screenshot) {
        // Auto-fill payment receipt if using chat receipt
        document.getElementById('paymentReceipt').value = selectedUserData.receipt_screenshot;
    } else {
        document.getElementById('paymentReceipt').value = '';
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
        // Reset payment fields
        document.getElementById('paymentType').value = '';
        document.getElementById('selectedPaymentMethod').classList.add('hidden');
        handlePaymentTypeChange();
    }
}

async function handlePaymentTypeChange() {
    const paymentType = document.getElementById('paymentType').value;
    const selectedMethodDiv = document.getElementById('selectedPaymentMethod');
    const selectedMethodText = document.getElementById('selectedPaymentTypeText');
    const userId = document.getElementById('sellUserId').value;
    
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
    
    // Show selected payment method
    if (paymentType) {
        const paymentTypeLabels = {
            'cash': 'Cash Payment',
            'usdt': 'USDT (Wallet Connect)',
            'bank': 'Bank Transfer'
        };
        selectedMethodText.textContent = paymentTypeLabels[paymentType] || paymentType;
        selectedMethodDiv.classList.remove('hidden');
    } else {
        selectedMethodDiv.classList.add('hidden');
        return;
    }
    
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
        showError('Please select a user first');
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

        const response = await fetch('/api/reseller/fetch-payment-proof', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
        
        // Show preview for images
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

// Removed - no longer needed with wallet lookup

// Copy share link functionality
function copyShareLink() {
    const linkInput = document.getElementById('shareLinkInput');
    const copySuccess = document.getElementById('copySuccess');
    
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        copySuccess.classList.remove('hidden');
        setTimeout(() => {
            copySuccess.classList.add('hidden');
        }, 3000);
    } catch (err) {
        // Fallback for modern browsers
        navigator.clipboard.writeText(linkInput.value).then(() => {
            copySuccess.classList.remove('hidden');
            setTimeout(() => {
                copySuccess.classList.add('hidden');
            }, 3000);
        }).catch(() => {
            alert('Failed to copy link. Please copy manually.');
        });
    }
}

async function sendOtp() {
    const email = document.getElementById('sellEmail').value;
    const userId = document.getElementById('sellUserId').value;
    const amount = document.getElementById('sellAmount').value;

    if (!email) {
        showError('Please enter your email');
        return;
    }

    if (!userId) {
        showError('Please enter a wallet address and select a user');
        return;
    }

    if (!amount || amount < 1) {
        showError('Please enter a valid quantity');
        return;
    }

    // Check balance before sending OTP
    if (parseFloat(amount) > currentBalance) {
        showError('Not enough balance in your account. Please enter a lower quantity.');
        return;
    }

    try {
        const response = await fetch('/api/reseller/send-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
    
    try {
        console.log('Step 1: Getting form elements...');
        const userIdEl = document.getElementById('sellUserId');
        console.log('userIdEl:', userIdEl);
        const userId = userIdEl?.value;
        console.log('userId:', userId);
        
        const amountEl = document.getElementById('sellAmount');
        console.log('amountEl:', amountEl);
        const amount = amountEl?.value;
        console.log('amount:', amount);
        
        const rawOtpEl = document.getElementById('sellOtp');
        console.log('rawOtpEl:', rawOtpEl);
        const rawOtp = rawOtpEl?.value || '';
        console.log('rawOtp:', rawOtp);
        
        const emailEl = document.getElementById('sellEmail');
        console.log('emailEl:', emailEl);
        const email = emailEl?.value;
        console.log('email:', email);
        
        console.log('Step 2: Getting payment elements...');
        const paymentReceivedEl = document.querySelector('input[name="paymentReceived"]:checked');
        console.log('paymentReceivedEl:', paymentReceivedEl);
        const paymentReceived = paymentReceivedEl?.value;
        console.log('paymentReceived:', paymentReceived);
        
        const paymentTypeEl = document.getElementById('paymentType');
        console.log('paymentTypeEl:', paymentTypeEl);
        const paymentType = paymentTypeEl?.value;
        console.log('paymentType:', paymentType);
        
        const paymentHashEl = document.getElementById('paymentHash');
        console.log('paymentHashEl:', paymentHashEl);
        const paymentHash = paymentHashEl?.value;
        console.log('paymentHash:', paymentHash);
        
        console.log('Step 3: Getting payment receipt...');
        const paymentReceiptEl = document.getElementById('paymentReceipt');
        console.log('paymentReceiptEl:', paymentReceiptEl);
        console.log('paymentReceiptEl?.files:', paymentReceiptEl?.files);
        console.log('paymentReceiptEl?.files?.length:', paymentReceiptEl?.files?.length);
        const paymentReceipt = (paymentReceiptEl && paymentReceiptEl.files && paymentReceiptEl.files.length > 0) ? paymentReceiptEl.files[0] : null;
        console.log('paymentReceipt:', paymentReceipt);

        // Clean OTP - remove all spaces
        console.log('Step 4: Cleaning OTP...');
        const otp = rawOtp ? rawOtp.replace(/\s+/g, '') : '';
        console.log('otp (cleaned):', otp);

        console.log('Step 5: Form Data Summary:', {
            userId: userId,
            amount: amount,
            rawOtp: rawOtp,
            cleanedOtp: otp,
            otpLength: otp ? otp.length : 0,
            email: email,
            paymentReceived: paymentReceived,
            paymentType: paymentType,
            paymentHash: paymentHash
        });

        console.log('Step 6: Validating required fields...');
        if (!userId || !amount || !otp || !email) {
            console.error('Validation failed - missing required fields:', {
                hasUserId: !!userId,
                hasAmount: !!amount,
                hasOtp: !!otp,
                hasEmail: !!email
            });
            showError('Please fill all required fields');
            return false;
        }

        console.log('Step 7: Validating OTP length...');
        if (otp.length !== 6) {
            console.error('OTP length validation failed. OTP length:', otp.length);
            showError('OTP must be 6 digits');
            return false;
        }
        console.log('✅ OTP length validation passed');

        console.log('Step 8: Validating payment information...');
        // Validate payment information if payment received
        if (paymentReceived === 'yes') {
            console.log('Payment received is yes, validating payment details...');
            if (!paymentType) {
                console.error('Payment type is missing');
                showError('Please select payment type');
                return false;
            }
            
            if (paymentType === 'usdt' && !paymentHash) {
                console.error('USDT payment hash is missing');
                showError('Payment proof not found. Please ensure user has an approved USDT payment.');
                return false;
            }
            
            if (paymentType === 'bank' && !paymentReceipt) {
                console.error('Bank payment receipt is missing');
                showError('Payment receipt not found. Please ensure user has an approved bank payment.');
                return false;
            }
            console.log('✅ Payment validation passed');
        }

        console.log('Step 9: Hiding errors and disabling submit button...');
        hideError();
        const submitBtn = document.getElementById('submitSellBtn');
        console.log('submitBtn:', submitBtn);
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            console.log('✅ Submit button disabled');
        } else {
            console.error('❌ Submit button not found!');
        }

        console.log('Step 10: Preparing request...');
        try {
            const coinPriceInputEl = document.getElementById('coinPriceInput');
            console.log('coinPriceInputEl:', coinPriceInputEl);
            const pricePerCoin = parseFloat(coinPriceInputEl?.value) || coinPrice;
            console.log('pricePerCoin:', pricePerCoin);
        
            console.log('Step 11: Building request body...');
            // Prepare request body (no file upload needed anymore)
            const requestBody = {
                user_id: userId,
                amount: amount,
                price_per_coin: pricePerCoin,
                otp: otp,
                email: email,
                payment_received: paymentReceived || 'no',
                payment_type: paymentType || '',
                payment_hash: paymentHash || '',
                payment_receipt: paymentReceipt || ''
            };

            console.log('=== Sending Request ===')
            console.log('Request URL:', '/api/reseller/sell')
            console.log('Request Body:', requestBody)
            console.log('OTP being sent:', otp)
            console.log('OTP type:', typeof otp)
            console.log('OTP length:', otp.length)

            console.log('Step 12: Getting CSRF token...');
            const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
            console.log('csrfTokenEl:', csrfTokenEl);
            const csrfToken = csrfTokenEl?.getAttribute('content') || '';
            console.log('csrfToken:', csrfToken ? 'Found' : 'Missing');

            console.log('Step 13: Sending fetch request...');
            const response = await fetch('/api/reseller/sell', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestBody)
            });

            console.log('Step 14: Response received');
            console.log('Response Status:', response.status)
            try {
                console.log('Response Headers:', Object.fromEntries(response.headers.entries()));
            } catch (e) {
                console.log('Response Headers: (unable to parse)', e);
            }

            console.log('Step 15: Reading response text...');
            const responseText = await response.text();
            console.log('Response Text (raw):', responseText);

            console.log('Step 16: Parsing JSON response...');
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('Response Data (parsed):', data);
            } catch (parseError) {
                console.error('❌ Failed to parse JSON response:', parseError)
                console.error('Response text that failed to parse:', responseText);
                showError('Invalid response from server. Please try again.');
                const submitBtn = document.getElementById('submitSellBtn');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Confirm Transfer';
                }
                return false;
            }

            console.log('Step 17: Processing response...');
            if (data.success) {
                console.log('✅ Transaction successful!')
                console.log('Step 18: Updating UI for success...');
                const successMessage = document.getElementById('successMessage');
                if (successMessage) successMessage.classList.remove('hidden');
                const sellForm = document.getElementById('sellForm');
                if (sellForm) sellForm.reset();
                const otpSection = document.getElementById('otpSection');
                if (otpSection) otpSection.classList.add('hidden');
                const sendOtpBtn = document.getElementById('sendOtpBtn');
                if (sendOtpBtn) sendOtpBtn.classList.remove('hidden');
                const submitSellBtn = document.getElementById('submitSellBtn');
                if (submitSellBtn) submitSellBtn.classList.add('hidden');
                setTimeout(() => {
                    window.location.href = '{{ route("dashboard.reseller") }}';
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
            let errorMsg = data.message || 'Transfer failed';
            if (data.debug && typeof data.debug === 'object') {
                if (data.debug.cache_exists === false) {
                    errorMsg += ' (OTP not found in cache. Please request a new OTP.)';
                }
            }
            showError(errorMsg);
            const submitBtn = document.getElementById('submitSellBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm Transfer';
            }
        }
        } catch (error) {
            console.error('❌ Error in try block:', error)
            console.error('Error stack:', error.stack);
            console.error('Error message:', error.message);
            showError('Error processing transfer. Please try again.');
            const submitBtn = document.getElementById('submitSellBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm Transfer';
            }
            return false;
        }
    } catch (error) {
        console.error('❌ Error in form submission handler:', error)
        console.error('Error stack:', error.stack);
        console.error('Error message:', error.message);
        console.error('Error at step:', 'Unknown - check logs above');
        showError('Error processing transfer. Please try again.');
        const submitBtn = document.getElementById('submitSellBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm Transfer';
        }
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

// Check balance and calculate total price
function checkBalanceAndCalculate() {
    const quantity = parseFloat(document.getElementById('sellAmount')?.value) || 0;
    const balanceError = document.getElementById('balanceError');
    const totalPriceSection = document.getElementById('totalPriceSection');
    const totalPriceEl = document.getElementById('totalPrice');
    const pricePerCoinEl = document.getElementById('pricePerCoin');
    const remainingBalanceDisplay = document.getElementById('remainingBalanceDisplay');
    const remainingBalanceEl = document.getElementById('remainingBalance');
    
    // Get current coin price
    const coinPriceInput = document.getElementById('coinPriceInput');
    const currentPrice = parseFloat(coinPriceInput?.value) || defaultPrice;
    coinPrice = currentPrice;
    
    if (quantity <= 0) {
        if (balanceError) balanceError.classList.add('hidden');
        if (totalPriceSection) totalPriceSection.classList.add('hidden');
        if (remainingBalanceDisplay) remainingBalanceDisplay.classList.add('hidden');
        return;
    }
    
    // Calculate remaining balance
    const remainingBalance = currentBalance - quantity;
    
    // Check balance
    if (quantity > currentBalance) {
        if (balanceError) balanceError.classList.remove('hidden');
        if (totalPriceSection) totalPriceSection.classList.add('hidden');
        if (remainingBalanceDisplay) remainingBalanceDisplay.classList.add('hidden');
        // Disable send OTP button
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        if (sendOtpBtn) sendOtpBtn.disabled = true;
        return;
    } else {
        if (balanceError) balanceError.classList.add('hidden');
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        if (sendOtpBtn) sendOtpBtn.disabled = false;
        
        // Show remaining balance
        if (remainingBalanceDisplay) {
            remainingBalanceDisplay.classList.remove('hidden');
        }
        if (remainingBalanceEl) {
            // Format remaining balance with commas
            const formattedBalance = remainingBalance.toLocaleString('en-US', { 
                minimumFractionDigits: 0, 
                maximumFractionDigits: 0 
            });
            remainingBalanceEl.textContent = formattedBalance + ' RWAMP';
            
            // Change color based on remaining balance
            if (remainingBalance < 100) {
                remainingBalanceEl.className = 'text-orange-600';
            } else if (remainingBalance < 500) {
                remainingBalanceEl.className = 'text-yellow-600';
            } else {
                remainingBalanceEl.className = 'text-green-600';
            }
        }
    }
    
    // Calculate and show total price
    const totalPrice = quantity * currentPrice;
    if (totalPriceEl) {
        totalPriceEl.textContent = 'PKR ' + totalPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    if (pricePerCoinEl) {
        pricePerCoinEl.textContent = 'PKR ' + currentPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    if (totalPriceSection) totalPriceSection.classList.remove('hidden');
    
    // Show payment section when quantity is entered and user is selected
    const sellUserId = document.getElementById('sellUserId');
    if (quantity > 0 && sellUserId?.value) {
        const paymentSection = document.getElementById('paymentSection');
        if (paymentSection) paymentSection.classList.remove('hidden');
    }
}

// Update coin price
async function updateCoinPrice() {
    const priceInput = document.getElementById('coinPriceInput');
    const price = parseFloat(priceInput.value);
    
    if (isNaN(price) || price < 0.01) {
        alert('Please enter a valid price (minimum 0.01)');
        return;
    }
    
    try {
        const response = await fetch('{{ route("reseller.update-coin-price") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ coin_price: price })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            coinPrice = price;
            // Recalculate if quantity is already entered
            checkBalanceAndCalculate();
            alert('Coin price updated successfully!');
        } else {
            alert(data.message || 'Failed to update price');
        }
    } catch (error) {
        console.error('Error updating price:', error)
        alert('Error updating price. Please try again.');
    }
}

// Listen for coin price input changes
document.getElementById('coinPriceInput').addEventListener('input', function() {
    checkBalanceAndCalculate();
});
</script>
@endsection


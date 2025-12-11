@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.reseller-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Buy Requests</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Manage coin purchase requests from users</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6" x-data="resellerBuyRequestsFilters()">
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-xl p-5 sm:p-6 mb-6 border border-gray-100">
                <form 
                    method="GET" 
                    action="{{ route('reseller.buy-requests') }}" 
                    class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto_auto] gap-3 md:gap-4 items-stretch"
                    @submit.prevent="submit($event)"
                >
                    <div class="w-full">
                        <label for="buy-requests-status" class="sr-only">Status</label>
                        <select 
                            id="buy-requests-status"
                            name="status" 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Filter buy requests by status"
                        >
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <button 
                        type="submit" 
                        class="btn-primary w-full md:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold"
                        x-bind:disabled="isLoading"
                    >
                        <span x-show="!isLoading">Filter</span>
                        <span x-show="isLoading">Filtering…</span>
                    </button>
                    @if(request('status'))
                        <a 
                            href="{{ route('reseller.buy-requests') }}" 
                            class="btn-secondary w-full md:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold text-center"
                        >
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Buy Requests Table -->
            <div id="resellerBuyRequestsTable" class="bg-white rounded-xl shadow-xl overflow-hidden animate-fadeInUp">
            @if($requests->count() > 0)
                <div class="rw-table-scroll overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">User</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Coins</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Price per Coin</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Total Amount</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 sm:px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $buyRequest)
                                <tr class="border-b hover:bg-gray-50" data-request-id="{{ $buyRequest->id }}">
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="font-semibold" data-user-name>{{ $buyRequest->user->name }}</div>
                                        <div class="text-xs sm:text-sm text-gray-600" data-user-email>{{ $buyRequest->user->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 font-semibold"><span data-coin-quantity>{{ number_format($buyRequest->coin_quantity, 0) }} RWAMP</span></td>
                                    <td class="py-3 px-4 sm:px-6"><span data-coin-price>PKR {{ number_format($buyRequest->coin_price, 2) }}</span></td>
                                    <td class="py-3 px-4 sm:px-6 font-semibold"><span data-total-amount>PKR {{ number_format($buyRequest->total_amount, 2) }}</span></td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <span class="px-3 py-1 rounded text-sm font-semibold 
                                            {{ $buyRequest->status === 'approved' || $buyRequest->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($buyRequest->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($buyRequest->status) }}
                                        </span>
                                        @if($buyRequest->rejection_reason)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Reason: {{ $buyRequest->rejection_reason }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 text-xs sm:text-sm text-gray-600">
                                        <div>{{ $buyRequest->created_at->format('M d, Y H:i') }}</div>
                                        @if($buyRequest->approved_at)
                                            <div class="text-xs text-green-600">Approved: {{ $buyRequest->approved_at->format('M d, Y H:i') }}</div>
                                        @endif
                                        @if($buyRequest->rejected_at)
                                            <div class="text-xs text-red-600">Rejected: {{ $buyRequest->rejected_at->format('M d, Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="flex flex-wrap gap-2">
                                            @if($buyRequest->status === 'pending')
                                                <button type="button" onclick="approveBuyRequest({{ $buyRequest->id }})" class="btn-small bg-green-600 hover:bg-green-700 text-white rounded-lg px-3 py-2">
                                                    Approve
                                                </button>
                                                <button type="button" onclick="openRejectBuyRequestModal({{ $buyRequest->id }})" class="btn-small bg-red-600 hover:bg-red-700 text-white rounded-lg px-3 py-2">
                                                    Reject
                                                </button>
                                            @else
                                                <span class="text-xs text-gray-500">No actions available</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 sm:p-6 border-t">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="text-center py-10 sm:py-12 text-gray-500">
                    <p class="text-base sm:text-lg">No buy requests found.</p>
                </div>
            @endif
            </div>

            <!-- Toast -->
            <div
                x-show="toast.open"
                x-transition
                class="fixed bottom-4 right-4 z-50 max-w-sm w-full px-4"
                role="alert"
                aria-live="assertive"
            >
                <div
                    class="rounded-lg shadow-lg px-4 py-3 text-sm"
                    :class="toast.type === 'error'
                        ? 'bg-red-600 text-white'
                        : 'bg-gray-900 text-white'"
                >
                    <span x-text="toast.message"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Buy Request Confirmation Modal -->
<div id="approveBuyRequestModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Confirm Approval</h3>
            <button onclick="closeApproveBuyRequestModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="mb-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 rounded-full p-3 mr-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-900 font-medium">Approve Buy Request?</p>
                    <p class="text-sm text-gray-600 mt-1">Are you sure you want to approve this buy request?</p>
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                    <strong>Note:</strong> The tokens will be transferred to the user immediately upon approval.
                </p>
            </div>
            <div id="approveBuyRequestDetails" class="text-sm text-gray-600 space-y-1 mb-4">
                <!-- Details will be populated by JavaScript -->
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method <span class="text-red-500">*</span></label>
                <select id="paymentMethod" class="form-input w-full" required>
                    <option value="">Select payment method...</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="usdt">USDT</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Select how the user will pay for these coins.</p>
            </div>
        </div>
        <div class="flex gap-3">
            <button onclick="closeApproveBuyRequestModal()" class="btn-secondary flex-1">Cancel</button>
            <button onclick="confirmApproveBuyRequest()" id="confirmApproveBtn" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors flex-1 font-medium">
                Approve Request
            </button>
        </div>
    </div>
</div>

<!-- Reject Buy Request Modal -->
<div id="rejectBuyRequestModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Reject Buy Request</h3>
            <button onclick="closeRejectBuyRequestModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="mb-6">
            <div class="flex items-center mb-4">
                <div class="bg-red-100 rounded-full p-3 mr-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-900 font-medium">Reject Buy Request?</p>
                    <p class="text-sm text-gray-600 mt-1">Please provide a reason for rejecting this request.</p>
                </div>
            </div>
            <div id="rejectBuyRequestDetails" class="text-sm text-gray-600 space-y-1 mb-4">
                <!-- Details will be populated by JavaScript -->
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                <textarea 
                    id="rejectionReason" 
                    rows="4" 
                    class="form-input w-full" 
                    placeholder="Enter the reason for rejecting this buy request..."
                    required
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">This reason will be visible to the user.</p>
            </div>
        </div>
        <div class="flex gap-3">
            <button onclick="closeRejectBuyRequestModal()" class="btn-secondary flex-1">Cancel</button>
            <button onclick="confirmRejectBuyRequest()" id="confirmRejectBtn" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors flex-1 font-medium">
                Reject Request
            </button>
        </div>
    </div>
</div>

<!-- Success/Error Toast Notification -->
<div id="toastNotification" class="hidden fixed top-4 right-4 z-50 max-w-sm w-full">
    <div id="toastContent" class="bg-white rounded-lg shadow-lg border p-4 flex items-center justify-between">
        <div class="flex items-center">
            <div id="toastIcon" class="mr-3"></div>
            <div>
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
        </div>
        <button onclick="hideToast()" class="ml-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<script>
let currentBuyRequestId = null;

document.addEventListener('alpine:init', () => {
    Alpine.data('resellerBuyRequestsFilters', () => ({
        isLoading: false,
        toast: { open: false, message: '', type: 'info' },

        showToast(message, type = 'info') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.open = true;
            setTimeout(() => { this.toast.open = false }, 3000);
        },

        async submit(event) {
            this.isLoading = true;
            try {
                const form = event.target;
                const params = new URLSearchParams(new FormData(form)).toString();
                const url = form.action + (params ? ('?' + params) : '');

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Server error: ' + response.status);

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#resellerBuyRequestsTable');
                const current = document.querySelector('#resellerBuyRequestsTable');

                if (incoming && current) {
                    current.innerHTML = incoming.innerHTML;
                    current.classList.remove('animate-fadeInUp');
                    void current.offsetWidth;
                    current.classList.add('animate-fadeInUp');
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', url);
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load buy requests. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        }
    }));
});

function approveBuyRequest(requestId) {
    // Find the request data from the DOM
    const requestElement = document.querySelector(`[data-request-id="${requestId}"]`);
    if (!requestElement) {
        showToast('Request not found', false);
        return;
    }

    currentBuyRequestId = requestId;
    
    // Extract request details
    const userName = requestElement.querySelector('[data-user-name]')?.textContent || 'User';
    const userEmail = requestElement.querySelector('[data-user-email]')?.textContent || '';
    const coinQuantity = requestElement.querySelector('[data-coin-quantity]')?.textContent || '';
    const coinPrice = requestElement.querySelector('[data-coin-price]')?.textContent || '';
    const totalAmount = requestElement.querySelector('[data-total-amount]')?.textContent || '';

    // Populate modal with details
    const detailsHtml = `
        <div class="bg-gray-50 rounded-lg p-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-600">User:</span>
                <span class="font-medium text-gray-900">${userName}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Email:</span>
                <span class="font-medium text-gray-900">${userEmail}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Coins:</span>
                <span class="font-medium text-gray-900">${coinQuantity}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Price per Coin:</span>
                <span class="font-medium text-gray-900">${coinPrice}</span>
            </div>
            <div class="flex justify-between border-t pt-2">
                <span class="text-gray-600 font-semibold">Total Amount:</span>
                <span class="font-bold text-gray-900">${totalAmount}</span>
            </div>
        </div>
    `;
    
    document.getElementById('approveBuyRequestDetails').innerHTML = detailsHtml;
    document.getElementById('approveBuyRequestModal').style.display = 'flex';
}

function closeApproveBuyRequestModal() {
    document.getElementById('approveBuyRequestModal').style.display = 'none';
    document.getElementById('paymentMethod').value = '';
    currentBuyRequestId = null;
}

async function confirmApproveBuyRequest() {
    if (!currentBuyRequestId) return;

    const paymentMethod = document.getElementById('paymentMethod').value;
    if (!paymentMethod) {
        showToast('Please select a payment method', false);
        return;
    }

    const confirmBtn = document.getElementById('confirmApproveBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    try {
        const response = await fetch(`{{ route('reseller.buy-requests.approve', ['buyRequest' => ':id']) }}`.replace(':id', currentBuyRequestId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ payment_method: paymentMethod })
        });

        const data = await response.json();
        if (data.success) {
            closeApproveBuyRequestModal();
            showToast('Buy request approved successfully!', true);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to approve buy request', false);
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    } catch (error) {
        showToast('Error approving buy request', false);
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    }
}

function openRejectBuyRequestModal(requestId) {
    // Find the request data from the DOM
    const requestElement = document.querySelector(`[data-request-id="${requestId}"]`);
    if (!requestElement) {
        showToast('Request not found', false);
        return;
    }

    currentBuyRequestId = requestId;
    
    // Extract request details
    const userName = requestElement.querySelector('[data-user-name]')?.textContent || 'User';
    const userEmail = requestElement.querySelector('[data-user-email]')?.textContent || '';
    const coinQuantity = requestElement.querySelector('[data-coin-quantity]')?.textContent || '';
    const coinPrice = requestElement.querySelector('[data-coin-price]')?.textContent || '';
    const totalAmount = requestElement.querySelector('[data-total-amount]')?.textContent || '';

    // Populate modal with details
    const detailsHtml = `
        <div class="bg-gray-50 rounded-lg p-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-600">User:</span>
                <span class="font-medium text-gray-900">${userName}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Email:</span>
                <span class="font-medium text-gray-900">${userEmail}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Coins:</span>
                <span class="font-medium text-gray-900">${coinQuantity}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Price per Coin:</span>
                <span class="font-medium text-gray-900">${coinPrice}</span>
            </div>
            <div class="flex justify-between border-t pt-2">
                <span class="text-gray-600 font-semibold">Total Amount:</span>
                <span class="font-bold text-gray-900">${totalAmount}</span>
            </div>
        </div>
    `;
    
    document.getElementById('rejectBuyRequestDetails').innerHTML = detailsHtml;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectBuyRequestModal').style.display = 'flex';
}

function closeRejectBuyRequestModal() {
    document.getElementById('rejectBuyRequestModal').style.display = 'none';
    document.getElementById('rejectionReason').value = '';
    currentBuyRequestId = null;
}

async function confirmRejectBuyRequest() {
    if (!currentBuyRequestId) return;

    const reason = document.getElementById('rejectionReason').value.trim();
    if (!reason) {
        showToast('Please provide a reason for rejection', false);
        return;
    }

    const confirmBtn = document.getElementById('confirmRejectBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    try {
        const response = await fetch(`{{ route('reseller.buy-requests.reject', ['buyRequest' => ':id']) }}`.replace(':id', currentBuyRequestId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rejection_reason: reason })
        });

        const data = await response.json();
        if (data.success) {
            closeRejectBuyRequestModal();
            showToast('Buy request rejected successfully!', true);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to reject buy request', false);
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    } catch (error) {
        showToast('Error rejecting buy request', false);
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    }
}

function showToast(message, isSuccess = true) {
    const toast = document.getElementById('toastNotification');
    const toastContent = document.getElementById('toastContent');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');

    toastMessage.textContent = message;

    if (isSuccess) {
        toastIcon.innerHTML = `
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `;
        toastContent.classList.remove('border-red-200');
        toastContent.classList.add('border-green-200');
    } else {
        toastIcon.innerHTML = `
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `;
        toastContent.classList.remove('border-green-200');
        toastContent.classList.add('border-red-200');
    }

    toast.classList.remove('hidden');
    toast.classList.add('fadeIn');

    // Auto hide after 5 seconds
    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    const toast = document.getElementById('toastNotification');
    toast.classList.add('hidden');
    toast.classList.remove('fadeIn');
}

// Close modals when clicking outside
document.getElementById('approveBuyRequestModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveBuyRequestModal();
    }
});

document.getElementById('rejectBuyRequestModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectBuyRequestModal();
    }
});
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
    .fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
</style>
@endsection


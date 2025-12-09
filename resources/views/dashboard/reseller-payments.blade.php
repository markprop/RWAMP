@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.reseller-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Payments</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Manage crypto payments from your users</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6" x-data="resellerPaymentsFilters()">
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-xl p-5 sm:p-6 mb-6 border border-gray-100">
                <form 
                    method="GET" 
                    action="{{ route('reseller.payments') }}" 
                    class="grid grid-cols-1 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto_auto] gap-3 md:gap-4 items-stretch"
                    @submit.prevent="submit($event)"
                >
                    <div class="w-full">
                        <label for="payments-search" class="sr-only">Search payments</label>
                        <input 
                            id="payments-search"
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search by TX hash, user name, or email..." 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Search payments"
                        />
                    </div>
                    <div class="w-full">
                        <label for="payments-status" class="sr-only">Status</label>
                        <select 
                            id="payments-status"
                            name="status" 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Filter by payment status"
                        >
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                    @if(request('search') || request('status'))
                        <a 
                            href="{{ route('reseller.payments') }}" 
                            class="btn-secondary w-full md:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold text-center"
                        >
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Payments Table -->
            <div id="resellerPaymentsTable" class="bg-white rounded-xl shadow-xl overflow-hidden animate-fadeInUp">
            @if($payments->count() > 0)
                <div class="rw-table-scroll overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">User</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Network</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">TX Hash</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 sm:px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="font-semibold">{{ $payment->user->name }}</div>
                                        <div class="text-xs sm:text-sm text-gray-600">{{ $payment->user->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 font-semibold">{{ number_format($payment->token_amount, 0) }} RWAMP</td>
                                    <td class="py-3 px-4 sm:px-6">{{ strtoupper($payment->network) }}</td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <code class="text-xs break-all">{{ $payment->tx_hash }}</code>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <span class="px-3 py-1 rounded text-sm font-semibold 
                                            {{ $payment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                               ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 text-xs sm:text-sm text-gray-600">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('reseller.payments.view', $payment) }}" class="btn-primary btn-small">
                                                View
                                            </a>
                                            @if($payment->status === 'pending')
                                                <button type="button" onclick="approvePayment({{ $payment->id }})" class="btn-small bg-green-600 hover:bg-green-700 text-white rounded-lg px-3 py-2">
                                                    Approve
                                                </button>
                                                <button type="button" onclick="openRejectModal({{ $payment->id }})" class="btn-small bg-red-600 hover:bg-red-700 text-white rounded-lg px-3 py-2">
                                                    Reject
                                                </button>
                                            @endif
                                            <button type="button" onclick="sharePaymentDetails({{ $payment->id }})" class="btn-secondary btn-small">
                                                Share
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 sm:p-6 border-t">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-10 sm:py-12 text-gray-500">
                    <p class="text-base sm:text-lg">No payments found.</p>
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

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Reject Payment</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Reason (Optional)</label>
                <textarea name="notes" rows="3" class="form-input" placeholder="Enter rejection reason..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="btn-secondary flex-1">Cancel</button>
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 flex-1">Reject Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('resellerPaymentsFilters', () => ({
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
                const incoming = doc.querySelector('#resellerPaymentsTable');
                const current = document.querySelector('#resellerPaymentsTable');

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
                this.showToast('Failed to load payments. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        }
    }));
});

async function approvePayment(paymentId) {
    if (!confirm('Are you sure you want to approve this payment?')) return;

    try {
        const response = await fetch(`/api/reseller/crypto-payments/${paymentId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        if (data.success) {
            alert('Payment approved successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to approve payment');
        }
    } catch (error) {
        alert('Error approving payment');
    }
}

function openRejectModal(paymentId) {
    document.getElementById('rejectForm').action = `/dashboard/reseller/payments/${paymentId}/reject`;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('rejectForm').reset();
}

async function sharePaymentDetails(paymentId) {
    try {
        const response = await fetch(`/dashboard/reseller/payments/${paymentId}`);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const details = doc.querySelector('[data-payment-details]')?.textContent || 'Payment details not available';
        
        await navigator.clipboard.writeText(details);
        alert('Payment details copied to clipboard!');
    } catch (error) {
        console.error('Error sharing:', error)
        alert('Failed to copy details. Please try again.');
    }
}
</script>
@endsection


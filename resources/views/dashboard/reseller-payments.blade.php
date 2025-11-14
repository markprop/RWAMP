@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Payments</h1>
                    <p class="text-white/80">Manage crypto payments from your users</p>
                </div>
                <a href="{{ route('dashboard.reseller') }}" class="btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <form method="GET" action="{{ route('reseller.payments') }}" class="flex gap-4 flex-wrap">
                <div class="flex-1 min-w-[250px]">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search by TX hash, user name, or email..." 
                        class="form-input w-full"
                    />
                </div>
                <select name="status" class="form-input">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="btn-primary">Filter</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('reseller.payments') }}" class="btn-secondary">Clear</a>
                @endif
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            @if($payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">User</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Network</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">TX Hash</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-4 px-6">
                                        <div class="font-semibold">{{ $payment->user->name }}</div>
                                        <div class="text-sm text-gray-600">{{ $payment->user->email }}</div>
                                    </td>
                                    <td class="py-4 px-6 font-semibold">{{ number_format($payment->token_amount, 0) }} RWAMP</td>
                                    <td class="py-4 px-6">{{ strtoupper($payment->network) }}</td>
                                    <td class="py-4 px-6">
                                        <code class="text-xs">{{ substr($payment->tx_hash, 0, 20) }}...</code>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded text-sm font-semibold 
                                            {{ $payment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                               ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-2">
                                            <a href="{{ route('reseller.payments.view', $payment) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                View
                                            </a>
                                            @if($payment->status === 'pending')
                                                <button onclick="approvePayment({{ $payment->id }})" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                                    Approve
                                                </button>
                                                <button onclick="openRejectModal({{ $payment->id }})" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                                    Reject
                                                </button>
                                            @endif
                                            <button onclick="sharePaymentDetails({{ $payment->id }})" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">
                                                Share
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg">No payments found.</p>
                </div>
            @endif
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


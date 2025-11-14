@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Payment Details</h1>
                    <p class="text-white/80">View complete payment information</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="sharePaymentDetails()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        📋 Share Details
                    </button>
                    <a href="{{ route('reseller.payments') }}" class="btn-secondary">
                        ← Back to Payments
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Payment Information Card -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <div data-payment-details style="display: none;">
Payment Details:
User: {{ $payment->user->name }} ({{ $payment->user->email }})
Token Amount: {{ number_format($payment->token_amount, 0) }} RWAMP
Network: {{ strtoupper($payment->network) }}
Transaction Hash: {{ $payment->tx_hash }}
Status: {{ ucfirst($payment->status) }}
USD Amount: ${{ number_format($payment->usd_amount ?? 0, 2) }}
PKR Amount: Rs {{ number_format($payment->pkr_amount ?? 0, 2) }}
Submitted: {{ $payment->created_at->format('F d, Y H:i:s') }}
{{ $payment->notes ? 'Notes: ' . $payment->notes : '' }}

Shared from RWAMP Reseller Dashboard
            </div>
            <h2 class="text-2xl font-bold mb-6">Payment Information</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">User Name</label>
                    <p class="text-lg font-semibold">{{ $payment->user->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">User Email</label>
                    <p class="text-lg">{{ $payment->user->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Token Amount</label>
                    <p class="text-lg font-semibold text-green-600">{{ number_format($payment->token_amount, 0) }} RWAMP</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Network</label>
                    <p class="text-lg">{{ strtoupper($payment->network) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Transaction Hash</label>
                    <p class="text-lg font-mono text-sm break-all">{{ $payment->tx_hash }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                    <p class="text-lg">
                        <span class="px-3 py-1 rounded text-sm font-semibold 
                            {{ $payment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">USD Amount</label>
                    <p class="text-lg">${{ number_format($payment->usd_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">PKR Amount</label>
                    <p class="text-lg">Rs {{ number_format($payment->pkr_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Submitted Date</label>
                    <p class="text-lg">{{ $payment->created_at->format('F d, Y H:i:s') }}</p>
                </div>
                @if($payment->notes)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Notes</label>
                    <p class="text-lg">{{ $payment->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($payment->status === 'pending')
        <div class="bg-white rounded-xl shadow-xl p-6">
            <h2 class="text-2xl font-bold mb-6">Actions</h2>
            <div class="flex gap-4">
                <button onclick="approvePayment({{ $payment->id }})" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                    Approve Payment
                </button>
                <button onclick="openRejectModal()" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors">
                    Reject Payment
                </button>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Reject Payment</h3>
        <form method="POST" action="{{ route('reseller.payments.reject', $payment) }}">
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

function openRejectModal() {
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function sharePaymentDetails() {
    const details = `Payment Details:
User: {{ $payment->user->name }} ({{ $payment->user->email }})
Token Amount: {{ number_format($payment->token_amount, 0) }} RWAMP
Network: {{ strtoupper($payment->network) }}
Transaction Hash: {{ $payment->tx_hash }}
Status: {{ ucfirst($payment->status) }}
USD Amount: ${{ number_format($payment->usd_amount ?? 0, 2) }}
PKR Amount: Rs {{ number_format($payment->pkr_amount ?? 0, 2) }}
Submitted: {{ $payment->created_at->format('F d, Y H:i:s') }}
{{ $payment->notes ? 'Notes: ' . $payment->notes : '' }}

Shared from RWAMP Reseller Dashboard`;

    navigator.clipboard.writeText(details).then(() => {
        alert('Payment details copied to clipboard!');
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = details;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Payment details copied to clipboard!');
    });
}
</script>
@endsection


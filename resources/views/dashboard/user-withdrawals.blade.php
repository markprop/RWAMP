@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.investor-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">My Withdrawal Requests</h1>
                        <p class="text-gray-500 text-sm mt-1.5">View your withdrawal request status and details</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">
        <!-- Current Balance Card -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <h2 class="text-lg font-montserrat font-bold mb-4">Current Balance</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-black text-white rounded-lg p-4 text-center">
                    <div class="text-xs text-white/70 mb-1">Token Balance</div>
                    <div class="text-2xl font-bold">{{ number_format($user->token_balance ?? 0, 2) }}</div>
                </div>
                <div class="bg-accent text-black rounded-lg p-4 text-center">
                    <div class="text-xs text-black/70 mb-1">Value (Rs)</div>
                    <div class="text-2xl font-bold">{{ number_format(($user->token_balance ?? 0) * ($officialPrice ?? 0.70), 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Withdrawals Table -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($withdrawals as $withdrawal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $withdrawal->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ number_format($withdrawal->token_amount, 2) }} RWAMP
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-mono break-all max-w-xs">
                                        {{ $withdrawal->wallet_address }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($withdrawal->status === 'approved')
                                        @if($withdrawal->receipt_path)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Approved - Waiting for Receipt</span>
                                        @endif
                                    @elseif($withdrawal->status === 'rejected')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $withdrawal->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button 
                                        onclick="openDetailsModal({{ $withdrawal->id }}, {{ $withdrawal->token_amount }}, '{{ $withdrawal->wallet_address }}', '{{ $withdrawal->status }}', '{{ $withdrawal->notes ?? '' }}', '{{ $withdrawal->created_at->format('M d, Y H:i') }}', '{{ $withdrawal->updated_at->format('M d, Y H:i') }}', {{ $withdrawal->receipt_path ? 'true' : 'false' }}, '{{ $withdrawal->transaction_hash ?? '' }}', '{{ $withdrawal->transfer_completed_at ? $withdrawal->transfer_completed_at->format('M d, Y H:i') : '' }}', '{{ $withdrawal->receipt_path ? route('user.withdrawals.receipt', $withdrawal) : '' }}')"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No withdrawal requests yet</p>
                                        <p class="text-sm text-gray-600">Submit a withdrawal request from your profile page</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($withdrawals->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="details-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-montserrat font-bold">Withdrawal Request Details</h3>
            <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div class="space-y-4" id="details-content">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
function openDetailsModal(id, amount, wallet, status, notes, submittedDate, updatedDate, hasReceipt, transactionHash, transferCompletedAt, receiptUrl) {
    const modal = document.getElementById('details-modal');
    const content = document.getElementById('details-content');
    
    let statusBadge = '';
    let statusText = '';
    let statusInfoBox = '';
    
    if (status === 'approved') {
        if (hasReceipt) {
            statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>';
            statusText = 'Your withdrawal transfer has been successfully completed. Tokens have been transferred to your wallet address.';
            statusInfoBox = `
                <div class="mt-3 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-green-800">Transfer Completed</p>
                            <p class="text-xs text-green-700 mt-1">Your tokens have been successfully transferred to your wallet address.</p>
                            ${transferCompletedAt ? `<p class="text-xs text-green-600 mt-1">Completed on: ${transferCompletedAt}</p>` : ''}
                        </div>
                    </div>
                </div>
            `;
        } else {
            statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Approved - Waiting for Receipt</span>';
            statusText = 'Your withdrawal request has been approved. Tokens have been deducted from your balance. Admin is processing the transfer and will submit a receipt to validate the transaction.';
            statusInfoBox = `
                <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Waiting for Receipt Validation</p>
                            <p class="text-xs text-blue-700 mt-1">Your request is accepted and approved. Admin will process the transfer and submit a receipt to validate the transaction. You will be notified once the transfer is completed.</p>
                        </div>
                    </div>
                </div>
            `;
        }
    } else if (status === 'rejected') {
        statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>';
        statusText = 'Your withdrawal request has been rejected. Please check the notes below for more information.';
    } else {
        statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>';
        statusText = 'Your withdrawal request is pending review. Admin will process it within 24 hours.';
    }
    
    content.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Request ID</label>
            <p class="text-gray-900 font-mono">#${id}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Token Amount</label>
            <p class="text-gray-900 font-semibold text-lg">${parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} RWAMP</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Wallet Address</label>
            <p class="text-gray-900 font-mono break-all bg-gray-50 p-3 rounded">${wallet}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <div class="mb-2">${statusBadge}</div>
            <p class="text-sm text-gray-600">${statusText}</p>
            ${statusInfoBox || ''}
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Submitted Date</label>
            <p class="text-gray-900">${submittedDate}</p>
        </div>
        ${updatedDate !== submittedDate ? `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
            <p class="text-gray-900">${updatedDate}</p>
        </div>
        ` : ''}
        ${transactionHash ? `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Hash</label>
            <p class="text-gray-900 font-mono break-all bg-gray-50 p-3 rounded text-sm">${transactionHash}</p>
        </div>
        ` : ''}
        ${receiptUrl ? `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt</label>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Transaction Receipt</p>
                            <p class="text-xs text-gray-600">Proof of transfer validation uploaded by admin</p>
                        </div>
                    </div>
                    <a href="${receiptUrl}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Receipt
                    </a>
                </div>
            </div>
        </div>
        ` : ''}
        ${notes ? `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Admin Notes</label>
            <p class="text-gray-900 bg-yellow-50 p-3 rounded border border-yellow-200">${notes}</p>
        </div>
        ` : ''}
    `;
    
    modal.classList.remove('hidden');
}

function closeDetailsModal() {
    document.getElementById('details-modal').classList.add('hidden');
}
</script>
@endsection


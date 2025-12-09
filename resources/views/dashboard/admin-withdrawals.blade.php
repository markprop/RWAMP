@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="withdrawalManagement()">
    <!-- Sidebar -->
    @include('components.admin-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Withdrawal Requests</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Manage user withdrawal requests</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">{{ session('error') }}</div>
        @endif

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 mb-6">
            <form 
                method="GET" 
                action="{{ route('admin.withdrawals') }}" 
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4"
                @submit.prevent="submitFilters($event.target)"
            >
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="User name, email, or wallet address"
                        class="form-input w-full min-h-[44px]"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-input w-full min-h-[44px]">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button 
                        type="submit" 
                        class="btn-primary flex-1 min-h-[44px]"
                        :disabled="isListLoading"
                    >
                        <span x-show="!isListLoading">Filter</span>
                        <span x-show="isListLoading">Loading…</span>
                    </button>
                    <button 
                        type="button" 
                        class="btn-secondary min-h-[44px] hidden sm:inline-flex"
                        @click="clearFilters"
                    >
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Withdrawals Table -->
        <div id="adminWithdrawalsTable" class="bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="rw-table-scroll overflow-x-auto -mx-4 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 whitespace-nowrap text-xs sm:text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($withdrawals as $withdrawal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $withdrawal->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $withdrawal->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $withdrawal->user->email }}</div>
                                </td>
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
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                    @elseif($withdrawal->status === 'rejected')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $withdrawal->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            @click="openViewModal({{ $withdrawal->id }}, '{{ $withdrawal->user->name }}', '{{ $withdrawal->user->email }}', {{ $withdrawal->token_amount }}, '{{ $withdrawal->wallet_address }}', '{{ $withdrawal->status }}', '{{ $withdrawal->notes ?? '' }}', '{{ $withdrawal->created_at->format('M d, Y H:i') }}', '{{ $withdrawal->receipt_path ? asset('storage/' . $withdrawal->receipt_path) : '' }}', '{{ $withdrawal->transaction_hash ?? '' }}', '{{ $withdrawal->transfer_completed_at ? $withdrawal->transfer_completed_at->format('M d, Y H:i') : '' }}')"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            View
                                        </button>
                                        @if($withdrawal->status === 'pending')
                                            <button 
                                                @click="openEditModal({{ $withdrawal->id }}, {{ $withdrawal->token_amount }}, '{{ $withdrawal->wallet_address }}', '{{ $withdrawal->notes ?? '' }}')"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </button>
                                            <form 
                                                method="POST" 
                                                action="{{ route('admin.withdrawals.approve', $withdrawal) }}"
                                                class="inline"
                                                id="approve-form-{{ $withdrawal->id }}"
                                            >
                                                @csrf
                                            </form>
                                            <button 
                                                @click="openApproveConfirm({{ $withdrawal->id }}, '{{ $withdrawal->user->name }}')"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Approve
                                            </button>
                                            <button 
                                                @click="openRejectModal({{ $withdrawal->id }}, '{{ $withdrawal->user->name }}')"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Reject
                                            </button>
                                            <button 
                                                @click="openDeleteModal({{ $withdrawal->id }}, '{{ $withdrawal->user->name }}')"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        @elseif($withdrawal->status === 'approved' && !$withdrawal->receipt_path)
                                            <button 
                                                @click="openReceiptModal({{ $withdrawal->id }}, '{{ $withdrawal->user->name }}')"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Submit Receipt
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No withdrawal requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $withdrawals->links() }}
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div x-show="viewModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.away="viewModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-montserrat font-bold">Withdrawal Request Details</h3>
                <button @click="viewModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Request ID</label>
                    <p class="text-gray-900 font-mono">#<span x-text="viewData.id"></span></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">User</label>
                    <p class="text-gray-900" x-text="viewData.name"></p>
                    <p class="text-gray-600 text-sm" x-text="viewData.email"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Token Amount</label>
                    <p class="text-gray-900 font-semibold" x-text="formatNumber(viewData.amount) + ' RWAMP'"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Wallet Address</label>
                    <p class="text-gray-900 font-mono break-all" x-text="viewData.wallet"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <span x-show="viewData.status === 'approved'" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                    <span x-show="viewData.status === 'rejected'" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                    <span x-show="viewData.status === 'pending'" class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Submitted Date</label>
                    <p class="text-gray-900" x-text="viewData.date"></p>
                </div>
                <div x-show="viewData.notes">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <p class="text-gray-900" x-text="viewData.notes"></p>
                </div>
                <div x-show="viewData.receiptPath">
                    <label class="block text-sm font-medium text-gray-700">Receipt</label>
                    <a :href="viewData.receiptPath" target="_blank" class="text-blue-600 hover:text-blue-900 underline">View Receipt</a>
                </div>
                <div x-show="viewData.transactionHash">
                    <label class="block text-sm font-medium text-gray-700">Transaction Hash</label>
                    <p class="text-gray-900 font-mono break-all" x-text="viewData.transactionHash"></p>
                </div>
                <div x-show="viewData.transferCompletedAt">
                    <label class="block text-sm font-medium text-gray-700">Transfer Completed At</label>
                    <p class="text-gray-900" x-text="viewData.transferCompletedAt"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.away="editModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-montserrat font-bold">Edit Withdrawal Request</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form method="POST" :action="editFormAction" @submit.prevent="submitEdit($event)">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Token Amount</label>
                        <input type="number" name="token_amount" x-model="editData.amount" step="0.01" min="0.01" class="form-input w-full" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Wallet Address</label>
                        <input type="text" name="wallet_address" x-model="editData.wallet" class="form-input w-full" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea name="notes" x-model="editData.notes" rows="3" class="form-input w-full"></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.away="rejectModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-montserrat font-bold">Reject Withdrawal Request</h3>
                <button @click="rejectModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form method="POST" :action="rejectFormAction" @submit.prevent="submitReject($event)">
                @csrf
                <div class="space-y-4">
                    <p class="text-gray-700">Are you sure you want to reject the withdrawal request from <strong x-text="rejectData.name"></strong>?</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason (Optional)</label>
                        <textarea name="rejection_reason" x-model="rejectData.reason" rows="3" class="form-input w-full" placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.away="deleteModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-montserrat font-bold text-red-600">Delete Withdrawal Request</h3>
                <button @click="deleteModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <p class="text-gray-700 mb-4">Are you sure you want to delete the withdrawal request from <strong x-text="deleteData.name"></strong>? This action cannot be undone. Tokens will be refunded to the user.</p>
            <form method="POST" :action="deleteFormAction" @submit.prevent="submitDelete($event)">
                @csrf
                @method('DELETE')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deletion Reason (Optional)</label>
                        <textarea name="deletion_reason" x-model="deleteData.reason" rows="3" class="form-input w-full" placeholder="Enter reason for deletion..."></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div x-show="approveConfirmModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.away="approveConfirmModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-montserrat font-bold text-green-600">Approve Withdrawal Request</h3>
                <button @click="approveConfirmModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <p class="text-gray-700 mb-4">Are you sure you want to approve the withdrawal request from <strong x-text="approveConfirmData.name"></strong>? Tokens were already deducted on submission. Admin will transfer manually within 24 hours.</p>
            <div class="flex gap-3 justify-end">
                <button type="button" @click="approveConfirmModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">Cancel</button>
                <button type="button" @click="confirmApprove()" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">Approve</button>
            </div>
        </div>
    </div>

    <!-- Receipt Submission Modal -->
    <div x-show="receiptModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.away="receiptModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-montserrat font-bold text-blue-600">Submit Transfer Receipt</h3>
                <button @click="receiptModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form method="POST" :action="receiptFormAction" enctype="multipart/form-data" @submit.prevent="submitReceipt($event)">
                @csrf
                <div class="space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <strong>Instructions:</strong> Upload a screenshot or PDF receipt showing the successful transfer of tokens to the user's wallet address. This validates that the transfer has been completed.
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Receipt/Proof of Transfer <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="file" 
                            name="receipt" 
                            accept="image/*,.pdf"
                            required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            @change="handleReceiptFileChange($event)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, PDF (Max: 5MB)</p>
                        <div x-show="receiptFileName" class="mt-2 text-sm text-green-600">
                            <span x-text="receiptFileName"></span> selected
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Transaction Hash (Optional)
                        </label>
                        <input 
                            type="text" 
                            name="transaction_hash" 
                            x-model="receiptData.transactionHash"
                            placeholder="Enter blockchain transaction hash if available"
                            class="form-input w-full"
                        />
                        <p class="mt-1 text-xs text-gray-500">If you have a blockchain transaction hash, enter it here for verification</p>
                    </div>
                    
                    <div class="flex gap-3 justify-end pt-4 border-t">
                        <button type="button" @click="receiptModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">Submit Receipt</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.visible" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-4 right-4 z-50 max-w-sm w-full"
         @click="toast.visible = false">
        <div :class="{
            'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
            'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
            'bg-yellow-50 border-yellow-200 text-yellow-800': toast.type === 'warning',
            'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info'
        }" class="border rounded-lg shadow-lg p-4 cursor-pointer">
            <p class="text-sm font-medium" x-text="toast.message"></p>
        </div>
    </div>
</div>

<script>
function withdrawalManagement() {
    return {
        isListLoading: false,
        toast: { visible: false, message: '', type: 'success' },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.visible = true;
            setTimeout(() => { this.toast.visible = false; }, 3000);
        },
        async submitFilters(form) {
            this.isListLoading = true;
            try {
                const params = new URLSearchParams(new FormData(form)).toString();
                const url = form.action + (params ? ('?' + params) : '');
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Server error: ' + response.status);
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#adminWithdrawalsTable');
                const current = document.querySelector('#adminWithdrawalsTable');
                if (incoming && current) current.innerHTML = incoming.innerHTML;
                if (window.history && window.history.replaceState) window.history.replaceState({}, '', url);
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load withdrawals. Please try again.', 'error');
            } finally {
                this.isListLoading = false;
            }
        },
        clearFilters() {
            const form = document.querySelector('form[action="{{ route('admin.withdrawals') }}"]');
            if (!form) return;
            ['search', 'status'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });
            this.submitFilters(form);
        },
        viewModalOpen: false,
        editModalOpen: false,
        rejectModalOpen: false,
        deleteModalOpen: false,
        approveConfirmModalOpen: false,
        receiptModalOpen: false,
        viewData: {},
        editData: {},
        rejectData: {},
        deleteData: {},
        approveConfirmData: {},
        receiptData: {},
        receiptFileName: '',
        approveFormAction: '',
        editFormAction: '',
        rejectFormAction: '',
        deleteFormAction: '',
        receiptFormAction: '',
        
        openViewModal(id, name, email, amount, wallet, status, notes, date, receiptPath, transactionHash, transferCompletedAt) {
            this.viewData = { 
                id, 
                name, 
                email, 
                amount, 
                wallet, 
                status, 
                notes: notes || '', 
                date,
                receiptPath: receiptPath || '',
                transactionHash: transactionHash || '',
                transferCompletedAt: transferCompletedAt || ''
            };
            this.viewModalOpen = true;
        },
        
        openEditModal(id, amount, wallet, notes) {
            this.editData = { id, amount, wallet, notes: notes || '' };
            this.editFormAction = '{{ url("/dashboard/admin/withdrawals") }}/' + id;
            this.editModalOpen = true;
        },
        
        openRejectModal(id, name) {
            this.rejectData = { id, name, reason: '' };
            this.rejectFormAction = '{{ url("/dashboard/admin/withdrawals") }}/' + id + '/reject';
            this.rejectModalOpen = true;
        },
        
        openDeleteModal(id, name) {
            this.deleteData = { id, name, reason: '' };
            this.deleteFormAction = '{{ url("/dashboard/admin/withdrawals") }}/' + id;
            this.deleteModalOpen = true;
        },
        
        openReceiptModal(id, name) {
            this.receiptData = { id, name, transactionHash: '' };
            this.receiptFormAction = '{{ url("/dashboard/admin/withdrawals") }}/' + id + '/submit-receipt';
            this.receiptFileName = '';
            this.receiptModalOpen = true;
        },
        
        handleReceiptFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.receiptFileName = file.name;
            } else {
                this.receiptFileName = '';
            }
        },
        
        async submitReceipt(event) {
            event.target.closest('form').submit();
        },
        
        formatNumber(value) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },
        
        openApproveConfirm(id, name) {
            this.approveConfirmData = { id, name };
            this.approveFormAction = '{{ url("/dashboard/admin/withdrawals") }}/' + id + '/approve';
            this.approveConfirmModalOpen = true;
        },
        
        confirmApprove() {
            const form = document.getElementById('approve-form-' + this.approveConfirmData.id);
            if (form) {
                this.approveConfirmModalOpen = false;
                form.submit();
            }
        },
        
        async submitEdit(event) {
            event.target.closest('form').submit();
        },
        
        async submitReject(event) {
            event.target.closest('form').submit();
        },
        
        async submitDelete(event) {
            event.target.closest('form').submit();
        }
    };
}
</script>
@endsection


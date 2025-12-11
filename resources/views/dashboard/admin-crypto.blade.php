@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="window.cryptoPaymentManagement ? window.cryptoPaymentManagement() : {}">
    <!-- Sidebar -->
    @include('components.admin-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Crypto Payments</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Review, approve, reject, edit, or delete payment submissions</p>
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
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">{{ $errors->first() }}</div>
        @endif

        <!-- Search and Filters -->
        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 mb-6">
            <form 
                method="GET" 
                action="{{ route('admin.crypto.payments') }}" 
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4"
                @submit.prevent="submitFilters($event.target)"
            >
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="User name, email, or TX hash"
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Network</label>
                    <select name="network" class="form-input w-full min-h-[44px]">
                        <option value="">All Networks</option>
                        <option value="TRC20" {{ request('network') === 'TRC20' ? 'selected' : '' }}>TRC20</option>
                        <option value="ERC20" {{ request('network') === 'ERC20' ? 'selected' : '' }}>ERC20</option>
                        <option value="BEP20" {{ request('network') === 'BEP20' ? 'selected' : '' }}>BEP20</option>
                        <option value="BTC" {{ request('network') === 'BTC' ? 'selected' : '' }}>BTC</option>
                        <option value="BNB" {{ request('network') === 'BNB' ? 'selected' : '' }}>BNB</option>
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
                        Clear
                    </button>
                </div>
            </form>
        </div>

        <div id="adminCryptoPaymentsTable" class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">Payment Submissions</h3>
                <span class="rw-badge">{{ $payments->total() }} total</span>
            </div>
            <div class="rw-table-scroll overflow-x-auto -mx-4 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-left text-gray-600 border-b">
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Date @if(request('sort') === 'created_at') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">User</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'token_amount', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Tokens @if(request('sort') === 'token_amount') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'network', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Network @if(request('sort') === 'network') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">TX Hash</th>
                            <th class="py-3 pr-6">Screenshot</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Status @if(request('sort') === 'status') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr class="border-b">
                                <td class="py-3 pr-6">{{ $p->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3 pr-6">
                                    {{ $p->user->name ?? 'N/A' }}<br>
                                    <span class="text-gray-500 text-xs">{{ $p->user->email ?? 'N/A' }}</span>
                                </td>
                                <td class="py-3 pr-6 font-semibold">{{ number_format($p->token_amount, 0) }}</td>
                                <td class="py-3 pr-6">
                                    <span class="rw-badge">{{ $p->network }}</span>
                                </td>
                                <td class="py-3 pr-6">
                                    <a href="#" 
                                       @click.prevent="openViewDetailsModal({{ $p->id }})"
                                       class="text-blue-600 hover:text-blue-800 hover:underline break-all font-mono text-xs" 
                                       title="{{ $p->tx_hash }}">
                                        {{ \Illuminate\Support\Str::limit($p->tx_hash, 20) }}
                                    </a>
                                </td>
                                <td class="py-3 pr-6">
                                    @if($p->screenshot)
                                        <button 
                                            @click="openScreenshot('{{ route('admin.crypto.payments.screenshot', $p) }}', 'Screenshot - {{ $p->user->name ?? 'N/A' }}')"
                                            class="text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium cursor-pointer">
                                            📷 View
                                        </button>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-6">
                                    @if($p->status === 'approved')
                                        <span class="rw-badge bg-green-100 text-green-800">Approved</span>
                                    @elseif($p->status === 'rejected')
                                        <span class="rw-badge bg-red-100 text-red-800">Rejected</span>
                                    @else
                                        <span class="rw-badge bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-6 whitespace-nowrap">
                                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2">
                                        <button 
                                            @click="openViewDetailsModal({{ $p->id }})"
                                            class="btn-secondary btn-small">👁️ View</button>
                                        <button 
                                            @click="openEditModal({{ $p->id }}, @js($p->token_amount), @js($p->usd_amount ?? ''), @js($p->pkr_amount ?? ''), @js($p->network), @js($p->tx_hash), @js($p->status), @js($p->notes ?? ''))"
                                            class="btn-secondary btn-small">✏️ Edit</button>
                                    @if($p->status === 'pending')
                                        <form method="POST" action="{{ route('admin.crypto.approve', $p) }}" class="inline">
                                            @csrf
                                                <button type="submit" class="btn-primary btn-small bg-green-600 hover:bg-green-700">✅ Approve</button>
                                        </form>
                                            <form method="POST" action="{{ route('admin.crypto.reject', $p) }}" class="inline">
                                            @csrf
                                                <button type="submit" class="btn-primary btn-small bg-red-600 hover:bg-red-700">❌ Reject</button>
                                        </form>
                                    @endif
                                        <button 
                                            @click="openDeleteModal({{ $p->id }}, @js($p->user->name ?? 'N/A'), @js($p->tx_hash))"
                                            class="btn-primary btn-small bg-red-600 hover:bg-red-700">🗑️ Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-6 text-center text-gray-500">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div x-show="viewDetailsModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="rw-modal rw-modal--mobile"
         @click.self="viewDetailsModalOpen = false"
         @keydown.escape.window="viewDetailsModalOpen = false">
        <div class="rw-modal__panel max-w-4xl">
            <div class="sticky bg-white" style="top: 28px;" border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-montserrat font-bold">Payment Details</h3>
                <button @click="viewDetailsModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <div class="p-6" x-show="viewDetailsLoading">
                <div class="text-center py-8">Loading...</div>
            </div>
            <div class="p-6" x-show="!viewDetailsLoading && viewDetailsData">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">User Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-600">Name:</span> <span x-text="getUserName()"></span></div>
                            <div><span class="text-gray-600">Email:</span> <span x-text="getUserEmail()"></span></div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Payment Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-600">Token Amount:</span> <span x-text="getTokenAmount()"></span></div>
                            <div><span class="text-gray-600">USD Amount:</span> <span x-text="getUsdAmount()"></span></div>
                            <div><span class="text-gray-600">PKR Amount:</span> <span x-text="getPkrAmount()"></span></div>
                            <div><span class="text-gray-600">Network:</span> <span x-text="getNetwork()"></span></div>
                            <div><span class="text-gray-600">Status:</span> <span x-text="getStatus()"></span></div>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <h4 class="font-semibold text-gray-700 mb-3">Transaction Hash</h4>
                        <div class="bg-gray-100 p-3 rounded font-mono text-xs break-all" x-text="getTxHash()"></div>
                    </div>
                    <div class="md:col-span-2" x-show="getNotes()">
                        <h4 class="font-semibold text-gray-700 mb-3">Notes</h4>
                        <div class="bg-gray-100 p-3 rounded text-sm" x-text="getNotes()"></div>
                    </div>
                    <div class="md:col-span-2">
                        <h4 class="font-semibold text-gray-700 mb-3">Timestamps</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-600">Created:</span> <span x-text="getCreatedAt()"></span></div>
                            <div><span class="text-gray-600">Updated:</span> <span x-text="getUpdatedAt()"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="rw-modal rw-modal--mobile"
         @click.self="editModalOpen = false"
         @keydown.escape.window="editModalOpen = false">
        <div class="rw-modal__panel max-w-2xl">
            <div class="sticky bg-white" style="top: 28px;" border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-montserrat font-bold">Edit Payment</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form method="POST" :action="editFormAction" class="p-6">
                @csrf
                @method('PUT')
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Token Amount</label>
                        <input type="number" step="0.01" name="token_amount" x-model="editTokenAmount" class="form-input w-full" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Network</label>
                        <select name="network" x-model="editNetwork" class="form-input w-full" required>
                            <option value="TRC20">TRC20</option>
                            <option value="ERC20">ERC20</option>
                            <option value="BEP20">BEP20</option>
                            <option value="BTC">BTC</option>
                            <option value="BNB">BNB</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">USD Amount</label>
                        <input type="text" name="usd_amount" x-model="editUsdAmount" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">PKR Amount</label>
                        <input type="text" name="pkr_amount" x-model="editPkrAmount" class="form-input w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">TX Hash</label>
                        <input type="text" name="tx_hash" x-model="editTxHash" class="form-input w-full font-mono" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" x-model="editStatus" class="form-input w-full" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" x-model="editNotes" rows="3" class="form-input w-full"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6 flex-col sm:flex-row">
                    <button type="button" @click="editModalOpen = false" class="flex-1 btn-secondary btn-small">Cancel</button>
                    <button type="submit" class="flex-1 btn-primary btn-small">Update Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="deleteModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="rw-modal rw-modal--mobile"
         @click.self="deleteModalOpen = false"
         @keydown.escape.window="deleteModalOpen = false">
        <div class="rw-modal__panel max-w-md">
            <h3 class="text-xl font-montserrat font-bold mb-4">Delete Payment</h3>
            <p class="text-gray-700 mb-4">Are you sure you want to delete this payment?</p>
            <div class="bg-gray-100 p-3 rounded mb-4 text-sm">
                <div><strong>User:</strong> <span x-text="deleteUserName"></span></div>
                <div><strong>TX Hash:</strong> <span x-text="deleteTxHash" class="font-mono text-xs break-all"></span></div>
            </div>
            <p class="text-red-600 text-sm mb-4">This action cannot be undone.</p>
            <form method="POST" :action="deleteFormAction" class="flex gap-3 flex-col sm:flex-row">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="flex-1 btn-secondary btn-small">Cancel</button>
                <button type="submit" class="flex-1 btn-primary btn-small bg-red-600 hover:bg-red-700">Delete</button>
            </form>
        </div>
    </div>

    <!-- Screenshot Viewer Modal -->
    <div x-show="screenshotModal.open" 
         x-cloak
         style="z-index: 9999;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-90 p-4"
         @click.self="closeScreenshot()"
         @keydown.escape.window="closeScreenshot()">
        <div class="relative max-w-6xl max-h-full w-full h-full flex flex-col" style="z-index: 10000;">
            <div class="flex items-center justify-between bg-gray-900 text-white px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-montserrat font-bold" x-text="screenshotModal.title"></h3>
                <button 
                    type="button"
                    @click="closeScreenshot()" 
                    class="text-white hover:text-gray-300 transition p-2 rounded-full hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-white"
                    title="Close (Esc)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto bg-gray-800 flex items-center justify-center p-4">
                <img :src="screenshotModal.src" 
                     :alt="screenshotModal.title"
                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                     x-on:error="$el.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2218%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EImage not found%3C/text%3E%3C/svg%3E'">
            </div>
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

@push('scripts')
<script>
window.cryptoPaymentManagement = function() {
    return {
        viewDetailsModalOpen: false,
        viewDetailsLoading: false,
        viewDetailsData: null,
        editModalOpen: false,
        editPaymentId: null,
        editTokenAmount: '',
        editUsdAmount: '',
        editPkrAmount: '',
        editNetwork: '',
        editTxHash: '',
        editStatus: '',
        editNotes: '',
        editFormAction: '',
        deleteModalOpen: false,
        deletePaymentId: null,
        deleteUserName: '',
        deleteTxHash: '',
        deleteFormAction: '',
        screenshotModal: {
            open: false,
            src: '',
            title: ''
        },
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
                const incoming = doc.querySelector('#adminCryptoPaymentsTable');
                const current = document.querySelector('#adminCryptoPaymentsTable');
                if (incoming && current) current.innerHTML = incoming.innerHTML;
                if (window.history && window.history.replaceState) window.history.replaceState({}, '', url);
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load payments. Please try again.', 'error');
            } finally {
                this.isListLoading = false;
            }
        },
        clearFilters() {
            const form = document.querySelector('form[action="{{ route('admin.crypto.payments') }}"]');
            if (!form) return;
            ['search', 'status', 'network'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });
            this.submitFilters(form);
        },
        async openViewDetailsModal(paymentId) {
            this.viewDetailsModalOpen = true;
            this.viewDetailsLoading = true;
            this.viewDetailsData = null;
            
            try {
                const response = await fetch('{{ url("/dashboard/admin/crypto-payments") }}/' + paymentId + '/details');
                const data = await response.json();
                this.viewDetailsData = data;
            } catch (error) {
                console.error('Error fetching payment details:', error)
                alert('Failed to load payment details. Please try again.');
            } finally {
                this.viewDetailsLoading = false;
            }
        },
        openEditModal(paymentId, tokenAmount, usdAmount, pkrAmount, network, txHash, status, notes) {
            this.editPaymentId = paymentId;
            this.editTokenAmount = tokenAmount;
            this.editUsdAmount = usdAmount || '';
            this.editPkrAmount = pkrAmount || '';
            this.editNetwork = network;
            this.editTxHash = txHash;
            this.editStatus = status;
            this.editNotes = notes || '';
            this.editFormAction = '{{ url("/dashboard/admin/crypto-payments") }}/' + paymentId;
            this.editModalOpen = true;
        },
        openDeleteModal(paymentId, userName, txHash) {
            this.deletePaymentId = paymentId;
            this.deleteUserName = userName;
            this.deleteTxHash = txHash;
            this.deleteFormAction = '{{ url("/dashboard/admin/crypto-payments") }}/' + paymentId;
            this.deleteModalOpen = true;
        },
        openScreenshot(src, title) {
            this.screenshotModal.src = src;
            this.screenshotModal.title = title;
            this.screenshotModal.open = true;
        },
        closeScreenshot() {
            this.screenshotModal.open = false;
            this.screenshotModal.src = '';
            this.screenshotModal.title = '';
        },
        getUserName() {
            if (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.name) {
                return this.viewDetailsData.user.name;
            }
            return '—';
        },
        getUserEmail() {
            if (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.email) {
                return this.viewDetailsData.user.email;
            }
            return '—';
        },
        getTokenAmount() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.token_amount) {
                return parseFloat(this.viewDetailsData.payment.token_amount).toLocaleString();
            }
            return '—';
        },
        getUsdAmount() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.usd_amount) {
                return this.viewDetailsData.payment.usd_amount;
            }
            return '—';
        },
        getPkrAmount() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.pkr_amount) {
                return this.viewDetailsData.payment.pkr_amount;
            }
            return '—';
        },
        getNetwork() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.network) {
                return this.viewDetailsData.payment.network;
            }
            return '—';
        },
        getStatus() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.status) {
                const status = this.viewDetailsData.payment.status;
                return status.charAt(0).toUpperCase() + status.slice(1);
            }
            return '—';
        },
        getTxHash() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.tx_hash) {
                return this.viewDetailsData.payment.tx_hash;
            }
            return '—';
        },
        getNotes() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.notes) {
                return this.viewDetailsData.payment.notes;
            }
            return '';
        },
        getCreatedAt() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.created_at) {
                return new Date(this.viewDetailsData.payment.created_at).toLocaleString();
            }
            return '—';
        },
        getUpdatedAt() {
            if (this.viewDetailsData && this.viewDetailsData.payment && this.viewDetailsData.payment.updated_at) {
                return new Date(this.viewDetailsData.payment.updated_at).toLocaleString();
            }
            return '—';
        }
    }
}
</script>
@endpush
@endsection

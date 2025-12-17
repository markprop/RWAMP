@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="kycManagement()">
    <!-- Sidebar -->
    @include('components.admin-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">KYC Review</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Review and approve or reject KYC submissions</p>
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
                action="{{ route('admin.kyc.list') }}" 
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4"
                @submit.prevent="submitFilters($event.target)"
            >
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Name, email, ID number, or full name"
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
                    <select name="id_type" class="form-input w-full min-h-[44px]">
                        <option value="">All Types</option>
                        <option value="cnic" {{ request('id_type') === 'cnic' ? 'selected' : '' }}>CNIC</option>
                        <option value="nicop" {{ request('id_type') === 'nicop' ? 'selected' : '' }}>NICOP</option>
                        <option value="passport" {{ request('id_type') === 'passport' ? 'selected' : '' }}>Passport</option>
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

        <div id="adminKycTable" class="bg-white rounded-xl shadow-xl p-4 sm:p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">KYC Submissions</h3>
                <span class="rw-badge">{{ $kycSubmissions->total() }} total</span>
            </div>
            <div class="rw-table-scroll overflow-x-auto -mx-4 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-left text-gray-600 border-b">
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Name @if(request('sort') === 'name') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Email @if(request('sort') === 'email') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'kyc_id_type', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    ID Type @if(request('sort') === 'kyc_id_type') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">ID Number</th>
                            <th class="py-3 pr-6">Full Name (on ID)</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'kyc_submitted_at', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Submitted At @if(request('sort') === 'kyc_submitted_at') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'kyc_status', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Status @if(request('sort') === 'kyc_status') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">Documents</th>
                            <th class="py-3 pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kycSubmissions as $user)
                            <tr class="border-b">
                                <td class="py-3 pr-6">{{ $user->name }}</td>
                                <td class="py-3 pr-6">{{ $user->email }}</td>
                                <td class="py-3 pr-6">
                                    <span class="rw-badge">{{ strtoupper($user->kyc_id_type ?? '—') }}</span>
                                </td>
                                <td class="py-3 pr-6 font-mono text-xs">{{ $user->kyc_id_number ?? '—' }}</td>
                                <td class="py-3 pr-6">{{ $user->kyc_full_name ?? '—' }}</td>
                                <td class="py-3 pr-6">{{ $user->kyc_submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="py-3 pr-6">
                                    @if($user->kyc_status === 'approved')
                                        <span class="rw-badge bg-green-100 text-green-800">Approved</span>
                                    @elseif($user->kyc_status === 'rejected')
                                        <span class="rw-badge bg-red-100 text-red-800">Rejected</span>
                                    @else
                                        <span class="rw-badge bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-6">
                                    <div class="flex gap-2">
                                        @if($user->kyc_id_front_path)
                                            <button @click="openImage('{{ route('admin.kyc.download', ['user' => $user->id, 'type' => 'front']) }}', 'ID Front - {{ $user->name }}')" 
                                                    class="text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium cursor-pointer">
                                                📄 Front
                                            </button>
                                        @endif
                                        @if($user->kyc_id_back_path)
                                            <button @click="openImage('{{ route('admin.kyc.download', ['user' => $user->id, 'type' => 'back']) }}', 'ID Back - {{ $user->name }}')" 
                                                    class="text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium cursor-pointer">
                                                📄 Back
                                            </button>
                                        @endif
                                        @if($user->kyc_selfie_path)
                                            <button @click="openImage('{{ route('admin.kyc.download', ['user' => $user->id, 'type' => 'selfie']) }}', 'Selfie - {{ $user->name }}')" 
                                                    class="text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium cursor-pointer">
                                                📷 Selfie
                                            </button>
                                        @endif
                                        @if(!$user->kyc_id_front_path && !$user->kyc_id_back_path && !$user->kyc_selfie_path)
                                            <span class="text-gray-500 text-xs">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 pr-6 whitespace-nowrap">
                                    <div class="flex gap-2">
                                        @if($user->kyc_status === 'pending')
                                            <form method="POST" action="{{ route('admin.kyc.approve', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="btn-secondary text-sm px-3 py-1.5">✅ Approve</button>
                                            </form>
                                            <button @click="openRejectModal({{ $user->id }}, @js($user->name))" 
                                                    class="btn-primary text-sm px-3 py-1.5">❌ Reject</button>
                                        @endif
                                        <button @click="openEditModal({{ $user->id }}, @js($user->name), @js($user->email), @js($user->kyc_id_type ?? ''), @js($user->kyc_id_number ?? ''), @js($user->kyc_full_name ?? ''), @js($user->kyc_status ?? ''), @js($user->kyc_rejection_reason ?? '') )" 
                                                class="btn-secondary text-sm px-3 py-1.5">✏️ Edit</button>
                                        <button @click="openDeleteModal({{ $user->id }}, @js($user->name))" 
                                                class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1.5 rounded-lg transition">🗑️ Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="py-6 text-center text-gray-500">No KYC submissions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $kycSubmissions->links() }}
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div x-show="imageModal.open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4"
         @click.self="closeImage()"
         @keydown.escape.window="closeImage()">
        <div class="relative max-w-6xl max-h-full w-full h-full flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between bg-gray-900 text-white px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-montserrat font-bold" x-text="imageModal.title"></h3>
                <button @click="closeImage()" class="text-white hover:text-gray-300 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Image Container -->
            <div class="flex-1 overflow-auto bg-gray-800 flex items-center justify-center p-4">
                <img :src="imageModal.src" 
                     :alt="imageModal.title"
                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                     x-on:error="$el.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2218%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EImage not found%3C/text%3E%3C/svg%3E'">
            </div>
            
            <!-- Footer with Download Option -->
            <div class="bg-gray-900 text-white px-6 py-3 rounded-b-lg flex items-center justify-between">
                <span class="text-sm text-gray-400">Click outside or press ESC to close</span>
                <a :href="imageModal.src" 
                   download 
                   class="text-blue-400 hover:text-blue-300 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    </div>

    <!-- Edit KYC Modal -->
    <div x-show="editModal.open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
         @click.self="closeEditModal()"
         @keydown.escape.window="closeEditModal()">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky bg-gradient" style="top: 28px;"-to-r from-black to-secondary text-white px-6 py-4 rounded-t-xl flex items-center justify-between">
                <h3 class="text-xl font-montserrat font-bold">Edit KYC Submission</h3>
                <button @click="closeEditModal()" class="text-white hover:text-gray-300 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" :action="editModal.updateUrl" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" :value="editModal.name" disabled class="form-input bg-gray-100 cursor-not-allowed">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" :value="editModal.email" disabled class="form-input bg-gray-100 cursor-not-allowed">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type <span class="text-red-500">*</span></label>
                        <select name="kyc_id_type" x-model="editModal.idType" class="form-input" required>
                            <option value="">Select ID Type</option>
                            <option value="cnic">CNIC</option>
                            <option value="nicop">NICOP</option>
                            <option value="passport">Passport</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number <span class="text-red-500">*</span></label>
                        <input type="text" name="kyc_id_number" x-model="editModal.idNumber" class="form-input" required maxlength="50">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name (on ID) <span class="text-red-500">*</span></label>
                        <input type="text" name="kyc_full_name" x-model="editModal.fullName" class="form-input" required maxlength="255">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="kyc_status" x-model="editModal.status" class="form-input" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4" x-show="editModal.status === 'rejected'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                    <textarea 
                        name="rejection_reason" 
                        x-model="editModal.rejectionReason"
                        rows="4" 
                        class="form-input w-full" 
                        placeholder="Explain clearly why this KYC is being rejected so the user can fix it on the next attempt."
                        maxlength="1000"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <span x-text="(editModal.rejectionReason || '').length"></span>/1000 characters
                    </p>
                </div>
                
                <div class="mt-6 flex gap-3 justify-end">
                    <button type="button" @click="closeEditModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject KYC Modal -->
    <div x-show="rejectModal.open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
         @click.self="closeRejectModal()"
         @keydown.escape.window="closeRejectModal()">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-xl">
                <h3 class="text-xl font-montserrat font-bold">Reject KYC Submission</h3>
            </div>
            
            <form method="POST" :action="rejectModal.rejectUrl" @submit.prevent="submitReject($event)" class="p-6">
                @csrf
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to reject the KYC submission for <strong x-text="rejectModal.name"></strong>?
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="rejection_reason" 
                            x-model="rejectModal.reason"
                            rows="4" 
                            class="form-input w-full" 
                            placeholder="Please provide a clear reason for rejection so the user can fix the issue..."
                            required
                            maxlength="1000"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <span x-text="(rejectModal.reason || '').length"></span>/1000 characters
                        </p>
                    </div>
                    <p class="text-sm text-yellow-600 bg-yellow-50 p-3 rounded-lg">
                        ⚠️ The user will be notified with this rejection reason and can resubmit their KYC after fixing the issues.
                    </p>
                </div>
                
                <div class="mt-6 flex gap-3 justify-end">
                    <button type="button" @click="closeRejectModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                        Reject KYC
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
         @click.self="closeDeleteModal()"
         @keydown.escape.window="closeDeleteModal()">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-xl">
                <h3 class="text-xl font-montserrat font-bold">Delete KYC Submission</h3>
            </div>
            
            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    Are you sure you want to delete the KYC submission for <strong x-text="deleteModal.name"></strong>?
                </p>
                <p class="text-sm text-red-600 mb-6">
                    ⚠️ This action cannot be undone. All KYC documents and data will be permanently deleted.
                </p>
                
                <form method="POST" :action="deleteModal.deleteUrl" class="flex gap-3 justify-end">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="closeDeleteModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">Delete</button>
                </form>
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
function kycManagement() {
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
                const incoming = doc.querySelector('#adminKycTable');
                const current = document.querySelector('#adminKycTable');
                if (incoming && current) current.innerHTML = incoming.innerHTML;
                if (window.history && window.history.replaceState) window.history.replaceState({}, '', url);
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load KYC submissions. Please try again.', 'error');
            } finally {
                this.isListLoading = false;
            }
        },
        clearFilters() {
            const form = document.querySelector('form[action="{{ route('admin.kyc.list') }}"]');
            if (!form) return;
            ['search', 'status', 'id_type'].forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = '';
            });
            this.submitFilters(form);
        },
        // Image viewer (existing)
        imageModal: {
            open: false,
            src: '',
            title: ''
        },
        openImage(src, title) {
            this.imageModal.src = src;
            this.imageModal.title = title;
            this.imageModal.open = true;
        },
        closeImage() {
            this.imageModal.open = false;
            this.imageModal.src = '';
            this.imageModal.title = '';
        },
        
        // Edit modal
        editModal: {
            open: false,
            userId: null,
            name: '',
            email: '',
            idType: '',
            idNumber: '',
            fullName: '',
            status: '',
            rejectionReason: '',
            updateUrl: ''
        },
        openEditModal(userId, name, email, idType, idNumber, fullName, status, rejectionReason) {
            this.editModal.userId = userId;
            this.editModal.name = name;
            this.editModal.email = email;
            this.editModal.idType = idType;
            this.editModal.idNumber = idNumber;
            this.editModal.fullName = fullName;
            this.editModal.status = status;
            this.editModal.rejectionReason = rejectionReason || '';
            this.editModal.updateUrl = `{{ url('/dashboard/admin/kyc') }}/${userId}/update`;
            this.editModal.open = true;
        },
        closeEditModal() {
            this.editModal.open = false;
            this.editModal.userId = null;
            this.editModal.name = '';
            this.editModal.email = '';
            this.editModal.idType = '';
            this.editModal.idNumber = '';
            this.editModal.fullName = '';
            this.editModal.status = '';
            this.editModal.rejectionReason = '';
            this.editModal.updateUrl = '';
        },
        
        // Reject modal
        rejectModal: {
            open: false,
            userId: null,
            name: '',
            reason: '',
            rejectUrl: ''
        },
        openRejectModal(userId, name) {
            this.rejectModal.userId = userId;
            this.rejectModal.name = name;
            this.rejectModal.reason = '';
            this.rejectModal.rejectUrl = `{{ url('/dashboard/admin/kyc') }}/${userId}/reject`;
            this.rejectModal.open = true;
        },
        closeRejectModal() {
            this.rejectModal.open = false;
            this.rejectModal.userId = null;
            this.rejectModal.name = '';
            this.rejectModal.reason = '';
            this.rejectModal.rejectUrl = '';
        },
        async submitReject(event) {
            const form = event.target;
            const formData = new FormData(form);
            
            // Validate reason
            const reason = formData.get('rejection_reason')?.trim();
            if (!reason || reason.length < 10) {
                this.showToast('Please provide a detailed rejection reason (at least 10 characters).', 'error');
                return;
            }
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                
                if (response.ok) {
                    this.closeRejectModal();
                    this.showToast('KYC rejected successfully. User has been notified.', 'success');
                    // Reload the page after a short delay to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    const data = await response.json().catch(() => ({}));
                    this.showToast(data.message || 'Failed to reject KYC. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Error rejecting KYC:', error);
                this.showToast('An error occurred. Please try again.', 'error');
            }
        },
        
        // Delete modal
        deleteModal: {
            open: false,
            userId: null,
            name: '',
            deleteUrl: ''
        },
        openDeleteModal(userId, name) {
            this.deleteModal.userId = userId;
            this.deleteModal.name = name;
            this.deleteModal.deleteUrl = `{{ url('/dashboard/admin/kyc') }}/${userId}/delete`;
            this.deleteModal.open = true;
        },
        closeDeleteModal() {
            this.deleteModal.open = false;
            this.deleteModal.userId = null;
            this.deleteModal.name = '';
            this.deleteModal.deleteUrl = '';
        }
    }
}
</script>
@endpush
@endsection

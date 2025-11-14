@extends('layouts.app')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('userManagement', () => ({
        deleteModalOpen: false,
        deleteUserId: null,
        deleteUserName: '',
        deleteUserEmail: '',
        deleteFormAction: '',
        viewDetailsModalOpen: false,
        viewDetailsLoading: false,
        viewDetailsData: null,
        editModalOpen: false,
        imageModal: {
            open: false,
            src: '',
            title: ''
        },
        editUserId: null,
        editUserName: '',
        editUserEmail: '',
        editUserPhone: '',
        editUserRole: '',
        editFormAction: '',
        resetPasswordModalOpen: false,
        resetPasswordUserId: null,
        resetPasswordUserName: '',
        resetPasswordUserEmail: '',
        resetPasswordFormAction: '',
        createUserModalOpen: false,
        openEditModal(userId, userName, userEmail, userPhone, userRole) {
            this.editUserId = userId;
            this.editUserName = userName;
            this.editUserEmail = userEmail;
            this.editUserPhone = userPhone || '';
            this.editUserRole = userRole;
            this.editFormAction = '{{ url("/dashboard/admin/users") }}/' + userId;
            this.editModalOpen = true;
        },
        openResetPasswordModal(userId, userName, userEmail) {
            this.resetPasswordUserId = userId;
            this.resetPasswordUserName = userName;
            this.resetPasswordUserEmail = userEmail;
            this.resetPasswordFormAction = '{{ url("/dashboard/admin/users") }}/' + userId + '/reset-password';
            this.resetPasswordModalOpen = true;
        },
        openDeleteModal(userId, userName, userEmail) {
            this.deleteUserId = userId;
            this.deleteUserName = userName;
            this.deleteUserEmail = userEmail;
            this.deleteFormAction = '{{ url("/dashboard/admin/users") }}/' + userId;
            this.deleteModalOpen = true;
        },
        async openViewDetailsModal(userId) {
            this.viewDetailsModalOpen = true;
            this.viewDetailsLoading = true;
            this.viewDetailsData = null;
            
            try {
                const response = await fetch('{{ url("/dashboard/admin/users") }}/' + userId + '/details');
                const data = await response.json();
                this.viewDetailsData = data;
            } catch (error) {
                console.error('Error fetching user details:', error)
                alert('Failed to load user details. Please try again.');
            } finally {
                this.viewDetailsLoading = false;
            }
        },
        getUserName() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.name) || '';
        },
        getUserEmail() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.email) || '';
        },
        getUserPhone() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.phone) || '—';
        },
        getUserRole() {
            if (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.role) {
                const role = this.viewDetailsData.user.role;
                return role.charAt(0).toUpperCase() + role.slice(1);
            }
            return '—';
        },
        getUserCreatedAt() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.created_at) || '—';
        },
        getUserEmailVerified() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.email_verified_at) || 'Not verified';
        },
        getTokenBalance() {
            const balance = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.token_balance) || 0;
            return balance.toLocaleString();
        },
        getTokenValue() {
            const balance = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.token_balance) || 0;
            const value = balance * 0.70;
            return value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },
        getWalletAddress() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.wallet_address) || 'Not set';
        },
        getTransactionCount() {
            const count = (this.viewDetailsData && this.viewDetailsData.transactions && this.viewDetailsData.transactions.length) || 0;
            return '(' + count + ' transactions)';
        },
        hasTransactions() {
            return this.viewDetailsData && this.viewDetailsData.transactions && this.viewDetailsData.transactions.length > 0;
        },
        getTransactions() {
            return (this.viewDetailsData && this.viewDetailsData.transactions) || [];
        },
        formatTransactionType(type) {
            if (!type) return '—';
            return type.replace('_', ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        },
        formatTransactionAmount(amount) {
            return (amount || 0).toLocaleString() + ' RWAMP';
        },
        getTransactionStatusClass(status) {
            if (status === 'completed') return 'bg-green-100 text-green-800';
            if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
            if (status === 'failed') return 'bg-red-100 text-red-800';
            return '';
        },
        formatTransactionStatus(status) {
            if (!status) return '—';
            return status.charAt(0).toUpperCase() + status.slice(1);
        },
        getKycStatus() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_status) || 'not_started';
        },
        getKycStatusBadge() {
            const status = this.getKycStatus();
            if (status === 'approved') return 'bg-green-100 text-green-800';
            if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
            if (status === 'rejected') return 'bg-red-100 text-red-800';
            return 'bg-gray-100 text-gray-800';
        },
        getKycStatusText() {
            const status = this.getKycStatus();
            if (status === 'approved') return 'Approved';
            if (status === 'pending') return 'Pending';
            if (status === 'rejected') return 'Rejected';
            return 'Not Started';
        },
        getKycIdType() {
            const type = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_type) || '';
            if (!type) return '—';
            return type.toUpperCase();
        },
        getKycIdNumber() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_number) || '—';
        },
        getKycFullName() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_full_name) || '—';
        },
        getKycSubmittedAt() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_submitted_at) || '—';
        },
        getKycApprovedAt() {
            return (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_approved_at) || '—';
        },
        hasKycDocuments() {
            const user = this.viewDetailsData && this.viewDetailsData.user;
            return user && (user.kyc_id_front_path || user.kyc_id_back_path || user.kyc_selfie_path);
        },
        hasKycFront() {
            return this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_front_path;
        },
        hasKycBack() {
            return this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_id_back_path;
        },
        hasKycSelfie() {
            return this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.kyc_selfie_path;
        },
        getKycFrontUrl() {
            const userId = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.id) || null;
            if (!userId) return '#';
            const baseUrl = '{{ url("/admin/kyc") }}';
            return baseUrl + '/' + userId + '/download/front';
        },
        getKycBackUrl() {
            const userId = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.id) || null;
            if (!userId) return '#';
            const baseUrl = '{{ url("/admin/kyc") }}';
            return baseUrl + '/' + userId + '/download/back';
        },
        getKycSelfieUrl() {
            const userId = (this.viewDetailsData && this.viewDetailsData.user && this.viewDetailsData.user.id) || null;
            if (!userId) return '#';
            const baseUrl = '{{ url("/admin/kyc") }}';
            return baseUrl + '/' + userId + '/download/selfie';
        },
        openKycImage(url, title) {
            if (!url || url === '#') {
                alert('Invalid image URL. Please try again.');
                return;
            }
            // Set the image source and title
            this.imageModal.src = url;
            this.imageModal.title = title || 'KYC Document';
            // Open the modal
            this.imageModal.open = true;
            // Force a re-render by triggering Alpine
            this.$nextTick(() => {
                // Ensure the image loads
                const img = document.querySelector('[x-show="imageModal.open"] img');
                if (img) {
                    img.src = url;
                }
            });
        },
        closeKycImage() {
            this.imageModal.open = false;
            this.imageModal.src = '';
            this.imageModal.title = '';
        },
        closeViewDetailsModal() {
            this.viewDetailsModalOpen = false;
            this.viewDetailsData = null;
        }
    }));
});
</script>

<div class="min-h-screen bg-white" x-data="userManagement">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">User Management</h1>
                    <p class="text-white/80">Search, filter, and manage all users.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        @click="createUserModalOpen = true"
                        class="btn-primary flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create New User
                    </button>
                    <a href="{{ route('dashboard.admin') }}" class="btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-xl shadow p-4 grid md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Search</label>
                <input name="q" value="{{ request('q') }}" placeholder="Name, Email or Phone" class="rw-input w-full" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Role</label>
                <select name="role" class="rw-input w-full">
                    <option value="">All</option>
                    <option value="investor" @selected(request('role')==='investor')>Investor</option>
                    <option value="reseller" @selected(request('role')==='reseller')>Reseller</option>
                    <option value="admin" @selected(request('role')==='admin')>Admin</option>
                    <option value="user" @selected(request('role')==='user')>User</option>
                </select>
            </div>
            <div class="flex items-end">
                <button class="btn-primary w-full">Apply</button>
            </div>
            <div class="md:col-span-4 flex items-center gap-3">
                <label class="text-sm text-gray-600">Sort by:</label>
                @php
                    $qs = request()->except(['sort', 'dir', 'page']);
                @endphp
                <a href="{{ request()->fullUrlWithQuery(array_merge($qs, ['sort' => 'created_at', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="rw-badge">Date</a>
                <a href="{{ request()->fullUrlWithQuery(array_merge($qs, ['sort' => 'name', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="rw-badge">Name</a>
                <a href="{{ request()->fullUrlWithQuery(array_merge($qs, ['sort' => 'email', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="rw-badge">Email</a>
                <a href="{{ request()->fullUrlWithQuery(array_merge($qs, ['sort' => 'role', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="rw-badge">Role</a>
            </div>
        </form>

        <!-- Users table -->
        <div class="bg-white rounded-xl shadow px-4 py-6">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 border-b">
                            <th class="py-3 pr-6">Name</th>
                            <th class="py-3 pr-6">Email</th>
                            <th class="py-3 pr-6">Phone</th>
                            <th class="py-3 pr-6">Role</th>
                            <th class="py-3 pr-6">Reseller</th>
                            <th class="py-3 pr-6">Created</th>
                            <th class="py-3 pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr class="border-b">
                            <td class="py-3 pr-6">{{ $u->name }}</td>
                            <td class="py-3 pr-6">{{ $u->email }}</td>
                            <td class="py-3 pr-6">{{ $u->phone ?? '—' }}</td>
                            <td class="py-3 pr-6">
                                <span class="rw-badge">{{ ucfirst($u->role ?? 'user') }}</span>
                            </td>
                            <td class="py-3 pr-6">
                                @if($u->reseller)
                                    <div class="text-xs">
                                        <div class="font-semibold text-blue-600">{{ $u->reseller->name }}</div>
                                        @if($u->reseller->referral_code)
                                            <div class="text-gray-500">{{ $u->reseller->referral_code }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 pr-6">{{ $u->created_at?->format('Y-m-d') }}</td>
                            <td class="py-3 pr-6">
                                <div class="flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button 
                                        @click="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ addslashes($u->phone ?? '') }}', '{{ $u->role }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                    
                                    <!-- Reset Password Button -->
                                    <button 
                                        @click="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                        Reset
                                    </button>
                                    
                                    <!-- View Details Button -->
                                    <button 
                                        @click="openViewDetailsModal({{ $u->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </button>
                                    
                                    <!-- Delete Button -->
                                    <button 
                                        @click="openDeleteModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-6 text-center text-gray-500">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="editModalOpen" 
         x-cloak
         @keydown.escape.window="editModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="editModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Edit User</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Update user account information</p>
                                </div>
                            </div>
                            <button @click="editModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">📝 Note:</span> Update user information below. All changes will be saved immediately and the user will be notified of any modifications to their account.
                        </p>
                    </div>

                    <!-- User Info Display -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Current User Information
                        </p>
                        <div class="grid md:grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Name</p>
                                <p class="text-sm font-medium text-gray-900" x-text="editUserName"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Email</p>
                                <p class="text-sm font-medium text-gray-900 break-all" x-text="editUserEmail"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div x-show="editUserId">
                        <form method="POST" x-bind:action="editFormAction" class="space-y-5">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        name="name" 
                                        x-model="editUserName" 
                                        class="rw-input w-full" 
                                        placeholder="Enter full name" 
                                        required 
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">The user's complete legal name</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        name="email" 
                                        type="email" 
                                        x-model="editUserEmail" 
                                        class="rw-input w-full" 
                                        placeholder="user@example.com" 
                                        required 
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">Valid email address for account access</p>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input 
                                    name="phone" 
                                    x-model="editUserPhone" 
                                    class="rw-input w-full" 
                                    placeholder="+1234567890" 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">Contact number (optional)</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    User Role <span class="text-red-500">*</span>
                                </label>
                                <select name="role" x-model="editUserRole" class="rw-input w-full" required>
                                    <option value="user">User - Standard account access</option>
                                    <option value="investor">Investor - Investment features enabled</option>
                                    <option value="reseller">Reseller - Reseller program access</option>
                                    <option value="admin">Admin - Full system access</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1.5">Select the appropriate role for this user</p>
                            </div>

                            <!-- Guidelines Box -->
                            <div class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-gray-900">Important Guidelines</p>
                                        <ul class="mt-2 text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                            <li>Email changes require verification from the user</li>
                                            <li>Role changes affect user permissions immediately</li>
                                            <li>All changes are logged for security purposes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-300">
                                <button 
                                    @click="editModalOpen = false"
                                    type="button"
                                    class="btn-secondary px-6 py-2.5 text-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Cancel
                                    </span>
                                </button>
                                <button 
                                    type="submit" 
                                    class="btn-primary px-6 py-2.5 text-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Save Changes
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="resetPasswordModalOpen" 
         x-cloak
         @keydown.escape.window="resetPasswordModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="resetPasswordModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="resetPasswordModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="resetPasswordModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Reset Password</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Change user account password</p>
                                </div>
                            </div>
                            <button @click="resetPasswordModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">🔐 Note:</span> Reset the password for this user account. You can set a custom password or use the default password. The user will be required to change their password on their next login for security.
                        </p>
                    </div>

                    <!-- User Info Display -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            User Account
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="resetPasswordUserName"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="resetPasswordUserEmail"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div x-show="resetPasswordUserId">
                        <form method="POST" x-bind:action="resetPasswordFormAction" class="space-y-5">
                            @csrf
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    New Password
                                </label>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    class="rw-input w-full" 
                                    placeholder="Enter custom password (min 8 characters)" 
                                    minlength="8"
                                />
                                <div class="mt-2 space-y-1">
                                    <p class="text-xs text-gray-500">
                                        <span class="font-medium">Option 1:</span> Enter a custom password (minimum 8 characters)
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <span class="font-medium">Option 2:</span> Leave empty to use default password: 
                                        <code class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">RWAMP@agent</code>
                                    </p>
                                </div>
                            </div>

                            <!-- Guidelines Box -->
                            <div class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-gray-900">What happens next?</p>
                                        <ul class="mt-2 text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                            <li>The user's password will be reset immediately</li>
                                            <li>An email notification will be sent to the user</li>
                                            <li>User must change password on next login (mandatory)</li>
                                            <li>Previous password will be invalidated</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Notice -->
                            <div class="bg-primary/5 border-2 border-primary/30 p-3 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-xs text-gray-800 font-medium">
                                        <span class="font-bold text-primary">Security Note:</span> Always use strong passwords. If using default password, ensure the user changes it immediately upon login.
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-300">
                                <button 
                                    @click="resetPasswordModalOpen = false"
                                    type="button"
                                    class="btn-secondary px-6 py-2.5 text-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Cancel
                                    </span>
                                </button>
                                <button 
                                    type="submit" 
                                    class="btn-primary px-6 py-2.5 text-sm">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                        Reset Password
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" 
         x-cloak
         @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="deleteModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-primary/20 to-primary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-primary/10"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/30">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/40 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Delete User</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Permanently remove user account</p>
                                </div>
                            </div>
                            <button @click="deleteModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">⚠️ Warning:</span> You are about to permanently delete this user account from the system. This action will remove all user data, including account information, transaction history, and associated records.
                        </p>
                    </div>

                    <!-- User Info Display -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            User to be Deleted
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="deleteUserName"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="deleteUserEmail"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Critical Warning Box -->
                    <div class="mb-6 bg-primary/10 border-l-4 border-primary p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-bold text-primary mb-2">⚠️ This action cannot be undone!</p>
                                <p class="text-xs text-gray-800 mb-3 font-medium">Deleting this user will permanently remove:</p>
                                <ul class="text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                    <li>User account and profile information</li>
                                    <li>All transaction and activity history</li>
                                    <li>Associated data and records</li>
                                    <li>Access to the system</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Guidelines Box -->
                    <div class="bg-accent/10 border-2 border-accent/30 p-4 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-accent flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-gray-900 mb-1">Before deleting, consider:</p>
                                <ul class="text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                    <li>Is this user account truly inactive or unnecessary?</li>
                                    <li>Have you backed up any important data?</li>
                                    <li>Are there any pending transactions or obligations?</li>
                                    <li>Would deactivating the account be a better option?</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t-2 border-gray-300">
                    <button 
                        @click="deleteModalOpen = false"
                        type="button"
                        class="btn-secondary px-6 py-2.5 text-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </span>
                    </button>
                    <form method="POST" x-bind:action="deleteFormAction" class="inline" x-show="deleteUserId">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit"
                            :disabled="!deleteUserId"
                            class="btn-primary px-6 py-2.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete User Permanently
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Details Modal -->
    <div x-show="viewDetailsModalOpen" 
         x-cloak
         @keydown.escape.window="closeViewDetailsModal()"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="viewDetailsModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeViewDetailsModal()"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="viewDetailsModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">User Details</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Balance, wallet, and transaction history</p>
                                </div>
                            </div>
                            <button @click="closeViewDetailsModal()" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white max-h-[70vh] overflow-y-auto">
                    <!-- Loading State -->
                    <div x-show="viewDetailsLoading" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                        <p class="mt-4 text-gray-600">Loading user details...</p>
                    </div>

                    <!-- User Details Content -->
                    <div x-show="!viewDetailsLoading && viewDetailsData" class="space-y-6">
                        <!-- User Information -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Account Information
                            </h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserName()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="getUserEmail()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Phone</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserPhone()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Role</p>
                                    <span class="rw-badge" x-text="getUserRole()"></span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Account Created</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserCreatedAt()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Email Verified</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getUserEmailVerified()"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Balance & Wallet -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-black text-white rounded-lg p-6">
                                <p class="text-xs text-white/70 mb-2">Token Balance</p>
                                <p class="text-3xl font-bold" x-text="getTokenBalance()"></p>
                                <p class="text-sm text-white/70 mt-2">RWAMP Tokens</p>
                            </div>
                            <div class="bg-accent text-black rounded-lg p-6">
                                <p class="text-xs text-black/70 mb-2">Value (Rs)</p>
                                <p class="text-3xl font-bold" x-text="getTokenValue()"></p>
                                <p class="text-sm text-black/70 mt-2">@ Rs 0.70 per token</p>
                            </div>
                        </div>

                        <!-- Wallet Address -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Wallet Address
                            </h4>
                            <p class="font-mono text-sm break-all bg-white p-3 rounded border" x-text="getWalletAddress()"></p>
                        </div>

                        <!-- KYC Details -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                KYC Verification
                            </h4>
                            <div class="space-y-4">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">KYC Status</p>
                                        <span class="rw-badge" :class="getKycStatusBadge()" x-text="getKycStatusText()"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">ID Type</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycIdType()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">ID Number</p>
                                        <p class="text-sm font-semibold text-gray-900 font-mono" x-text="getKycIdNumber()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Full Name (on ID)</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycFullName()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Submitted At</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycSubmittedAt()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Approved At</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="getKycApprovedAt()"></p>
                                    </div>
                                </div>
                                
                                <!-- KYC Documents -->
                                <div x-show="hasKycDocuments()" class="mt-4 pt-4 border-t border-gray-300">
                                    <p class="text-xs text-gray-500 mb-3">KYC Documents</p>
                                    <div class="flex gap-3">
                                        <button 
                                            x-show="hasKycFront()"
                                            @click="openKycImage(getKycFrontUrl(), 'ID Front - ' + getUserName())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Front
                                        </button>
                                        <button 
                                            x-show="hasKycBack()"
                                            @click="openKycImage(getKycBackUrl(), 'ID Back - ' + getUserName())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Back
                                        </button>
                                        <button 
                                            x-show="hasKycSelfie()"
                                            @click="openKycImage(getKycSelfieUrl(), 'Selfie - ' + getUserName())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Selfie
                                        </button>
                                    </div>
                                </div>
                                
                                <div x-show="!hasKycDocuments()" class="text-center py-4 text-gray-500 text-sm">
                                    <p>No KYC documents available</p>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction History -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Transaction History
                                <span class="text-sm font-normal text-gray-600" x-text="getTransactionCount()"></span>
                            </h4>
                            
                            <div x-show="!hasTransactions()" class="text-center py-8 text-gray-500">
                                <p>No transactions found</p>
                            </div>
                            
                            <div x-show="hasTransactions()" class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-gray-600 border-b">
                                            <th class="py-2 pr-4">Date</th>
                                            <th class="py-2 pr-4">Type</th>
                                            <th class="py-2 pr-4">Amount</th>
                                            <th class="py-2 pr-4">Status</th>
                                            <th class="py-2 pr-4">Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="transaction in getTransactions()" :key="transaction.id">
                                            <tr class="border-b">
                                                <td class="py-2 pr-4" x-text="transaction.created_at"></td>
                                                <td class="py-2 pr-4">
                                                    <span class="rw-badge" x-text="formatTransactionType(transaction.type)"></span>
                                                </td>
                                                <td class="py-2 pr-4 font-semibold" x-text="formatTransactionAmount(transaction.amount)"></td>
                                                <td class="py-2 pr-4">
                                                    <span class="rw-badge" 
                                                          :class="getTransactionStatusClass(transaction.status)"
                                                          x-text="formatTransactionStatus(transaction.status)"></span>
                                                </td>
                                                <td class="py-2 pr-4 font-mono text-xs break-all" x-text="transaction.reference || '—'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end border-t-2 border-gray-300">
                    <button 
                        @click="closeViewDetailsModal()"
                        type="button"
                        class="btn-primary px-6 py-2.5 text-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Close
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Image Viewer Modal -->
    <div x-show="imageModal.open" 
         x-cloak
         style="display: none; z-index: 9999;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-95 p-4"
         @click.self="closeKycImage()"
         @keydown.escape.window="closeKycImage()">
        <div class="relative max-w-6xl max-h-full w-full h-full flex flex-col bg-gray-900 rounded-lg shadow-2xl overflow-hidden" style="z-index: 10000;">
            <!-- Header -->
            <div class="flex items-center justify-between bg-gray-900 text-white px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-montserrat font-bold" x-text="imageModal.title"></h3>
                <button 
                    @click="closeKycImage()" 
                    type="button"
                    class="text-white hover:text-red-400 hover:bg-red-500/20 transition-all duration-200 p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                    title="Close (ESC)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Image Container -->
            <div class="flex-1 overflow-auto bg-gray-800 flex items-center justify-center p-4">
                <img :src="imageModal.src" 
                     :alt="imageModal.title"
                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                     x-on:error="$el.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2218%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EImage not found%3C/text%3E%3C/svg%3E'"
                     loading="lazy">
            </div>
            
            <!-- Footer with Download Option -->
            <div class="bg-gray-900 text-white px-6 py-3 border-t border-gray-700 flex items-center justify-between">
                <span class="text-sm text-gray-400">Click outside or press ESC to close</span>
                <a :href="imageModal.src" 
                   target="_blank"
                   class="text-blue-400 hover:text-blue-300 text-sm font-medium flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    </div>

    <!-- Create New User Modal -->
    <div x-show="createUserModalOpen" 
         x-cloak
         @keydown.escape.window="createUserModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="createUserModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="createUserModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/70 backdrop-blur-sm"></div>

            <div x-show="createUserModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border-4 border-primary">
                
                <div class="bg-gradient-to-r from-black via-gray-900 to-secondary px-8 py-6 border-b-4 border-primary relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-xl ring-4 ring-primary/20">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div class="border-l-2 border-primary/30 pl-4">
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Create New User</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Add a new user account to the system</p>
                                </div>
                            </div>
                            <button @click="createUserModalOpen = false" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <!-- Description Section -->
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">➕ Note:</span> Fill in the form below to create a new user account. The user will receive login credentials via email and will be required to change their password on first login.
                        </p>
                    </div>

                    <!-- Form Section -->
                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                        @csrf
                        
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    name="name" 
                                    type="text"
                                    value="{{ old('name') }}"
                                    class="rw-input w-full" 
                                    placeholder="Enter full name" 
                                    required 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">The user's complete legal name</p>
                                @error('name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    name="email" 
                                    type="email" 
                                    value="{{ old('email') }}"
                                    class="rw-input w-full" 
                                    placeholder="user@example.com" 
                                    required 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">Valid email address for account access</p>
                                @error('email')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input 
                                    name="phone" 
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    class="rw-input w-full" 
                                    placeholder="+1234567890" 
                                />
                                <p class="text-xs text-gray-500 mt-1.5">Contact number (optional)</p>
                                @error('phone')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    User Role <span class="text-red-500">*</span>
                                </label>
                                <select name="role" class="rw-input w-full" required>
                                    <option value="">Select Role</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User - Standard account access</option>
                                    <option value="investor" {{ old('role') == 'investor' ? 'selected' : '' }}>Investor - Investment features enabled</option>
                                    <option value="reseller" {{ old('role') == 'reseller' ? 'selected' : '' }}>Reseller - Reseller program access</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin - Full system access</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1.5">Select the appropriate role for this user</p>
                                @error('role')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Password
                            </label>
                            <input 
                                name="password" 
                                type="password" 
                                class="rw-input w-full" 
                                placeholder="Enter password (min 8 characters)" 
                                minlength="8"
                            />
                            <div class="mt-2 space-y-1">
                                <p class="text-xs text-gray-500">
                                    <span class="font-medium">Option 1:</span> Enter a custom password (minimum 8 characters)
                                </p>
                                <p class="text-xs text-gray-500">
                                    <span class="font-medium">Option 2:</span> Leave empty to use default password: 
                                    <code class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">RWAMP@agent</code>
                                </p>
                            </div>
                            @error('password')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Guidelines Box -->
                        <div class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-bold text-gray-900">Important Information</p>
                                    <ul class="mt-2 text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                        <li>User will receive an email with login credentials</li>
                                        <li>User must change password on first login (mandatory)</li>
                                        <li>Email verification is required for account activation</li>
                                        <li>All user data will be logged for security purposes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-300">
                            <button 
                                @click="createUserModalOpen = false"
                                type="button"
                                class="btn-secondary px-6 py-2.5 text-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel
                                </span>
                            </button>
                            <button 
                                type="submit" 
                                class="btn-primary px-6 py-2.5 text-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create User
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



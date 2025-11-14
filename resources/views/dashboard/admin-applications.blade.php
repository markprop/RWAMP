@extends('layouts.app')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('applicationManagement', () => ({
        deleteModalOpen: false,
        deleteApplicationId: null,
        deleteApplicationName: '',
        deleteApplicationEmail: '',
        deleteFormAction: '',
        viewDetailsModalOpen: false,
        viewDetailsLoading: false,
        viewDetailsData: null,
        editModalOpen: false,
        editApplicationId: null,
        editApplicationName: '',
        editApplicationEmail: '',
        editApplicationPhone: '',
        editApplicationCompany: '',
        editApplicationCapacity: '',
        editApplicationMessage: '',
        editApplicationStatus: '',
        editFormAction: '',
        openEditModal(appId, name, email, phone, company, capacity, message, status) {
            this.editApplicationId = appId;
            this.editApplicationName = name;
            this.editApplicationEmail = email;
            this.editApplicationPhone = phone || '';
            this.editApplicationCompany = company || '';
            this.editApplicationCapacity = capacity || '';
            this.editApplicationMessage = message || '';
            this.editApplicationStatus = status || 'pending';
            this.editFormAction = '{{ url("/dashboard/admin/applications") }}/' + appId;
            this.editModalOpen = true;
        },
        openDeleteModal(appId, name, email) {
            this.deleteApplicationId = appId;
            this.deleteApplicationName = name;
            this.deleteApplicationEmail = email;
            this.deleteFormAction = '{{ url("/dashboard/admin/applications") }}/' + appId;
            this.deleteModalOpen = true;
        },
        async openViewDetailsModal(appId) {
            this.viewDetailsModalOpen = true;
            this.viewDetailsLoading = true;
            this.viewDetailsData = null;
            
            try {
                const response = await fetch('{{ url("/dashboard/admin/applications") }}/' + appId + '/details');
                const data = await response.json();
                this.viewDetailsData = data;
            } catch (error) {
                console.error('Error fetching application details:', error)
                alert('Failed to load application details. Please try again.');
            } finally {
                this.viewDetailsLoading = false;
            }
        },
        getApplicationName() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.name) || '';
        },
        getApplicationEmail() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.email) || '';
        },
        getApplicationPhone() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.phone) || '—';
        },
        getApplicationCompany() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.company) || '—';
        },
        getApplicationCapacity() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.investment_capacity_label) || '—';
        },
        getApplicationMessage() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.message) || 'No message provided';
        },
        getApplicationStatus() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.status) || 'pending';
        },
        getApplicationStatusBadge() {
            const status = this.getApplicationStatus();
            if (status === 'approved') return 'bg-green-100 text-green-800';
            if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
            if (status === 'rejected') return 'bg-red-100 text-red-800';
            return 'bg-gray-100 text-gray-800';
        },
        getApplicationStatusText() {
            const status = this.getApplicationStatus();
            if (status === 'approved') return 'Approved';
            if (status === 'pending') return 'Pending';
            if (status === 'rejected') return 'Rejected';
            return 'Unknown';
        },
        getApplicationIpAddress() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.ip_address) || '—';
        },
        getApplicationUserAgent() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.user_agent) || '—';
        },
        getApplicationCreatedAt() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.created_at) || '—';
        },
        getApplicationUpdatedAt() {
            return (this.viewDetailsData && this.viewDetailsData.application && this.viewDetailsData.application.updated_at) || '—';
        },
        closeViewDetailsModal() {
            this.viewDetailsModalOpen = false;
            this.viewDetailsData = null;
        },
        closeEditModal() {
            this.editModalOpen = false;
            this.editApplicationId = null;
            this.editApplicationName = '';
            this.editApplicationEmail = '';
            this.editApplicationPhone = '';
            this.editApplicationCompany = '';
            this.editApplicationCapacity = '';
            this.editApplicationMessage = '';
            this.editApplicationStatus = '';
            this.editFormAction = '';
        },
        closeDeleteModal() {
            this.deleteModalOpen = false;
            this.deleteApplicationId = null;
            this.deleteApplicationName = '';
            this.deleteApplicationEmail = '';
            this.deleteFormAction = '';
        }
    }));
});
</script>

<div class="min-h-screen bg-white" x-data="applicationManagement">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Reseller Applications</h1>
                    <p class="text-white/80">Search, filter, and manage all reseller applications.</p>
                </div>
                <a href="{{ route('dashboard.admin') }}" class="btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
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
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <form method="GET" action="{{ route('admin.applications') }}" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ request('q') }}" 
                        placeholder="Name, email, phone, or company"
                        class="form-input w-full"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-input w-full">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                    <select name="capacity" class="form-input w-full">
                        <option value="">All Capacities</option>
                        <option value="1-10k" {{ request('capacity') === '1-10k' ? 'selected' : '' }}>Rs 1,000 - Rs 10,000</option>
                        <option value="10-50k" {{ request('capacity') === '10-50k' ? 'selected' : '' }}>Rs 10,000 - Rs 50,000</option>
                        <option value="50-100k" {{ request('capacity') === '50-100k' ? 'selected' : '' }}>Rs 50,000 - Rs 100,000</option>
                        <option value="100k+" {{ request('capacity') === '100k+' ? 'selected' : '' }}>Rs 100,000+</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary flex-1">Filter</button>
                    <a href="{{ route('admin.applications') }}" class="btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-montserrat font-bold text-xl">Applications</h3>
                <span class="rw-badge">{{ $applications->total() }} total</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
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
                            <th class="py-3 pr-6">Phone</th>
                            <th class="py-3 pr-6">Company</th>
                            <th class="py-3 pr-6">Capacity</th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Status @if(request('sort') === 'status') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-primary">
                                    Created @if(request('sort') === 'created_at') {{ request('dir') === 'asc' ? '↑' : '↓' }} @endif
                                </a>
                            </th>
                            <th class="py-3 pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr class="border-b">
                            <td class="py-3 pr-6">{{ $app->name }}</td>
                            <td class="py-3 pr-6">{{ $app->email }}</td>
                            <td class="py-3 pr-6">{{ $app->phone ?? '—' }}</td>
                            <td class="py-3 pr-6">{{ $app->company ?? '—' }}</td>
                            <td class="py-3 pr-6">
                                <span class="rw-badge">{{ $app->investment_capacity_label }}</span>
                            </td>
                            <td class="py-3 pr-6">
                                @if($app->status === 'approved')
                                    <span class="rw-badge bg-green-100 text-green-800">Approved</span>
                                @elseif($app->status === 'rejected')
                                    <span class="rw-badge bg-red-100 text-red-800">Rejected</span>
                                @else
                                    <span class="rw-badge bg-yellow-100 text-yellow-800">Pending</span>
                                @endif
                            </td>
                            <td class="py-3 pr-6">{{ $app->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-3 pr-6 whitespace-nowrap">
                                <div class="flex gap-2 flex-wrap">
                                    <button 
                                        @click="openViewDetailsModal({{ $app->id }})"
                                        class="btn-secondary text-xs px-2 py-1">👁️ View</button>
                                    <button 
                                        @click="openEditModal({{ $app->id }}, @js($app->name), @js($app->email), @js($app->phone ?? ''), @js($app->company ?? ''), @js($app->investment_capacity), @js($app->message ?? ''), @js($app->status))"
                                        class="btn-secondary text-xs px-2 py-1">✏️ Edit</button>
                                    @if($app->status === 'pending')
                                        <form method="POST" action="{{ route('admin.applications.approve', $app) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs px-2 py-1 rounded transition">✅ Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.applications.reject', $app) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs px-2 py-1 rounded transition">❌ Reject</button>
                                        </form>
                                    @endif
                                    <button 
                                        @click="openDeleteModal({{ $app->id }}, @js($app->name), @js($app->email))"
                                        class="bg-red-600 hover:bg-red-700 text-white text-xs px-2 py-1 rounded transition">🗑️ Delete</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="py-6 text-center text-gray-500">No applications found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $applications->links() }}</div>
        </div>
    </div>

    <!-- View Application Details Modal -->
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
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Application Details</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Complete reseller application information</p>
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
                        <p class="mt-4 text-gray-600">Loading application details...</p>
                    </div>

                    <!-- Application Details Content -->
                    <div x-show="!viewDetailsLoading && viewDetailsData" class="space-y-6">
                        <!-- Contact Information -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Contact Information
                            </h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getApplicationName()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="getApplicationEmail()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Phone</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getApplicationPhone()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Company</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getApplicationCompany()"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Application Details -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Application Details
                            </h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Investment Capacity</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getApplicationCapacity()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Status</p>
                                    <span class="rw-badge" :class="getApplicationStatusBadge()" x-text="getApplicationStatusText()"></span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Submitted At</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getApplicationCreatedAt()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="getApplicationUpdatedAt()"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>
                                Message
                            </h4>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap bg-white p-4 rounded border" x-text="getApplicationMessage()"></p>
                        </div>

                        <!-- Technical Information -->
                        <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-300">
                            <h4 class="text-lg font-montserrat font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Technical Information
                            </h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">IP Address</p>
                                    <p class="text-sm font-semibold text-gray-900 font-mono" x-text="getApplicationIpAddress()"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">User Agent</p>
                                    <p class="text-sm font-semibold text-gray-900 text-xs break-all" x-text="getApplicationUserAgent()"></p>
                                </div>
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

    <!-- Edit Application Modal -->
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
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Edit Application</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Update reseller application information</p>
                                </div>
                            </div>
                            <button @click="closeEditModal()" class="text-white/90 hover:text-white transition-all duration-200 p-2.5 hover:bg-white/20 rounded-xl hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 bg-white">
                    <div x-show="editApplicationId">
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
                                        x-model="editApplicationName" 
                                        class="rw-input w-full" 
                                        placeholder="Enter full name" 
                                        required 
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        name="email" 
                                        type="email" 
                                        x-model="editApplicationEmail" 
                                        class="rw-input w-full" 
                                        placeholder="user@example.com" 
                                        required 
                                    />
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Phone Number
                                    </label>
                                    <input 
                                        name="phone" 
                                        x-model="editApplicationPhone" 
                                        class="rw-input w-full" 
                                        placeholder="+1234567890" 
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Company Name
                                    </label>
                                    <input 
                                        name="company" 
                                        x-model="editApplicationCompany" 
                                        class="rw-input w-full" 
                                        placeholder="Company name" 
                                    />
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Investment Capacity <span class="text-red-500">*</span>
                                    </label>
                                    <select name="investment_capacity" x-model="editApplicationCapacity" class="rw-input w-full" required>
                                        <option value="">Select Capacity</option>
                                        <option value="1-10k">Rs 1,000 - Rs 10,000</option>
                                        <option value="10-50k">Rs 10,000 - Rs 50,000</option>
                                        <option value="50-100k">Rs 50,000 - Rs 100,000</option>
                                        <option value="100k+">Rs 100,000+</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" x-model="editApplicationStatus" class="rw-input w-full" required>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Message
                                </label>
                                <textarea 
                                    name="message" 
                                    x-model="editApplicationMessage" 
                                    class="rw-input w-full" 
                                    rows="4"
                                    placeholder="Application message (optional)"
                                    maxlength="1000"
                                ></textarea>
                                <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters</p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-300">
                                <button 
                                    @click="closeEditModal()"
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
                                    <h3 class="text-3xl font-montserrat font-bold text-white tracking-tight">Delete Application</h3>
                                    <p class="text-sm text-white/90 mt-1 font-medium">Permanently remove application</p>
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
                    <div class="mb-6 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg">
                        <p class="text-sm text-gray-800 leading-relaxed font-medium">
                            <span class="text-primary font-bold">⚠️ Warning:</span> You are about to permanently delete this reseller application from the system. This action cannot be undone.
                        </p>
                    </div>

                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Application to be Deleted
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Name</p>
                                    <p class="text-sm font-semibold text-gray-900" x-text="deleteApplicationName"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="text-sm font-semibold text-gray-900 break-all" x-text="deleteApplicationEmail"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-accent/10 border-2 border-accent/30 p-4 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-accent flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-gray-900 mb-1">Before deleting, consider:</p>
                                <ul class="text-xs text-gray-700 list-disc list-inside space-y-1.5 font-medium">
                                    <li>Is this application truly unnecessary?</li>
                                    <li>Have you reviewed all application details?</li>
                                    <li>Would changing the status be a better option?</li>
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
                    <form method="POST" x-bind:action="deleteFormAction" class="inline" x-show="deleteApplicationId">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit"
                            :disabled="!deleteApplicationId"
                            class="btn-primary px-6 py-2.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Application Permanently
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

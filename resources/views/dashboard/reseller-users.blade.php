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
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">My Users</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Manage users who registered with your referral code</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6" x-data="resellerUsersFilters()">
            <!-- Search and Filters -->
            <div class="bg-white rounded-xl shadow-xl p-5 sm:p-6 mb-6 border border-gray-100">
                <form 
                    method="GET" 
                    action="{{ route('reseller.users') }}" 
                    class="grid grid-cols-1 sm:grid-cols-[minmax(0,2fr)_auto_auto] gap-3 sm:gap-4 items-stretch"
                    @submit.prevent="submit($event)"
                >
                    <div class="w-full">
                        <label for="users-search" class="sr-only">Search users</label>
                        <input 
                            id="users-search"
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search by name, email, or phone..." 
                            class="form-input w-full min-h-[44px]"
                            aria-label="Search users by name, email, or phone"
                        />
                    </div>
                    <button 
                        type="submit" 
                        class="btn-primary w-full sm:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold"
                        x-bind:disabled="isLoading"
                    >
                        <span x-show="!isLoading">Search</span>
                        <span x-show="isLoading">Searching…</span>
                    </button>
                    @if(request('search'))
                        <a 
                            href="{{ route('reseller.users') }}" 
                            class="btn-secondary w-full sm:w-auto min-h-[44px] flex items-center justify-center text-sm font-semibold text-center"
                        >
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Users Table -->
            <div id="resellerUsersTable" class="bg-white rounded-xl shadow-xl overflow-hidden animate-fadeInUp">
            @if($users->count() > 0)
                <div class="rw-table-scroll overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Name</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Email</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Phone</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Payments</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Transactions</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Joined</th>
                                <th class="text-left py-3 px-4 sm:px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 sm:px-6">{{ $user->name }}</td>
                                    <td class="py-3 px-4 sm:px-6 text-gray-600">{{ $user->email }}</td>
                                    <td class="py-3 px-4 sm:px-6 text-gray-600">{{ $user->phone ?? '—' }}</td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs sm:text-sm">
                                            {{ $user->crypto_payments_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs sm:text-sm">
                                            {{ $user->transactions_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 sm:px-6 text-gray-600 text-xs sm:text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4 sm:px-6">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('reseller.users.view', $user) }}" class="btn-primary btn-small">
                                                View
                                            </a>
                                            <button type="button" onclick="shareUserDetails({{ $user->id }})" class="btn-secondary btn-small">
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
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-10 sm:py-12 text-gray-500">
                    <p class="text-base sm:text-lg">No users found.</p>
                    <p class="text-xs sm:text-sm mt-2">Share your referral code to get started!</p>
                </div>
            @endif
            </div>

            <!-- Simple toast -->
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

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('resellerUsersFilters', () => ({
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

                if (!response.ok) {
                    throw new Error('Server returned ' + response.status);
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const incoming = doc.querySelector('#resellerUsersTable');
                const current = document.querySelector('#resellerUsersTable');

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
                this.showToast('Failed to search users. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        }
    }));
});

async function shareUserDetails(userId) {
    try {
        const response = await fetch(`/dashboard/reseller/users/${userId}`);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extract user details
        const name = doc.querySelector('[data-user-name]')?.textContent || '';
        const email = doc.querySelector('[data-user-email]')?.textContent || '';
        const phone = doc.querySelector('[data-user-phone]')?.textContent || '';
        const payments = doc.querySelector('[data-user-payments]')?.textContent || '';
        const transactions = doc.querySelector('[data-user-transactions]')?.textContent || '';
        const joined = doc.querySelector('[data-user-joined]')?.textContent || '';
        
        const details = `User Details:
Name: ${name}
Email: ${email}
Phone: ${phone}
Total Payments: ${payments}
Total Transactions: ${transactions}
Joined: ${joined}

Shared from RWAMP Reseller Dashboard`;

        await navigator.clipboard.writeText(details);
        alert('User details copied to clipboard!');
    } catch (error) {
        console.error('Error sharing:', error)
        alert('Failed to copy details. Please try again.');
    }
}
</script>
@endsection


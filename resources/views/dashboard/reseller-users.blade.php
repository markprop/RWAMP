@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">My Users</h1>
                    <p class="text-white/80">Manage users who registered with your referral code</p>
                </div>
                <a href="{{ route('dashboard.reseller') }}" class="btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Search and Filters -->
        <div class="bg-white rounded-xl shadow-xl p-6 mb-6">
            <form method="GET" action="{{ route('reseller.users') }}" class="flex gap-4 flex-wrap">
                <div class="flex-1 min-w-[250px]">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search by name, email, or phone..." 
                        class="form-input w-full"
                    />
                </div>
                <button type="submit" class="btn-primary">Search</button>
                @if(request('search'))
                    <a href="{{ route('reseller.users') }}" class="btn-secondary">Clear</a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Name</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Email</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Phone</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Payments</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Transactions</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Joined</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-4 px-6">{{ $user->name }}</td>
                                    <td class="py-4 px-6 text-gray-600">{{ $user->email }}</td>
                                    <td class="py-4 px-6 text-gray-600">{{ $user->phone ?? '—' }}</td>
                                    <td class="py-4 px-6">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                            {{ $user->crypto_payments_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                            {{ $user->transactions_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-2">
                                            <a href="{{ route('reseller.users.view', $user) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                View
                                            </a>
                                            <button onclick="shareUserDetails({{ $user->id }})" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
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
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg">No users found.</p>
                    <p class="text-sm mt-2">Share your referral code to get started!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
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


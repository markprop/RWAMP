@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="adminChatFilters()">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">All Chats (Admin View)</h1>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.chats.index') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search chats..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                </div>

                <!-- Type Filter -->
                <div>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="private" {{ request('type') === 'private' ? 'selected' : '' }}>Private</option>
                        <option value="group" {{ request('type') === 'group' ? 'selected' : '' }}>Group</option>
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Users</option>
                        @foreach(\App\Models\User::whereIn('role', ['investor', 'reseller'])->orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-4">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="show_deleted_only" 
                        value="1"
                        {{ request('show_deleted_only') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-primary focus:ring-primary"
                    >
                    <span class="ml-2 text-sm text-gray-700">Show chats with deleted messages only</span>
                </label>

                <button type="submit" class="btn-primary px-6 py-2">
                    Apply Filters
                </button>

                @if(request()->anyFilled(['search', 'type', 'user_id', 'show_deleted_only']))
                    <a href="{{ route('admin.chats.index') }}" class="text-gray-600 hover:text-gray-800 text-sm">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>

        <!-- Chat List -->
        <div class="space-y-2">
            @forelse($chats as $chat)
                <a 
                    href="{{ route('admin.chat.view', $chat) }}"
                    class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold mr-3">
                                {{ strtoupper(substr($chat->display_name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-semibold">{{ $chat->display_name }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ $chat->type === 'group' ? 'Group Chat' : 'Private Chat' }}
                                    • {{ $chat->participants->count() }} participants
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">
                                {{ $chat->last_message_at ? $chat->last_message_at->diffForHumans() : 'No messages' }}
                            </p>
                            @if($chat->messages_count > 0)
                                <span class="text-xs bg-primary text-white px-2 py-1 rounded">
                                    {{ $chat->messages_count }} messages
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-center text-gray-500 py-8">No chats found</p>
            @endforelse
        </div>

        {{ $chats->links() }}
    </div>
</div>

@push('scripts')
<script>
function adminChatFilters() {
    return {
        // Filter logic handled by form submission
    }
}
</script>
@endpush
@endsection


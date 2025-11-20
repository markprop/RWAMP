@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100" x-data="chatIndex()">
    <div class="flex h-screen">
        <!-- Left Sidebar: Chat List -->
        <div class="w-full md:w-1/3 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="bg-primary text-white p-4 flex items-center justify-between">
                <h1 class="text-xl font-bold">Chats</h1>
                <button @click="showNewChatModal = true" class="p-2 hover:bg-white/20 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>

            <!-- Search -->
            <div class="p-3 border-b border-gray-200">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input.debounce.300ms="searchChats()"
                    placeholder="Search chats..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                >
            </div>

            <!-- Chat List -->
            <div class="flex-1 overflow-y-auto">
                <template x-for="chat in filteredChats" :key="chat.id">
                    <a 
                        :href="'/chat/' + chat.id"
                        class="flex items-center p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer"
                        :class="{ 'bg-primary/10': activeChatId === chat.id }"
                    >
                        <img 
                            :src="getChatAvatar(chat)" 
                            :alt="chat.display_name"
                            class="w-12 h-12 rounded-full object-cover mr-3"
                        >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 truncate" x-text="chat.display_name"></h3>
                                <span class="text-xs text-gray-500" x-text="formatTime(chat.last_message_at)"></span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-sm text-gray-600 truncate" x-text="getLastMessage(chat)"></p>
                                <span 
                                    x-show="chat.unread_count > 0"
                                    class="bg-primary text-white text-xs rounded-full px-2 py-1"
                                    x-text="chat.unread_count"
                                ></span>
                            </div>
                        </div>
                    </a>
                </template>
                <div x-show="filteredChats.length === 0" class="p-8 text-center text-gray-500">
                    No chats found. Start a new conversation!
                </div>
            </div>
        </div>

        <!-- Right Panel: Welcome/Empty State -->
        <div class="hidden md:flex flex-1 items-center justify-center bg-gray-50">
            <div class="text-center">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">Select a chat to start messaging</h2>
                <p class="text-gray-500">Or create a new conversation</p>
            </div>
        </div>
    </div>

    <!-- New Chat Modal -->
    <div 
        x-show="showNewChatModal" 
        @click.away="showNewChatModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        style="display: none;"
    >
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold mb-4">New Chat</h3>
            
            <!-- Search Users -->
            <input 
                type="text" 
                x-model="userSearchQuery"
                @input.debounce.300ms="searchUsers()"
                placeholder="Search users by name or email..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4 focus:ring-2 focus:ring-primary focus:border-transparent"
            >
            <p class="text-xs text-gray-500 mb-4">Type at least 2 characters to search</p>

            <!-- User List -->
            <div class="max-h-64 overflow-y-auto">
                <template x-for="user in searchResults" :key="user.id">
                    <button 
                        @click="createPrivateChat(user.id)"
                        class="w-full flex items-center p-3 hover:bg-gray-50 rounded-lg mb-2 text-left"
                    >
                        <img 
                            :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=E30613&color=fff'" 
                            :alt="user.name" 
                            class="w-10 h-10 rounded-full mr-3 object-cover"
                            onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(this.alt) + '&background=E30613&color=fff'"
                        >
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate" x-text="user.name"></p>
                            <p class="text-sm text-gray-500 capitalize" x-text="user.role || 'user'"></p>
                        </div>
                    </button>
                </template>
                <div x-show="userSearchQuery.length >= 2 && searchResults.length === 0" class="p-4 text-center text-gray-500 text-sm">
                    No users found
                </div>
            </div>

            <button 
                @click="showNewChatModal = false"
                class="mt-4 w-full bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300"
            >
                Cancel
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function chatIndex() {
    return {
        chats: @json($chats),
        filteredChats: [],
        searchQuery: '',
        showNewChatModal: false,
        userSearchQuery: '',
        searchResults: [],
        activeChatId: null,

        init() {
            this.filteredChats = this.chats;
        },

        searchChats() {
            if (!this.searchQuery) {
                this.filteredChats = this.chats;
                return;
            }
            const query = this.searchQuery.toLowerCase();
            this.filteredChats = this.chats.filter(chat => 
                chat.display_name && chat.display_name.toLowerCase().includes(query)
            );
        },

        async searchUsers() {
            if (this.userSearchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            try {
                const response = await fetch('/api/chat/search-users?q=' + encodeURIComponent(this.userSearchQuery), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    console.error('Search response error:', response.status);
                    this.searchResults = [];
                    return;
                }
                
                const data = await response.json();
                this.searchResults = data.users || [];
            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = [];
            }
        },

        async createPrivateChat(userId) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    alert('Security token missing. Please refresh the page.');
                    return;
                }

                const response = await fetch('/chat/create-private', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ user_id: userId })
                });

                // Check if response is OK
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Response error:', response.status, text);
                    try {
                        const errorData = JSON.parse(text);
                        alert(errorData.message || 'Failed to create chat. Please try again.');
                    } catch (e) {
                        alert('Failed to create chat. Please try again.');
                    }
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    this.showNewChatModal = false;
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Failed to create chat. Please try again.');
                }
            } catch (error) {
                console.error('Create chat error:', error);
                alert('An error occurred. Please try again.');
            }
        },

        getChatAvatar(chat) {
            if (chat.type === 'group') {
                return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(chat.name) + '&background=E30613&color=fff';
            }
            const otherParticipant = chat.participants?.find(p => p.id !== @json(auth()->id()));
            return otherParticipant?.avatar_url || 'https://ui-avatars.com/api/?name=User&background=E30613&color=fff';
        },

        getLastMessage(chat) {
            if (chat.latest_message) {
                if (chat.latest_message.media_type === 'receipt') return '📄 Payment Receipt';
                if (chat.latest_message.media_type === 'location') return '📍 Location';
                if (chat.latest_message.media_type === 'voice') return '🎤 Voice Message';
                return chat.latest_message.content || '';
            }
            return 'No messages yet';
        },

        formatTime(timestamp) {
            if (!timestamp) return '';
            try {
                const date = new Date(timestamp);
                if (isNaN(date.getTime())) return '';
                const now = new Date();
                const diff = now - date;
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                
                if (days === 0) {
                    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                } else if (days === 1) {
                    return 'Yesterday';
                } else if (days < 7) {
                    return days + 'd ago';
                } else {
                    return date.toLocaleDateString();
                }
            } catch (e) {
                return '';
            }
        }
    }
}
</script>
@endpush
@endsection


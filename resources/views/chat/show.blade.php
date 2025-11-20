@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100" x-data="chatShowData()">
    <div class="flex h-screen">
        <!-- Left Sidebar: Chat List -->
        <div class="w-full md:w-1/3 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="bg-primary text-white p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="goToDashboard()" class="p-2 hover:bg-white/20 rounded" title="Back to Dashboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold">Chats</h1>
                </div>
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
                        @click.prevent="loadChat(chat.id)"
                        href="#"
                        class="flex items-center p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer"
                        :class="{ 'bg-primary/10': currentChatId === chat.id }"
                    >
                        <img 
                            :src="getChatAvatar(chat)" 
                            :alt="chat.display_name"
                            class="w-12 h-12 rounded-full object-cover mr-3"
                            @error="$el.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(chat.display_name) + '&background=E30613&color=fff'"
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
                                    class="ml-2 bg-primary text-white text-xs rounded-full px-2 py-1"
                                    x-text="chat.unread_count"
                                ></span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        <!-- Right Panel: Chat Conversation -->
        <div class="hidden md:flex flex-1 flex-col bg-white" x-show="currentChatId">
            <!-- Chat Header -->
            <div class="bg-primary text-white p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="closeChat()" class="p-2 hover:bg-white/20 rounded" title="Close Chat">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <img 
                        :src="getCurrentChatAvatar()" 
                        :alt="currentChat ? currentChat.display_name : ''"
                        class="w-10 h-10 rounded-full mr-3 object-cover"
                        @error="$el.src='https://ui-avatars.com/api/?name=User&background=E30613&color=fff'"
                    >
                    <div>
                        <h2 class="font-bold" x-text="currentChat ? currentChat.display_name : 'Chat'"></h2>
                        <p class="text-sm text-white/80" x-text="getParticipantStatus()"></p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="showPayModal = true" class="p-2 hover:bg-white/20 rounded" title="Pay Offline">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                    <button @click="showInfo = !showInfo" class="p-2 hover:bg-white/20 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div 
                class="flex-1 overflow-y-auto p-4 space-y-4"
                x-ref="messagesContainer"
            >
                <template x-for="message in messages" :key="message.id">
                    <div 
                        class="flex chat-message"
                        :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
                    >
                        <div 
                            class="max-w-xs md:max-w-md px-4 py-2 rounded-lg"
                            :class="message.sender_id === currentUserId 
                                ? 'bg-primary text-white' 
                                : 'bg-gray-200 text-gray-900'"
                        >
                            <div x-show="message.is_deleted" class="text-xs opacity-75 italic">
                                This message was deleted
                            </div>
                            
                            <div x-show="!message.is_deleted">
                                <div x-show="message.media_type === 'receipt'" class="mb-2">
                                    <img :src="message.media_url" alt="Receipt" class="rounded max-w-full">
                                    <p class="text-sm mt-1">📄 Payment Receipt</p>
                                </div>

                                <div x-show="message.media_type === 'location'" class="mb-2">
                                    <div class="bg-gray-100 rounded p-2">
                                        <p class="text-sm">📍 Location Shared</p>
                                        <a 
                                            :href="(message.location_data && message.location_data.lat) ? ('https://maps.google.com/?q=' + message.location_data.lat + ',' + message.location_data.lng) : '#'"
                                            target="_blank"
                                            class="text-xs underline"
                                        >
                                            View on Map
                                        </a>
                                    </div>
                                </div>

                                <div x-show="message.media_type === 'voice'" class="mb-2">
                                    <div class="flex items-center space-x-2 bg-gray-100 rounded-lg p-3">
                                        <svg class="w-6 h-6 text-primary flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                        </svg>
                                        <audio controls class="flex-1" preload="metadata">
                                            <source :src="message.media_url" type="audio/webm">
                                            <source :src="message.media_url" type="audio/mpeg">
                                            Your browser does not support audio playback.
                                        </audio>
                                    </div>
                                </div>

                                <div x-show="message.media_type === 'image'" class="mb-2">
                                    <img :src="message.media_url" alt="Image" class="rounded max-w-full">
                                </div>

                                <p x-show="message.content" x-text="message.content" class="whitespace-pre-wrap"></p>
                                
                                <div class="text-xs mt-1 opacity-75" 
                                    :class="message.sender_id === currentUserId ? 'text-right' : 'text-left'"
                                >
                                    <span x-text="formatTime(message.created_at)"></span>
                                    <span x-show="message.sender_id === currentUserId && message.is_read" class="ml-1">✓✓</span>
                                </div>
                                
                                <div x-show="!message.is_deleted" class="flex space-x-1 mt-2">
                                    <template x-for="emoji in ['👍', '👎', '❤️']" :key="emoji">
                                        <button 
                                            @click="reactToMessage(message.id, emoji)"
                                            class="text-xs px-2 py-1 rounded transition-colors"
                                            :class="message.reaction === emoji 
                                                ? 'bg-primary/20 text-primary' 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                            x-text="emoji"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Message Input -->
            <div class="border-t border-gray-200 p-4 bg-white chat-input-container relative">
                <div x-show="selectedFile" class="mb-2 p-2 bg-gray-100 rounded">
                    <div class="flex items-center justify-between">
                        <span x-text="selectedFile.name"></span>
                        <button @click="selectedFile = null" class="text-red-500">×</button>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button 
                        @click="startRecording()" 
                        x-show="!isRecording" 
                        class="p-2 text-gray-600 hover:text-primary transition-colors"
                        title="Record voice message"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </button>
                    <div x-show="isRecording" class="flex items-center text-red-500 px-2">
                        <span class="text-sm">🎙️ Recording...</span>
                        <button @click="stopRecording()" class="ml-2 text-sm font-semibold">⏹️ Stop</button>
                    </div>

                    <button 
                        @click="showAttachmentMenu = !showAttachmentMenu" 
                        x-show="!isRecording"
                        class="p-2 text-gray-600 hover:text-primary transition-colors"
                        title="Attach file"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                    </button>

                    <input 
                        type="text" 
                        x-model="messageContent"
                        @keydown.enter.prevent="sendMessage()"
                        placeholder="Type a message..." 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent chat-input"
                        :disabled="isRecording"
                    >

                    <button 
                        @click="sendMessage()"
                        :disabled="(!messageContent && !selectedFile) || isRecording"
                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        Send
                    </button>
                </div>

                <div 
                    x-show="showAttachmentMenu"
                    @click.away="showAttachmentMenu = false"
                    class="absolute bottom-full left-0 right-0 mb-2 bg-white border border-gray-200 rounded-lg shadow-lg p-2 chat-attachment-menu"
                    style="display: none;"
                >
                    <label class="block px-4 py-2 hover:bg-gray-100 rounded cursor-pointer">
                        <input type="file" @change="handleFileSelect($event)" accept="image/*" class="hidden">
                        📷 Photo
                    </label>
                    <label class="block px-4 py-2 hover:bg-gray-100 rounded cursor-pointer">
                        <input type="file" @change="handleFileSelect($event)" accept=".pdf,.doc,.docx" class="hidden">
                        📄 Document
                    </label>
                    <button @click="shareLocation()" class="block w-full text-left px-4 py-2 hover:bg-gray-100 rounded">
                        📍 Location
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div class="hidden md:flex flex-1 items-center justify-center bg-gray-50" x-show="!currentChatId">
            <div class="text-center">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-gray-600 text-lg">Select a chat to start messaging</p>
            </div>
        </div>
    </div>

    <!-- Pay Offline Modal -->
    <div 
        x-show="showPayModal"
        @click.away="showPayModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        style="display: none;"
    >
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold mb-4">Pay Offline</h3>
            <p class="text-gray-600 mb-4">Upload your payment receipt</p>
            
            <label class="block mb-4">
                <input 
                    type="file" 
                    @change="handleReceiptUpload($event)"
                    accept="image/*"
                    class="hidden"
                >
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-primary">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-gray-600">Click to upload receipt</p>
                </div>
            </label>

            <div class="flex space-x-2">
                <button 
                    @click="showPayModal = false"
                    class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300"
                >
                    Cancel
                </button>
                <button 
                    @click="submitReceipt()"
                    :disabled="!receiptFile"
                    class="flex-1 bg-primary text-white py-2 rounded-lg hover:bg-primary/90 disabled:opacity-50"
                >
                    Upload
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function chatShowData() {
    console.group('[Chat] Initializing chatShowData function');
    
    const chatData = {
        allChats: @json($allChats ?? []),
        currentChat: @json($currentChat ?? null),
        messages: @json($messages ?? []),
        participants: @json($participants ?? []),
        currentUserId: {{ auth()->id() ?? 0 }},
        currentChatId: @if(isset($currentChat) && isset($currentChat['id'])){{ $currentChat['id'] }}@else null @endif,
        
        searchQuery: '',
        filteredChats: [],
        messageContent: '',
        selectedFile: null,
        receiptFile: null,
        showAttachmentMenu: false,
        showPayModal: false,
        showInfo: false,
        showNewChatModal: false,
        isRecording: false,
        mediaRecorder: null,
        audioChunks: [],
        recordingStream: null,
        echoChannel: null,

        init() {
            console.group('[Chat] init() called');
            console.log('[Chat] Initial data:', {
                allChatsCount: this.allChats.length,
                currentChat: this.currentChat,
                messagesCount: this.messages.length,
                participantsCount: this.participants.length,
                currentUserId: this.currentUserId,
                currentChatId: this.currentChatId
            });
            
            try {
                this.filteredChats = this.allChats;
                console.log('[Chat] Filtered chats set:', this.filteredChats.length);
                
                if (this.currentChatId) {
                    console.log('[Chat] Current chat ID found:', this.currentChatId);
                    this.setupRealtime();
                    this.scrollToBottom();
                } else {
                    console.log('[Chat] No current chat ID, skipping realtime setup');
                }
                } catch (error) {
                    console.error('[Chat] Error in init():', error);
                    console.error('[Chat] Error stack:', error.stack);
                } finally {
                    console.groupEnd();
                }
            },

        closeChat() {
            console.log('[Chat] closeChat() called');
            const oldChatId = this.currentChatId;
            this.currentChatId = null;
            this.currentChat = null;
            this.messages = [];
            
            if (this.echoChannel && oldChatId) {
                try {
                    console.log('[Chat] Leaving Echo channel:', 'chat.' + oldChatId);
                    window.Echo.leave('chat.' + oldChatId);
                    this.echoChannel = null;
                    console.log('[Chat] Successfully left channel');
                } catch (e) {
                    console.error('[Chat] Error leaving channel:', e);
                }
            }
            console.log('[Chat] Chat closed successfully');
        },

        goToDashboard() {
            console.log('[Chat] goToDashboard() called');
            window.location.href = '{{ route("dashboard") }}';
        },

        async loadChat(chatId) {
            console.log('[Chat] loadChat() called with chatId:', chatId);
            
            if (!chatId) {
                console.error('[Chat] loadChat() called with invalid chatId');
                return;
            }
            
            try {
                // Leave previous channel if exists
                if (this.echoChannel && this.currentChatId) {
                    console.log('[Chat] Leaving previous channel:', 'chat.' + this.currentChatId);
                    try {
                        window.Echo.leave('chat.' + this.currentChatId);
                    } catch (e) {
                        console.warn('[Chat] Error leaving previous channel:', e);
                    }
                    this.echoChannel = null;
                }
                
                this.currentChatId = chatId;
                this.currentChat = this.allChats.find(c => c.id === chatId);
                console.log('[Chat] Found chat in list:', this.currentChat);
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('[Chat] CSRF token not found!');
                    return;
                }
                
                console.log('[Chat] Fetching chat data from:', '/chat/' + chatId);
                const response = await fetch('/chat/' + chatId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken.content
                    }
                });
                
                console.log('[Chat] Response status:', response.status, response.statusText);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('[Chat] Received data:', {
                        hasMessages: !!data.messages,
                        messagesCount: data.messages?.length || 0,
                        hasParticipants: !!data.participants,
                        participantsCount: data.participants?.length || 0,
                        chat: data.chat
                    });
                    
                    if (data.messages) {
                        this.messages = data.messages;
                        console.log('[Chat] Messages loaded:', this.messages.length);
                    }
                    if (data.participants) {
                        this.participants = data.participants;
                        console.log('[Chat] Participants loaded:', this.participants.length);
                    }
                    if (data.chat) {
                        this.currentChat = data.chat;
                        console.log('[Chat] Chat data updated');
                    }
                } else {
                    console.error('[Chat] Failed to load chat. Status:', response.status);
                    const text = await response.text();
                    console.error('[Chat] Response text:', text.substring(0, 500));
                    
                    // Try to parse as HTML fallback
                    try {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(text, 'text/html');
                        const script = doc.querySelector('script');
                        if (script && script.textContent.includes('chatShowData')) {
                            const match = script.textContent.match(/currentChatId:\s*(\d+|null)/);
                            if (match) {
                                this.currentChatId = match[1] === 'null' ? null : parseInt(match[1]);
                                console.log('[Chat] Extracted chatId from HTML:', this.currentChatId);
                            }
                        }
                    } catch (parseError) {
                        console.error('[Chat] Error parsing HTML response:', parseError);
                    }
                }
            } catch (error) {
                console.error('[Chat] Error in loadChat():', error);
                console.error('[Chat] Error stack:', error.stack);
            }
            
            console.log('[Chat] Setting up realtime for chat:', this.currentChatId);
            this.setupRealtime();
            this.scrollToBottom();
            console.log('[Chat] loadChat() completed');
        },

        setupRealtime() {
            console.log('[Chat] setupRealtime() called');
            console.log('[Chat] Echo available:', !!window.Echo);
            console.log('[Chat] Current chat ID:', this.currentChatId);
            
            if (!window.Echo) {
                console.error('[Chat] window.Echo is not available! Make sure Pusher is configured.');
                return;
            }
            
            if (!this.currentChatId) {
                console.warn('[Chat] No currentChatId, skipping realtime setup');
                return;
            }
            
            try {
                const channelName = 'chat.' + this.currentChatId;
                console.log('[Chat] Setting up channel:', channelName);
                
                if (this.echoChannel) {
                    console.log('[Chat] Existing channel found, leaving first');
                    try {
                        window.Echo.leave(channelName);
                        console.log('[Chat] Left existing channel');
                    } catch (e) {
                        console.warn('[Chat] Error leaving existing channel:', e);
                    }
                    this.echoChannel = null;
                }
                
                console.log('[Chat] Joining private channel:', channelName);
                this.echoChannel = window.Echo.private(channelName)
                    .listen('.message.sent', (e) => {
                        console.log('[Chat] Received real-time message:', e);
                        
                        if (!e || !e.id) {
                            console.warn('[Chat] Invalid message event received:', e);
                            return;
                        }
                        
                        if (this.messages.find(m => m.id === e.id)) {
                            console.log('[Chat] Message already exists, skipping:', e.id);
                            return;
                        }
                        
                        const newMessage = {
                            id: e.id,
                            chat_id: e.chat_id,
                            sender_id: e.sender_id,
                            sender: e.sender || null,
                            content: e.content || null,
                            media_type: e.media_type || null,
                            media_path: e.media_path || null,
                            media_url: e.media_url || null,
                            media_name: e.media_name || null,
                            location_data: e.location_data || null,
                            is_deleted: e.is_deleted || false,
                            reaction: e.reaction || null,
                            created_at: e.created_at || new Date().toISOString()
                        };
                        
                        console.log('[Chat] Adding new message to array:', newMessage);
                        this.messages.push(newMessage);
                        console.log('[Chat] Total messages now:', this.messages.length);
                        
                        this.scrollToBottom();
                        
                        if (e.sender_id && e.sender_id !== this.currentUserId) {
                            console.log('[Chat] Marking message as read:', e.id);
                            this.markAsRead(e.id);
                        }
                    })
                    .error((error) => {
                        console.error('[Chat] Echo channel error:', error);
                        console.error('[Chat] Error details:', JSON.stringify(error, null, 2));
                    });
                
                console.log('[Chat] Realtime setup completed successfully');
            } catch (error) {
                console.error('[Chat] Error in setupRealtime():', error);
                console.error('[Chat] Error stack:', error.stack);
            }
        },

        searchChats() {
            console.log('[Chat] searchChats() called, query:', this.searchQuery);
            if (!this.searchQuery) {
                this.filteredChats = this.allChats;
                console.log('[Chat] No search query, showing all chats:', this.filteredChats.length);
                return;
            }
            const query = this.searchQuery.toLowerCase();
            this.filteredChats = this.allChats.filter(chat => 
                chat.display_name && chat.display_name.toLowerCase().includes(query)
            );
            console.log('[Chat] Filtered chats:', this.filteredChats.length);
        },

        getChatAvatar(chat) {
            if (!chat) return 'https://ui-avatars.com/api/?name=User&background=E30613&color=fff';
            if (chat.type === 'group') {
                return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(chat.display_name || 'Group') + '&background=E30613&color=fff';
            }
            const otherParticipant = this.participants.find(p => p.id !== this.currentUserId);
            return otherParticipant?.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(chat.display_name || 'User') + '&background=E30613&color=fff';
        },

        getCurrentChatAvatar() {
            if (!this.currentChat) return 'https://ui-avatars.com/api/?name=User&background=E30613&color=fff';
            if (this.currentChat.type === 'group') {
                return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(this.currentChat.display_name || 'Group') + '&background=E30613&color=fff';
            }
            const otherParticipant = this.participants.find(p => p.id !== this.currentUserId);
            return otherParticipant?.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(this.currentChat.display_name || 'User') + '&background=E30613&color=fff';
        },

        getParticipantStatus() {
            if (!this.currentChat) return '';
            if (this.currentChat.type === 'group') {
                return (this.participants.length || 0) + ' participants';
            }
            const otherParticipant = this.participants.find(p => p.id !== this.currentUserId);
            return otherParticipant?.status || 'offline';
        },

        getLastMessage(chat) {
            if (!chat || !chat.latest_message) return 'No messages yet';
            if (chat.latest_message.media_type) {
                const types = {
                    'image': '📷 Photo',
                    'file': '📄 File',
                    'voice': '🎤 Voice',
                    'location': '📍 Location',
                    'receipt': '📄 Receipt'
                };
                return types[chat.latest_message.media_type] || 'Media';
            }
            return chat.latest_message.content || 'No messages yet';
        },

        formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            
            if (days === 0) {
                return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            } else if (days === 1) {
                return 'Yesterday';
            } else if (days < 7) {
                return date.toLocaleDateString('en-US', { weekday: 'short' });
            } else {
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }
        },

        async sendMessage() {
            console.log('[Chat] sendMessage() called');
            console.log('[Chat] Message content:', this.messageContent);
            console.log('[Chat] Selected file:', this.selectedFile);
            console.log('[Chat] Current chat ID:', this.currentChatId);
            
            if (!this.messageContent && !this.selectedFile) {
                console.warn('[Chat] No content or file to send');
                return;
            }
            
            if (!this.currentChatId) {
                console.error('[Chat] No currentChatId, cannot send message');
                alert('Please select a chat first');
                return;
            }

            const formData = new FormData();
            if (this.messageContent) {
                formData.append('content', this.messageContent);
                console.log('[Chat] Added content to formData');
            }
            if (this.selectedFile) {
                formData.append('file', this.selectedFile);
                const mediaType = this.selectedFile.type.startsWith('image/') ? 'image' : 'file';
                formData.append('media_type', mediaType);
                console.log('[Chat] Added file to formData, type:', mediaType);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('[Chat] CSRF token not found!');
                alert('Security token missing. Please refresh the page.');
                return;
            }

            try {
                const url = '/chat/' + this.currentChatId + '/message';
                console.log('[Chat] Sending POST request to:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                console.log('[Chat] Response status:', response.status, response.statusText);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('[Chat] Response error:', errorText);
                    throw new Error('Server returned error: ' + response.status);
                }
                
                const data = await response.json();
                console.log('[Chat] Response data:', data);
                
                if (data.success) {
                    console.log('[Chat] Message sent successfully:', data.message);
                    this.messages.push(data.message);
                    this.messageContent = '';
                    this.selectedFile = null;
                    console.log('[Chat] Message added to array, total:', this.messages.length);
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                } else {
                    console.error('[Chat] Server returned success=false:', data);
                    alert(data.message || 'Failed to send message');
                }
            } catch (error) {
                console.error('[Chat] Error in sendMessage():', error);
                console.error('[Chat] Error stack:', error.stack);
                alert('Failed to send message. Please try again.');
            }
        },

        handleFileSelect(event) {
            console.log('[Chat] handleFileSelect() called');
            if (event.target.files && event.target.files[0]) {
                this.selectedFile = event.target.files[0];
                console.log('[Chat] File selected:', {
                    name: this.selectedFile.name,
                    size: this.selectedFile.size,
                    type: this.selectedFile.type
                });
            } else {
                console.warn('[Chat] No file selected');
            }
            this.showAttachmentMenu = false;
        },

        handleReceiptUpload(event) {
            console.log('[Chat] handleReceiptUpload() called');
            if (event.target.files && event.target.files[0]) {
                this.receiptFile = event.target.files[0];
                console.log('[Chat] Receipt file selected:', {
                    name: this.receiptFile.name,
                    size: this.receiptFile.size,
                    type: this.receiptFile.type
                });
            } else {
                console.warn('[Chat] No receipt file selected');
            }
        },

        async submitReceipt() {
            console.log('[Chat] submitReceipt() called');
            console.log('[Chat] Receipt file:', this.receiptFile);
            console.log('[Chat] Current chat ID:', this.currentChatId);
            
            if (!this.receiptFile) {
                console.warn('[Chat] No receipt file selected');
                return;
            }
            
            if (!this.currentChatId) {
                console.error('[Chat] No currentChatId, cannot upload receipt');
                return;
            }

            const formData = new FormData();
            formData.append('receipt', this.receiptFile);
            console.log('[Chat] FormData created with receipt file');

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('[Chat] CSRF token not found!');
                alert('Security token missing. Please refresh the page.');
                return;
            }

            try {
                const url = '/chat/' + this.currentChatId + '/receipt';
                console.log('[Chat] Uploading receipt to:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                console.log('[Chat] Receipt upload response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('[Chat] Receipt upload error response:', errorText);
                    throw new Error('Server returned error: ' + response.status);
                }
                
                const data = await response.json();
                console.log('[Chat] Receipt upload response data:', data);
                
                if (data.success) {
                    console.log('[Chat] Receipt uploaded successfully:', data.message);
                    this.messages.push(data.message);
                    this.receiptFile = null;
                    this.showPayModal = false;
                    console.log('[Chat] Receipt message added to array');
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                } else {
                    console.error('[Chat] Server returned success=false:', data);
                    alert(data.message || 'Failed to upload receipt');
                }
            } catch (error) {
                console.error('[Chat] Error in submitReceipt():', error);
                console.error('[Chat] Error stack:', error.stack);
                alert('Failed to upload receipt. Please try again.');
            }
        },

        shareLocation() {
            console.log('[Chat] shareLocation() called');
            console.log('[Chat] Geolocation available:', !!navigator.geolocation);
            console.log('[Chat] Current chat ID:', this.currentChatId);
            
            if (!navigator.geolocation) {
                console.error('[Chat] Geolocation not supported');
                alert('Geolocation is not supported by your browser');
                return;
            }
            
            if (!this.currentChatId) {
                console.error('[Chat] No currentChatId, cannot share location');
                return;
            }
            
            console.log('[Chat] Requesting geolocation...');
            navigator.geolocation.getCurrentPosition(async (position) => {
                console.log('[Chat] Geolocation obtained:', {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
                
                const locationData = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                const formData = new FormData();
                formData.append('content', 'Location shared');
                formData.append('media_type', 'location');
                formData.append('location_data', JSON.stringify(locationData));
                console.log('[Chat] FormData created with location');

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('[Chat] CSRF token not found!');
                    return;
                }

                try {
                    const url = '/chat/' + this.currentChatId + '/message';
                    console.log('[Chat] Sending location to:', url);
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    console.log('[Chat] Location share response status:', response.status);
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('[Chat] Location share error response:', errorText);
                        throw new Error('Server returned error: ' + response.status);
                    }
                    
                    const data = await response.json();
                    console.log('[Chat] Location share response data:', data);
                    
                    if (data.success) {
                        console.log('[Chat] Location shared successfully:', data.message);
                        this.messages.push(data.message);
                        this.showAttachmentMenu = false;
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    } else {
                        console.error('[Chat] Server returned success=false:', data);
                    }
                } catch (error) {
                    console.error('[Chat] Error in shareLocation():', error);
                    console.error('[Chat] Error stack:', error.stack);
                }
            }, (error) => {
                console.error('[Chat] Geolocation error:', error);
                console.error('[Chat] Error code:', error.code);
                console.error('[Chat] Error message:', error.message);
                alert('Failed to get location: ' + error.message);
            });
        },

        async startRecording() {
            console.log('[Chat] startRecording() called');
            console.log('[Chat] Current chat ID:', this.currentChatId);
            
            if (!this.currentChatId) {
                console.error('[Chat] No currentChatId, cannot start recording');
                alert('Please select a chat first');
                return;
            }
            
            try {
                console.log('[Chat] Requesting microphone access...');
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log('[Chat] Microphone access granted');
                
                this.recordingStream = stream;
                this.isRecording = true;
                this.audioChunks = [];
                
                const mimeType = 'audio/webm;codecs=opus';
                console.log('[Chat] Creating MediaRecorder with mimeType:', mimeType);
                
                this.mediaRecorder = new MediaRecorder(stream, {
                    mimeType: mimeType
                });
                
                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        console.log('[Chat] Audio chunk received, size:', event.data.size);
                        this.audioChunks.push(event.data);
                    }
                };
                
                this.mediaRecorder.onstop = () => {
                    console.log('[Chat] Recording stopped, total chunks:', this.audioChunks.length);
                    this.uploadVoice();
                    if (this.recordingStream) {
                        this.recordingStream.getTracks().forEach(track => track.stop());
                        this.recordingStream = null;
                        console.log('[Chat] Recording stream stopped');
                    }
                };
                
                console.log('[Chat] Starting MediaRecorder...');
                this.mediaRecorder.start();
                console.log('[Chat] Recording started successfully');
            } catch (error) {
                console.error('[Chat] Error starting recording:', error);
                console.error('[Chat] Error name:', error.name);
                console.error('[Chat] Error message:', error.message);
                alert('Could not access microphone. Please check permissions.');
                this.isRecording = false;
            }
        },

        stopRecording() {
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
                this.isRecording = false;
            }
        },

        async uploadVoice() {
            console.log('[Chat] uploadVoice() called');
            console.log('[Chat] Audio chunks:', this.audioChunks.length);
            console.log('[Chat] Current chat ID:', this.currentChatId);
            
            if (this.audioChunks.length === 0) {
                console.warn('[Chat] No audio chunks to upload');
                this.audioChunks = [];
                return;
            }
            
            if (!this.currentChatId) {
                console.error('[Chat] No currentChatId, cannot upload voice');
                this.audioChunks = [];
                return;
            }

            try {
                const blob = new Blob(this.audioChunks, { type: 'audio/webm' });
                console.log('[Chat] Created blob, size:', blob.size, 'bytes');
                
                const formData = new FormData();
                const fileName = 'voice_' + Date.now() + '.webm';
                formData.append('file', blob, fileName);
                console.log('[Chat] FormData created with file:', fileName);

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('[Chat] CSRF token not found!');
                    throw new Error('CSRF token missing');
                }

                const url = '/chat/' + this.currentChatId + '/voice';
                console.log('[Chat] Uploading voice to:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                console.log('[Chat] Voice upload response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('[Chat] Voice upload error response:', errorText);
                    throw new Error('Server returned error: ' + response.status);
                }

                const data = await response.json();
                console.log('[Chat] Voice upload response data:', data);
                
                if (data.success) {
                    console.log('[Chat] Voice message uploaded successfully:', data.message);
                    this.messages.push(data.message);
                    console.log('[Chat] Voice message added to array');
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                } else {
                    console.error('[Chat] Server returned success=false:', data);
                    alert(data.message || 'Failed to upload voice message');
                }
            } catch (error) {
                console.error('[Chat] Error in uploadVoice():', error);
                console.error('[Chat] Error stack:', error.stack);
                alert('Failed to upload voice message. Please try again.');
            } finally {
                this.audioChunks = [];
                console.log('[Chat] Audio chunks cleared');
            }
        },

        async reactToMessage(messageId, emoji) {
            console.log('[Chat] reactToMessage() called:', { messageId, emoji });
            console.log('[Chat] Current chat ID:', this.currentChatId);
            
            if (!this.currentChatId) {
                console.error('[Chat] No currentChatId, cannot react to message');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('[Chat] CSRF token not found!');
                return;
            }
            
            try {
                const url = '/chat/' + this.currentChatId + '/message/' + messageId + '/react';
                console.log('[Chat] Sending reaction to:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ emoji })
                });

                console.log('[Chat] Reaction response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('[Chat] Reaction error response:', errorText);
                    throw new Error('Server returned error: ' + response.status);
                }

                const data = await response.json();
                console.log('[Chat] Reaction response data:', data);
                
                if (data.success) {
                    const index = this.messages.findIndex(m => m.id === messageId);
                    if (index !== -1) {
                        console.log('[Chat] Updating message reaction at index:', index);
                        this.messages[index].reaction = emoji;
                    } else {
                        console.warn('[Chat] Message not found in array:', messageId);
                    }
                } else {
                    console.error('[Chat] Server returned success=false:', data);
                }
            } catch (error) {
                console.error('[Chat] Error in reactToMessage():', error);
                console.error('[Chat] Error stack:', error.stack);
            }
        },

        async markAsRead(messageId) {
            console.log('[Chat] markAsRead() called for message:', messageId);
            
            if (!this.currentChatId) {
                console.warn('[Chat] No currentChatId, skipping mark as read');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('[Chat] CSRF token not found!');
                return;
            }
            
            try {
                const url = '/chat/' + this.currentChatId + '/message/' + messageId + '/read';
                console.log('[Chat] Marking message as read:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json'
                    }
                });
                
                console.log('[Chat] Mark as read response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.warn('[Chat] Mark as read error response:', errorText);
                } else {
                    console.log('[Chat] Message marked as read successfully');
                }
            } catch (error) {
                console.error('[Chat] Error in markAsRead():', error);
                console.error('[Chat] Error stack:', error.stack);
            }
        },

        scrollToBottom() {
            console.log('[Chat] scrollToBottom() called');
            this.$nextTick(() => {
                setTimeout(() => {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        const oldScroll = container.scrollTop;
                        container.scrollTop = container.scrollHeight;
                        console.log('[Chat] Scrolled from', oldScroll, 'to', container.scrollTop, '(max:', container.scrollHeight, ')');
                    } else {
                        console.warn('[Chat] Messages container not found');
                    }
                }, 100);
            });
        }
    };
    
    console.log('[Chat] chatShowData function defined, returning data object');
    console.groupEnd();
    return chatData;
}

console.log('[Chat] chatShowData function loaded');
</script>
@endpush
@endsection

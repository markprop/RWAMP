<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Display chat index page
     */
    /**
     * Prepare chat data for frontend (shared between index and show)
     */
    private function prepareChatData($user, $currentChat = null)
    {
        try {
            $allChats = $this->chatService->getUserChats($user->id);

            // Format all chats for sidebar
            $formattedChats = $allChats->map(function($chat) use ($user) {
                try {
                    // Get display name safely
                    $displayName = 'Chat';
                    if ($chat->type === 'group' && $chat->name) {
                        $displayName = $chat->name;
                    } elseif ($chat->type === 'private') {
                        // Query ChatParticipant directly to avoid relationship/column conflict
                        $otherParticipantIds = ChatParticipant::where('chat_id', $chat->id)
                            ->where('user_id', '!=', $user->id)
                            ->pluck('user_id');
                        
                        if ($otherParticipantIds->isNotEmpty()) {
                            $other = User::find($otherParticipantIds->first());
                            $displayName = $other ? $other->name : 'Unknown User';
                        } else {
                            $displayName = 'Unknown User';
                        }
                    } elseif ($chat->name) {
                        $displayName = $chat->name;
                    }

                    // Format latest message safely
                    $latestMessage = null;
                    if ($chat->relationLoaded('latestMessage') && $chat->latestMessage) {
                        $latestMessage = [
                            'content' => $chat->latestMessage->content ?? null,
                            'media_type' => $chat->latestMessage->media_type ?? null,
                            'sender' => null,
                        ];
                        
                        // Load sender if not already loaded
                        if ($chat->latestMessage->relationLoaded('sender') && $chat->latestMessage->sender) {
                            $latestMessage['sender'] = [
                                'id' => $chat->latestMessage->sender->id,
                                'name' => $chat->latestMessage->sender->name,
                            ];
                        } elseif ($chat->latestMessage->sender_id) {
                            // Fallback: get sender name from ID if relationship not loaded
                            $sender = \App\Models\User::find($chat->latestMessage->sender_id);
                            if ($sender) {
                                $latestMessage['sender'] = [
                                    'id' => $sender->id,
                                    'name' => $sender->name,
                                ];
                            }
                        }
                    }

                    return [
                        'id' => $chat->id,
                        'type' => $chat->type ?? 'private',
                        'name' => $chat->name ?? null,
                        'display_name' => $displayName,
                        'last_message_at' => $chat->last_message_at ? $chat->last_message_at->toIso8601String() : null,
                        'unread_count' => $chat->unread_count ?? 0,
                        'latest_message' => $latestMessage,
                        'participants' => $this->formatParticipants($chat),
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error formatting chat: ' . $e->getMessage(), [
                        'chat_id' => $chat->id ?? null,
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                }
            })->filter()->values();

            // Prepare current chat data if provided
            $currentChatData = null;
            $messages = collect([]);
            $participants = [];

            if ($currentChat) {
                // Format current chat
                $chatDisplayName = 'Chat';
                if ($currentChat->type === 'group' && $currentChat->name) {
                    $chatDisplayName = $currentChat->name;
                } elseif ($currentChat->type === 'private') {
                    $otherParticipantIds = ChatParticipant::where('chat_id', $currentChat->id)
                        ->where('user_id', '!=', $user->id)
                        ->pluck('user_id');
                    
                    if ($otherParticipantIds->isNotEmpty()) {
                        $other = User::find($otherParticipantIds->first());
                        $chatDisplayName = $other ? $other->name : 'Unknown User';
                    }
                }

                $currentChatData = [
                    'id' => $currentChat->id,
                    'type' => $currentChat->type ?? 'private',
                    'name' => $currentChat->name ?? null,
                    'display_name' => $chatDisplayName,
                    'last_message_at' => $currentChat->last_message_at ? $currentChat->last_message_at->toIso8601String() : null,
                ];

                // Format messages
                $messages = $currentChat->messages()
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function($message) {
                        return [
                            'id' => $message->id,
                            'chat_id' => $message->chat_id,
                            'sender_id' => $message->sender_id,
                            'sender' => $message->sender ? [
                                'id' => $message->sender->id,
                                'name' => $message->sender->name,
                                'avatar_url' => $message->sender->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->name ?? 'User') . '&background=E30613&color=fff',
                            ] : null,
                            'content' => $message->content,
                            'media_type' => $message->media_type,
                            'media_path' => $message->media_path,
                            'media_url' => $message->media_url,
                            'media_name' => $message->media_name,
                            'location_data' => $message->location_data,
                            'is_deleted' => $message->is_deleted,
                            'reaction' => $message->reaction,
                            'created_at' => $message->created_at->toIso8601String(),
                        ];
                    });

                // Format participants
                $participants = $this->formatParticipants($currentChat);
            }

            return [
                'allChats' => $formattedChats,
                'currentChat' => $currentChatData,
                'messages' => $messages,
                'participants' => $participants,
            ];
        } catch (\Exception $e) {
            \Log::error('Error preparing chat data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'allChats' => collect([]),
                'currentChat' => null,
                'messages' => collect([]),
                'participants' => [],
            ];
        }
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $data = $this->prepareChatData($user);

            return view('chat.index', [
                'allChats' => $data['allChats'],
                'currentChat' => null,
                'messages' => collect([]),
                'participants' => [],
                'title' => 'Chats – RWAMP',
            ]);
        } catch (\Exception $e) {
            \Log::error('Chat index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('chat.index', [
                'allChats' => collect([]),
                'currentChat' => null,
                'messages' => collect([]),
                'participants' => [],
                'title' => 'Chats – RWAMP',
                'error' => 'Unable to load chats. Please try again.',
            ]);
        }
    }

    /**
     * Show a specific chat
     */
    public function show(Chat $chat)
    {
        \Log::info('[ChatController] show() called', [
            'chat_id' => $chat->id ?? null,
            'user_id' => auth()->id(),
            'is_ajax' => request()->ajax(),
            'wants_json' => request()->wantsJson()
        ]);
        
        $user = Auth::user();

        // Verify user is a participant (unless admin)
        if (!$user->isAdmin() && !$chat->hasParticipant($user->id)) {
            \Log::warning('[ChatController] User not authorized', [
                'user_id' => $user->id,
                'chat_id' => $chat->id
            ]);
            abort(403, 'You do not have access to this chat');
        }

        // Mark messages as read
        if (!$user->isAdmin()) {
            try {
                $this->chatService->markAsRead($chat->id, $user->id);
                \Log::info('[ChatController] Messages marked as read');
            } catch (\Exception $e) {
                \Log::error('[ChatController] Error marking messages as read', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Prepare all data using shared method
        $data = $this->prepareChatData($user, $chat);

        // Return JSON for AJAX/API requests
        if (request()->wantsJson() || request()->ajax() || request()->is('api/*')) {
            \Log::info('[ChatController] Returning JSON response', [
                'chat_id' => $data['currentChat']['id'] ?? null,
                'messages_count' => $data['messages']->count(),
                'participants_count' => count($data['participants'])
            ]);
            
            return response()->json([
                'chat' => $data['currentChat'],
                'messages' => $data['messages'],
                'participants' => $data['participants'],
            ]);
        }

        \Log::info('[ChatController] Returning view', [
            'chat_id' => $data['currentChat']['id'] ?? null,
            'all_chats_count' => $data['allChats']->count()
        ]);

        return view('chat.show', [
            'allChats' => $data['allChats'],
            'currentChat' => $data['currentChat'],
            'messages' => $data['messages'],
            'participants' => $data['participants'],
            'title' => ($data['currentChat']['display_name'] ?? 'Chat') . ' – RWAMP',
        ]);
    }

    /**
     * Create a new private chat
     */
    public function createPrivateChat(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $otherUserId = $validated['user_id'];

        if ($user->id === $otherUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create chat with yourself',
            ], 422);
        }

        $chat = $this->chatService->createPrivateChat($user->id, $otherUserId);

        return response()->json([
            'success' => true,
            'chat' => $chat->load(['participants', 'latestMessage']),
            'redirect' => route('chat.show', $chat->id),
        ]);
    }

    /**
     * Create a group chat
     */
    public function createGroupChat(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $user = Auth::user();
        $chat = $this->chatService->createGroupChat($user->id, $validated['name'], $validated['user_ids']);

        return response()->json([
            'success' => true,
            'chat' => $chat->load(['participants', 'latestMessage']),
            'redirect' => route('chat.show', $chat->id),
        ]);
    }

    /**
     * Send a message
     */
    public function storeMessage(Request $request, Chat $chat)
    {
        // Rate limiting: 5 messages per second
        $key = 'send-message:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many messages. Please slow down.',
            ], 429);
        }
        RateLimiter::hit($key, 1);

        $user = Auth::user();

        // Verify user is a participant
        if (!$chat->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this chat',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'nullable|string|max:5000',
            'media_type' => 'nullable|in:image,file,location,receipt,voice',
            'file' => 'nullable|file|max:10240', // 10MB max
            'location_data' => 'nullable|array',
        ]);

        try {
            $file = $request->file('file');
            $locationData = $validated['location_data'] ?? null;

            $message = $this->chatService->sendMessage(
                $chat->id,
                $user->id,
                $validated['content'] ?? null,
                $validated['media_type'] ?? null,
                $file,
                $locationData
            );

            // Broadcast event
            event(new \App\Events\ChatMessageSent($message));

            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload receipt
     */
    public function uploadReceipt(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'receipt' => 'required|image|max:5120', // 5MB max
        ]);

        $user = Auth::user();

        if (!$chat->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this chat',
            ], 403);
        }

        try {
            $message = $this->chatService->sendMessage(
                $chat->id,
                $user->id,
                null,
                'receipt',
                $request->file('receipt')
            );

            // Broadcast event
            event(new \App\Events\ChatMessageSent($message));

            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, Chat $chat, ChatMessage $message)
    {
        $user = Auth::user();

        if ($message->chat_id !== $chat->id) {
            return response()->json([
                'success' => false,
                'message' => 'Message does not belong to this chat',
            ], 422);
        }

        try {
            $this->chatService->deleteMessage($message->id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Message deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Toggle pin
     */
    public function togglePin(Request $request, Chat $chat)
    {
        $pin = $request->input('pin', true);
        $this->chatService->togglePin($chat->id, Auth::id(), $pin);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle mute
     */
    public function toggleMute(Request $request, Chat $chat)
    {
        $mute = $request->input('mute', true);
        $this->chatService->toggleMute($chat->id, Auth::id(), $mute);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle archive
     */
    public function toggleArchive(Request $request, Chat $chat)
    {
        $archive = $request->input('archive', true);
        $this->chatService->toggleArchive($chat->id, Auth::id(), $archive);

        return response()->json(['success' => true]);
    }

    /**
     * Search users
     */
    public function searchUsers(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['users' => []]);
        }

        $users = $this->chatService->searchUsers($query, Auth::id());

        return response()->json(['users' => $users]);
    }

    /**
     * Upload voice message
     */
    public function uploadVoice(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:mp3,wav,ogg,webm|max:10240', // 10MB max
        ]);

        $user = Auth::user();

        if (!$chat->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this chat',
            ], 403);
        }

        try {
            $message = $this->chatService->sendMessage(
                $chat->id,
                $user->id,
                null,
                'voice',
                $request->file('file')
            );

            // Broadcast event
            event(new \App\Events\ChatMessageSent($message));

            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add reaction to message
     */
    public function reactToMessage(Request $request, Chat $chat, ChatMessage $message)
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $user = Auth::user();

        if ($message->chat_id !== $chat->id) {
            return response()->json([
                'success' => false,
                'message' => 'Message does not belong to this chat',
            ], 422);
        }

        if (!$chat->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this chat',
            ], 403);
        }

        $message->update(['reaction' => $validated['emoji']]);

        // Broadcast reaction update
        event(new \App\Events\ChatMessageSent($message->fresh()->load('sender')));

        return response()->json([
            'success' => true,
            'message' => $message->fresh()->load('sender'),
        ]);
    }

    /**
     * Mark message as read
     */
    public function markMessageAsRead(Chat $chat, ChatMessage $message)
    {
        $user = Auth::user();

        if ($message->chat_id !== $chat->id) {
            return response()->json([
                'success' => false,
                'message' => 'Message does not belong to this chat',
            ], 422);
        }

        if (!$chat->hasParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this chat',
            ], 403);
        }

        $message->markAsRead($user->id);

        return response()->json(['success' => true]);
    }

    /**
     * Format participants for frontend (avoid relationship/column conflict)
     */
    private function formatParticipants(Chat $chat): array
    {
        // Query participants directly to avoid relationship/column conflict
        $participantIds = ChatParticipant::where('chat_id', $chat->id)->pluck('user_id');
        $participants = User::whereIn('id', $participantIds)->get();
        
        return $participants->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name ?? 'Unknown',
                'avatar_url' => $p->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($p->name ?? 'User') . '&background=E30613&color=fff',
            ];
        })->toArray();
    }
}


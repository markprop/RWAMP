<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ChatService
{
    /**
     * Create a new private chat between two users
     */
    public function createPrivateChat(int $userId1, int $userId2): Chat
    {
        // Check if chat already exists by querying ChatParticipant directly
        $chatIds1 = ChatParticipant::where('user_id', $userId1)->pluck('chat_id');
        $chatIds2 = ChatParticipant::where('user_id', $userId2)->pluck('chat_id');
        $commonChatIds = $chatIds1->intersect($chatIds2);
        
        $existingChat = Chat::where('type', 'private')
            ->whereIn('id', $commonChatIds)
            ->first();

        if ($existingChat) {
            return $existingChat;
        }

        return DB::transaction(function () use ($userId1, $userId2) {
            $chat = Chat::create([
                'user_id' => $userId1,
                'type' => 'private',
                'participants' => [$userId1, $userId2],
            ]);

            // Add participants
            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $userId1,
            ]);

            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $userId2,
            ]);

            return $chat;
        });
    }

    /**
     * Create a group chat
     */
    public function createGroupChat(int $creatorId, string $name, array $userIds): Chat
    {
        $participants = array_unique(array_merge([$creatorId], $userIds));

        return DB::transaction(function () use ($creatorId, $name, $participants) {
            $chat = Chat::create([
                'user_id' => $creatorId,
                'type' => 'group',
                'name' => $name,
                'participants' => $participants,
            ]);

            // Add all participants
            foreach ($participants as $userId) {
                ChatParticipant::create([
                    'chat_id' => $chat->id,
                    'user_id' => $userId,
                    'is_admin' => $userId === $creatorId,
                ]);
            }

            return $chat;
        });
    }

    /**
     * Send a message in a chat
     */
    public function sendMessage(int $chatId, int $senderId, string $content = null, string $mediaType = null, $file = null, array $locationData = null): ChatMessage
    {
        $chat = Chat::findOrFail($chatId);

        // Verify sender is a participant
        if (!$chat->hasParticipant($senderId)) {
            throw new \Exception('You are not a participant in this chat');
        }

        $mediaPath = null;
        $mediaName = null;
        $mediaSize = null;

        // Handle file upload
        if ($file) {
            // For voice messages, use specific naming
            if ($mediaType === 'voice') {
                $mediaPath = $file->store("chats/{$chatId}/voice", 'public');
            } else {
                $mediaPath = $file->store("chats/{$chatId}", 'public');
            }
            $mediaName = $file->getClientOriginalName();
            $mediaSize = $file->getSize();
        }

        return DB::transaction(function () use ($chat, $senderId, $content, $mediaType, $mediaPath, $mediaName, $mediaSize, $locationData) {
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $senderId,
                'content' => $content,
                'media_type' => $mediaType,
                'media_path' => $mediaPath,
                'media_name' => $mediaName,
                'media_size' => $mediaSize,
                'location_data' => $locationData,
            ]);

            // Update chat last message time
            $chat->update(['last_message_at' => now()]);

            // Increment unread count for all participants except sender
            ChatParticipant::where('chat_id', $chat->id)
                ->where('user_id', '!=', $senderId)
                ->increment('unread_count');

            // If receipt, link to user profile
            if ($mediaType === 'receipt' && $mediaPath) {
                User::find($senderId)->update([
                    'receipt_screenshot' => $mediaPath,
                ]);
            }

            return $message->load('sender');
        });
    }

    /**
     * Delete a message (soft delete)
     */
    public function deleteMessage(int $messageId, int $userId): bool
    {
        $message = ChatMessage::findOrFail($messageId);

        // Only sender or admin can delete
        if ($message->sender_id !== $userId && !auth()->user()->isAdmin()) {
            throw new \Exception('You do not have permission to delete this message');
        }

        return $message->update([
            'is_deleted' => true,
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(int $chatId, int $userId): void
    {
        $participant = ChatParticipant::where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->first();

        if ($participant) {
            $participant->resetUnread();

            // Mark all unread messages as read
            ChatMessage::where('chat_id', $chatId)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->get()
                ->each(function ($message) use ($userId) {
                    $message->markAsRead($userId);
                });
        }
    }

    /**
     * Get user's chats
     */
    public function getUserChats(int $userId, bool $includeArchived = false)
    {
        // Use ChatParticipant directly to avoid relationship/column conflict
        $chatIds = ChatParticipant::where('user_id', $userId);
        
        if (!$includeArchived) {
            $chatIds->where('is_archived', false);
        }
        
        $chatIds = $chatIds->pluck('chat_id');
        
        $query = Chat::whereIn('id', $chatIds)
        ->with([
            'latestMessage' => function($q) {
                $q->with('sender');
            },
            'participants'
        ])
        ->orderBy('last_message_at', 'desc');

        $chats = $query->get();

        // Add unread_count and format for frontend
        return $chats->map(function ($chat) use ($userId) {
            // Get unread count from participant pivot
            // Use ChatParticipant model directly to avoid relationship/column conflict
            $participant = ChatParticipant::where('chat_id', $chat->id)
                ->where('user_id', $userId)
                ->first();
            
            $unreadCount = $participant?->unread_count ?? 0;
            
            // If pivot doesn't have unread_count, calculate it
            if ($unreadCount === 0) {
                $unreadCount = ChatMessage::where('chat_id', $chat->id)
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', false)
                    ->count();
            }

            // Add unread_count as attribute
            $chat->unread_count = $unreadCount;
            
            return $chat;
        });
    }

    /**
     * Pin/unpin a chat
     */
    public function togglePin(int $chatId, int $userId, bool $pin = true): void
    {
        ChatParticipant::where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->update(['is_pinned' => $pin]);
    }

    /**
     * Mute/unmute a chat
     */
    public function toggleMute(int $chatId, int $userId, bool $mute = true): void
    {
        ChatParticipant::where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->update(['is_muted' => $mute]);
    }

    /**
     * Archive/unarchive a chat
     */
    public function toggleArchive(int $chatId, int $userId, bool $archive = true): void
    {
        ChatParticipant::where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->update(['is_archived' => $archive]);
    }

    /**
     * Search users for chat
     */
    public function searchUsers(string $query, int $currentUserId)
    {
        return User::where('id', '!=', $currentUserId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->whereIn('role', ['investor', 'reseller'])
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar_url' => $user->avatar_url,
                ];
            });
    }
}


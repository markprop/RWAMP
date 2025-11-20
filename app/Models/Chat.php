<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'participants',
        'last_message_at',
    ];

    protected $casts = [
        'participants' => 'array',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the user who created the chat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all participants
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
            ->withPivot(['is_admin', 'is_pinned', 'is_muted', 'is_archived', 'last_read_at', 'unread_count'])
            ->withTimestamps();
    }

    /**
     * Get all messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany('created_at');
    }

    /**
     * Get participant pivot data for a specific user
     */
    public function getParticipantData(int $userId)
    {
        // Use ChatParticipant model directly to avoid relationship/column conflict
        return \App\Models\ChatParticipant::where('chat_id', $this->id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Check if user is a participant
     */
    public function hasParticipant(int $userId): bool
    {
        // Use ChatParticipant model directly to avoid relationship/column conflict
        return \App\Models\ChatParticipant::where('chat_id', $this->id)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get other participants (excluding current user)
     */
    public function getOtherParticipants(int $currentUserId)
    {
        // Use ChatParticipant model directly to avoid relationship/column conflict
        $participantIds = \App\Models\ChatParticipant::where('chat_id', $this->id)
            ->where('user_id', '!=', $currentUserId)
            ->pluck('user_id');
        
        return \App\Models\User::whereIn('id', $participantIds)->get();
    }

    /**
     * Get chat name for display
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'group' && $this->name) {
            return $this->name;
        }

        // For private chats, return other participant's name
        if ($this->type === 'private' && auth()->check()) {
            // Use ChatParticipant directly to avoid relationship/column conflict
            $participantIds = \App\Models\ChatParticipant::where('chat_id', $this->id)
                ->where('user_id', '!=', auth()->id())
                ->pluck('user_id');
            
            if ($participantIds->isNotEmpty()) {
                $other = \App\Models\User::find($participantIds->first());
                return $other ? $other->name : 'Unknown User';
            }
        }

        return $this->name ?? 'Chat';
    }
}


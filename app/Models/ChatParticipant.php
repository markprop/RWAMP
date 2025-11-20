<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'is_admin',
        'is_pinned',
        'is_muted',
        'is_archived',
        'last_read_at',
        'unread_count',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_pinned' => 'boolean',
        'is_muted' => 'boolean',
        'is_archived' => 'boolean',
        'last_read_at' => 'datetime',
        'unread_count' => 'integer',
    ];

    /**
     * Get the chat
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment unread count
     */
    public function incrementUnread(): void
    {
        $this->increment('unread_count');
    }

    /**
     * Reset unread count
     */
    public function resetUnread(): void
    {
        $this->update([
            'unread_count' => 0,
            'last_read_at' => now(),
        ]);
    }
}


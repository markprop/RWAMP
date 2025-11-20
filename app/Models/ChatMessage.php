<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'content',
        'media_type',
        'media_path',
        'media_name',
        'media_size',
        'location_data',
        'is_deleted',
        'deleted_by',
        'deleted_at',
        'is_read',
        'read_at',
        'reaction',
    ];

    protected $casts = [
        'location_data' => 'array',
        'is_deleted' => 'boolean',
        'is_read' => 'boolean',
        'deleted_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Get the chat this message belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the sender of the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get who deleted the message
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get read receipts
     */
    public function reads(): HasMany
    {
        return $this->hasMany(ChatMessageRead::class, 'message_id');
    }

    /**
     * Check if message is read by user
     */
    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Mark message as read by user
     */
    public function markAsRead(int $userId): void
    {
        $this->reads()->firstOrCreate([
            'message_id' => $this->id,
            'user_id' => $userId,
        ], [
            'read_at' => now(),
        ]);

        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Get display content (handles deleted messages)
     */
    public function getDisplayContentAttribute(): string
    {
        if ($this->is_deleted) {
            return 'This message was deleted';
        }

        if ($this->media_type === 'receipt') {
            return '📄 Payment Receipt';
        }

        if ($this->media_type === 'location') {
            return '📍 Location Shared';
        }

        if ($this->media_type === 'voice') {
            return '🎤 Voice Message';
        }

        return $this->content ?? '';
    }

    /**
     * Get media URL
     */
    public function getMediaUrlAttribute(): ?string
    {
        if (!$this->media_path) {
            return null;
        }

        return asset('storage/' . $this->media_path);
    }
}


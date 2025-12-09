<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameTrade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'side',
        'quantity',
        'price_pkr',
        'fee_pkr',
        'spread_revenue_pkr',
        'game_balance_after',
        'idempotency_key',
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price_pkr' => 'decimal:8',
        'fee_pkr' => 'decimal:8',
        'spread_revenue_pkr' => 'decimal:8',
        'game_balance_after' => 'decimal:8',
    ];

    /**
     * Get the game session that owns this trade
     */
    public function session()
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePriceHistory extends Model
{
    use HasFactory;

    protected $table = 'game_price_history';

    protected $fillable = [
        'session_id',
        'mid_price',
        'buy_price',
        'sell_price',
        'btc_usd',
        'usd_pkr',
        'recorded_at',
    ];

    protected $casts = [
        'mid_price' => 'decimal:8',
        'buy_price' => 'decimal:8',
        'sell_price' => 'decimal:8',
        'btc_usd' => 'decimal:8',
        'usd_pkr' => 'decimal:8',
        'recorded_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Get the game session that owns this price history
     */
    public function session()
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }
}

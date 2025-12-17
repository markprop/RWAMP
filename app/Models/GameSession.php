<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_game_sessions';

    protected $fillable = [
        'user_id',
        'type',
        'real_balance_start',
        'game_balance_start',
        'game_balance_end',
        'real_balance_end',
        'total_platform_revenue',
        'net_user_pnl_pkr',
        'anchor_btc_usd',
        'anchor_mid_price',
        'status',
        'started_at',
        'ended_at',
        'chart_state',
        'state_json',
    ];

    protected $casts = [
        'real_balance_start' => 'decimal:8',
        'game_balance_start' => 'decimal:8',
        'game_balance_end' => 'decimal:8',
        'real_balance_end' => 'decimal:8',
        'total_platform_revenue' => 'decimal:8',
        'net_user_pnl_pkr' => 'decimal:8',
        'anchor_btc_usd' => 'decimal:8',
        'anchor_mid_price' => 'decimal:8',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'chart_state' => 'array',
    ];

    /**
     * Get FOPI state as array (deserialize state_json)
     */
    public function getFopiState(): ?array
    {
        if (!$this->state_json) {
            return null;
        }
        return json_decode($this->state_json, true);
    }

    /**
     * Set FOPI state (serialize to state_json)
     */
    public function setFopiState(array $state): void
    {
        $this->state_json = json_encode($state);
    }

    /**
     * Check if this is a FOPI game session
     */
    public function isFopi(): bool
    {
        return $this->type === 'fopi';
    }

    /**
     * Check if this is a Trading game session
     */
    public function isTrading(): bool
    {
        return $this->type === 'trading';
    }

    /**
     * Get the user that owns the game session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all trades for this session
     */
    public function trades()
    {
        return $this->hasMany(GameTrade::class, 'session_id');
    }

    /**
     * Get price history for this session
     */
    public function priceHistory()
    {
        return $this->hasMany(GamePriceHistory::class, 'session_id');
    }

    /**
     * Calculate current game balance from trades (in RWAMP tokens)
     * When BUY: user gets RWAMP tokens (balance increases)
     * When SELL: user gives RWAMP tokens (balance decreases)
     */
    public function calculateCurrentBalance(): float
    {
        $balance = $this->game_balance_start;
        
        foreach ($this->trades as $trade) {
            if ($trade->side === 'BUY') {
                // BUY: User spends PKR to get RWAMP tokens, so RWAMP balance increases
                $balance += $trade->quantity;
            } else {
                // SELL: User sells RWAMP tokens to get PKR, so RWAMP balance decreases
                $balance -= $trade->quantity;
            }
        }
        
        return max(0, $balance);
    }

    /**
     * Calculate current PKR balance from trades (for validation)
     * This represents the PKR available to spend on BUY orders
     * Initially, user starts with PKR equivalent of their RWAMP tokens at anchor mid price
     */
    public function calculateCurrentPkrBalance(): float
    {
        // Start with PKR equivalent of initial RWAMP balance at anchor mid price
        // This allows users to buy immediately without needing to sell first
        $pkrBalance = $this->game_balance_start * $this->anchor_mid_price;
        
        foreach ($this->trades as $trade) {
            if ($trade->side === 'BUY') {
                // BUY: Spend PKR to get RWAMP tokens
                $pkrBalance -= ($trade->quantity * $trade->price_pkr) + $trade->fee_pkr;
            } else {
                // SELL: Get PKR by selling RWAMP tokens
                $pkrBalance += ($trade->quantity * $trade->price_pkr) - $trade->fee_pkr;
            }
        }
        
        return max(0, $pkrBalance);
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ended_at === null;
    }
}

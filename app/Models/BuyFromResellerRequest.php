<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyFromResellerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reseller_id',
        'coin_quantity',
        'coin_price',
        'total_amount',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'completed_at',
    ];

    protected $casts = [
        'coin_quantity' => 'decimal:2',
        'coin_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reseller()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }
}

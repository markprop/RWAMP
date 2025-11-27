<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_address',
        'token_amount',
        'status',
        'notes',
        'receipt_path',
        'transaction_hash',
        'transfer_completed_at',
    ];

    protected $casts = [
        'token_amount' => 'decimal:2',
        'transfer_completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


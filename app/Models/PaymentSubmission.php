<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_id',
        'recipient_type',
        'token_amount',
        'fiat_amount',
        'currency',
        'bank_name',
        'account_last4',
        'bank_reference',
        'receipt_path',
        'status',
        'admin_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}


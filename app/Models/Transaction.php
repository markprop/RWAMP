<?php

namespace App\Models;

use App\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'user_id',
        'sender_id',
        'recipient_id',
        'type',
        'amount',
        'price_per_coin',
        'total_price',
        'sender_type',
        'status',
        'reference',
        'payment_type',
        'payment_hash',
        'payment_receipt',
        'payment_status',
        'verified_by',
        'verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the display payment status.
     * For cash payments, automatically show as verified if payment_type exists.
     */
    public function getDisplayPaymentStatusAttribute()
    {
        // For cash payments, if payment_type exists, it means payment was received, so mark as verified
        if ($this->payment_type === 'cash' && $this->payment_status === 'pending') {
            return 'verified';
        }
        return $this->payment_status;
    }
}

?>



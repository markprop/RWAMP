<?php

namespace App\Models;

use App\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoPayment extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'user_id',
        'token_amount',
        'usd_amount',
        'pkr_amount',
        'coin_price_rs',
        'network',
        'tx_hash',
        'screenshot',
        'notes',
        'status',
        'reseller_commission_awarded',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

?>



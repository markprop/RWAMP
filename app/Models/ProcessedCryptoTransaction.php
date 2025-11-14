<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedCryptoTransaction extends Model
{
    use HasFactory;

    protected $table = 'processed_crypto_tx';

    protected $fillable = [
        'tx_hash',
        'network',
        'amount_usd',
    ];
}

?>




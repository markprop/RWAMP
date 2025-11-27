<?php

namespace App\Traits;

use App\Models\User;

trait GeneratesWalletAddress
{
    /**
     * Generate a unique 16-digit wallet address
     */
    protected function generateUniqueWalletAddress(): string
    {
        do {
            $wallet = str_pad(random_int(1000000000000000, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } while (User::where('wallet_address', $wallet)->exists());

        return $wallet;
    }
}


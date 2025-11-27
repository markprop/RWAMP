<?php

namespace Database\Factories;

use App\Models\CryptoPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoPaymentFactory extends Factory
{
    protected $model = CryptoPayment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token_amount' => 1000,
            'usd_amount' => '100',
            'pkr_amount' => '28000',
            'coin_price_rs' => 28,
            'network' => 'TRC20',
            'tx_hash' => $this->faker->unique()->sha256(),
            'screenshot' => null,
            'notes' => null,
            'status' => 'pending',
            'reseller_commission_awarded' => false,
        ];
    }
}



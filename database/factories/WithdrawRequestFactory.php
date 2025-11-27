<?php

namespace Database\Factories;

use App\Models\WithdrawRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawRequestFactory extends Factory
{
    protected $model = WithdrawRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_address' => $this->faker->bothify('T????????????????'),
            'token_amount' => 1000,
            'status' => 'pending',
            'notes' => null,
            'receipt_path' => null,
            'transaction_hash' => null,
            'transfer_completed_at' => null,
        ];
    }
}



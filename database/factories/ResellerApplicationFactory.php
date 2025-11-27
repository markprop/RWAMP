<?php

namespace Database\Factories;

use App\Models\ResellerApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerApplicationFactory extends Factory
{
    protected $model = ResellerApplication::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'password' => null,
            'company' => $this->faker->company(),
            'investment_capacity' => '1-10k',
            'experience' => $this->faker->sentence(),
            'message' => $this->faker->sentence(10),
            'status' => 'pending',
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => 'testing',
        ];
    }
}



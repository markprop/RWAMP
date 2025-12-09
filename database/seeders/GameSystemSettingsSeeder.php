<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'tokens_per_btc',
                'value' => '1000000',
                'type' => 'float',
                'description' => 'Number of RWAMP tokens per BTC',
            ],
            [
                'key' => 'spread_pkr',
                'value' => '0.5',
                'type' => 'float',
                'description' => 'Spread in PKR for buy/sell prices',
            ],
            [
                'key' => 'buy_fee_pct',
                'value' => '0.01',
                'type' => 'float',
                'description' => 'Buy fee percentage (1%)',
            ],
            [
                'key' => 'sell_fee_pct',
                'value' => '0.01',
                'type' => 'float',
                'description' => 'Sell fee percentage (1%)',
            ],
            [
                'key' => 'velocity_multiplier',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Velocity multiplier for BTC price changes',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Game system settings seeded successfully!');
    }
}

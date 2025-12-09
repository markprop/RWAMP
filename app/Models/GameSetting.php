<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    protected $fillable = [
        'entry_multiplier',
        'exit_divisor',
        'exit_fee_rate',
        'game_timeout_seconds',
    ];

    /**
     * Retrieve the singleton settings row, creating it with sensible defaults if needed.
     */
    public static function current(): self
    {
        /** @var self $settings */
        $settings = static::query()->first();

        if (! $settings) {
            $settings = static::query()->create([
                'entry_multiplier'     => 10.0,
                'exit_divisor'         => 100.0,
                'exit_fee_rate'        => 0.0,
                'game_timeout_seconds' => null,
            ]);
        }

        return $settings;
    }
}



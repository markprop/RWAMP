<?php

namespace App\Events;

use App\Models\GameSetting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameSettingsUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public float $entry_multiplier;
    public float $exit_divisor;
    public float $exit_fee_rate;
    public ?int $game_timeout_seconds;

    public function __construct(GameSetting $settings)
    {
        $this->entry_multiplier = (float) $settings->entry_multiplier;
        $this->exit_divisor = (float) $settings->exit_divisor;
        $this->exit_fee_rate = (float) $settings->exit_fee_rate;
        $this->game_timeout_seconds = $settings->game_timeout_seconds !== null
            ? (int) $settings->game_timeout_seconds
            : null;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // Authenticated users (including players) may listen so their game UI
        // can react to admin changes in real time.
        return new PrivateChannel('game.settings');
    }

    public function broadcastAs(): string
    {
        return 'GameSettingsUpdated';
    }

    /**
     * Data payload sent to listeners.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'entry_multiplier'     => $this->entry_multiplier,
            'exit_divisor'         => $this->exit_divisor,
            'exit_fee_rate'        => $this->exit_fee_rate,
            'game_timeout_seconds' => $this->game_timeout_seconds,
        ];
    }
}



<?php

namespace App\Http\Controllers;

use App\Events\GameSettingsUpdated;
use App\Models\GameSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameSettingController extends Controller
{
    /**
     * Return the current game settings as JSON.
     */
    public function show(Request $request): JsonResponse
    {
        $settings = GameSetting::current();

        return response()->json([
            'success' => true,
            'data' => [
                'entry_multiplier'     => (float) $settings->entry_multiplier,
                'exit_divisor'         => (float) $settings->exit_divisor,
                'exit_fee_rate'        => (float) $settings->exit_fee_rate,
                'game_timeout_seconds' => $settings->game_timeout_seconds !== null
                    ? (int) $settings->game_timeout_seconds
                    : null,
            ],
        ]);
    }

    /**
     * Update the game settings from the admin panel.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_multiplier'     => ['required', 'numeric', 'gt:0'],
            'exit_divisor'         => ['required', 'numeric', 'gt:0'],
            'exit_fee_rate'        => ['required', 'numeric', 'between:0,100'],
            'game_timeout_seconds' => ['nullable', 'integer', 'min:10', 'max:86400'],
        ]);

        $settings = GameSetting::current();

        $settings->fill($validated);
        $settings->save();

        event(new GameSettingsUpdated($settings));

        return response()->json([
            'success' => true,
            'message' => 'Game settings updated successfully.',
            'data' => [
                'entry_multiplier'     => (float) $settings->entry_multiplier,
                'exit_divisor'         => (float) $settings->exit_divisor,
                'exit_fee_rate'        => (float) $settings->exit_fee_rate,
                'game_timeout_seconds' => $settings->game_timeout_seconds !== null
                    ? (int) $settings->game_timeout_seconds
                    : null,
            ],
        ]);
    }
}



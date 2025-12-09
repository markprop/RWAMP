<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Game settings channel - any authenticated user can listen so game UIs
// can react to admin configuration changes in real time.
Broadcast::channel('game.settings', function ($user) {
    return $user !== null;
});

// Chat channel - only participants and admins can listen
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    try {
        $chat = \App\Models\Chat::findOrFail($chatId);
        
        // Admins can always listen
        if ($user->isAdmin()) {
            return true;
        }
        
        // Check if user is a participant
        return $chat->hasParticipant($user->id);
    } catch (\Exception $e) {
        return false;
    }
});


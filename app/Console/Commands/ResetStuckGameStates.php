<?php

namespace App\Console\Commands;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Console\Command;

class ResetStuckGameStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:reset-stuck-states';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all stuck game states (users marked as in_game without active sessions)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for stuck game states...');
        
        // Find all users marked as in_game
        $usersInGame = User::where('is_in_game', true)->get();
        $resetCount = 0;
        
        foreach ($usersInGame as $user) {
            $activeSession = $user->activeGameSession;
            
            if (!$activeSession) {
                // User is marked as in_game but has no active session - reset it
                $user->is_in_game = false;
                $user->save();
                
                // Mark any orphaned active sessions as abandoned
                GameSession::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'abandoned',
                        'ended_at' => now(),
                    ]);
                
                $resetCount++;
                $this->line("Reset game state for user: {$user->email} (ID: {$user->id})");
            }
        }
        
        if ($resetCount > 0) {
            $this->info("Successfully reset {$resetCount} stuck game state(s).");
        } else {
            $this->info('No stuck game states found.');
        }
        
        return Command::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table may already exist on some environments; avoid duplicate-creation errors.
        if (Schema::hasTable('fopi_game_events')) {
            return;
        }

        Schema::create('fopi_game_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Link to the existing user_game_sessions table used by GameSession model
            $table->foreignId('session_id')->constrained('user_game_sessions')->onDelete('cascade');
            $table->string('event_type', 50);
            $table->json('details');
            $table->timestamps();

            $table->index(['user_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fopi_game_events');
    }
};


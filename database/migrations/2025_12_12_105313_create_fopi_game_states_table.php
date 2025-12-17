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
        Schema::create('fopi_game_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('user_game_sessions')->onDelete('cascade');
            $table->integer('current_month')->default(1);
            $table->integer('game_day')->default(1);
            $table->decimal('fopi_balance', 20, 8)->default(0);
            $table->decimal('rwamp_balance', 20, 8)->default(0);
            $table->decimal('unclaimed_rent', 20, 8)->default(0);
            $table->decimal('total_rent_collected', 20, 8)->default(0);
            $table->decimal('total_rwamp_mined', 20, 8)->default(0);
            $table->decimal('total_rwamp_earned', 20, 8)->default(0);
            $table->decimal('total_rwamp_spent', 20, 8)->default(0);
            $table->json('achievements')->nullable();
            $table->json('missions')->nullable();
            $table->json('awards')->nullable();
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamps();
            
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fopi_game_states');
    }
};

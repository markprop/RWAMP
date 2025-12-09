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
        Schema::create('game_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('user_game_sessions')->onDelete('cascade');
            $table->decimal('mid_price', 20, 8);
            $table->decimal('buy_price', 20, 8);
            $table->decimal('sell_price', 20, 8);
            $table->decimal('btc_usd', 20, 8);
            $table->decimal('usd_pkr', 20, 8);
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index('session_id');
            $table->index('recorded_at');
            $table->index(['session_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_price_history');
    }
};

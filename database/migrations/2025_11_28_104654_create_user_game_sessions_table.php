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
        Schema::create('user_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('real_balance_start', 20, 8);
            $table->decimal('game_balance_start', 20, 8);
            $table->decimal('game_balance_end', 20, 8)->nullable();
            $table->decimal('real_balance_end', 20, 8)->nullable();
            $table->decimal('total_platform_revenue', 20, 8)->default(0);
            $table->decimal('net_user_pnl_pkr', 20, 8)->nullable();
            $table->decimal('anchor_btc_usd', 20, 8); // BTC price at session start
            $table->decimal('anchor_mid_price', 20, 8); // RWAMP mid price at anchor
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('chart_state')->nullable(); // { timeframe: '5m', chartType: 'candlestick' }
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_game_sessions');
    }
};

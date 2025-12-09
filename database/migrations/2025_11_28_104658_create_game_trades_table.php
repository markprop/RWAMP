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
        Schema::create('game_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('user_game_sessions')->onDelete('cascade');
            $table->enum('side', ['BUY', 'SELL']);
            $table->decimal('quantity', 20, 8);
            $table->decimal('price_pkr', 20, 8); // executed price (incl. spread)
            $table->decimal('fee_pkr', 20, 8);
            $table->decimal('spread_revenue_pkr', 20, 8);
            $table->decimal('game_balance_after', 20, 8); // game balance *after* this trade
            $table->string('idempotency_key')->unique()->nullable(); // Prevent replay attacks
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('session_id');
            $table->index('side');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_trades');
    }
};

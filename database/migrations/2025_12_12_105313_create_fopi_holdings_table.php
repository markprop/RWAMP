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
        Schema::create('fopi_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('user_game_sessions')->onDelete('cascade');
            $table->string('property_id', 50);
            $table->decimal('sqft_owned', 20, 8);
            $table->decimal('cost_basis', 20, 8); // Total cost paid
            $table->decimal('avg_price_per_sqft', 20, 8);
            $table->integer('months_held')->default(0);
            $table->decimal('unrealized_pl', 20, 8)->default(0);
            $table->boolean('in_sell_queue')->default(false);
            $table->integer('sell_queue_months_remaining')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fopi_holdings');
    }
};

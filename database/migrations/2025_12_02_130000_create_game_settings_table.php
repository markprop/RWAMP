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
        Schema::create('game_settings', function (Blueprint $table) {
            $table->id();

            // Core game economics configuration
            $table->decimal('entry_multiplier', 10, 4)->default(10.0);
            $table->decimal('exit_divisor', 10, 4)->default(100.0);
            $table->decimal('exit_fee_rate', 5, 2)->default(0.0); // percentage, e.g. 2.5 = 2.5%
            $table->unsignedInteger('game_timeout_seconds')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_settings');
    }
};



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
        Schema::table('users', function (Blueprint $table) {
            // Add game-specific PIN fields
            $table->string('trading_game_pin_hash')->nullable()->after('game_pin_hash');
            $table->string('fopi_game_pin_hash')->nullable()->after('trading_game_pin_hash');
            $table->integer('trading_game_pin_failed_attempts')->default(0)->after('trading_game_pin_hash');
            $table->integer('fopi_game_pin_failed_attempts')->default(0)->after('fopi_game_pin_hash');
            $table->timestamp('trading_game_pin_locked_until')->nullable()->after('trading_game_pin_failed_attempts');
            $table->timestamp('fopi_game_pin_locked_until')->nullable()->after('fopi_game_pin_failed_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'trading_game_pin_hash',
                'fopi_game_pin_hash',
                'trading_game_pin_failed_attempts',
                'fopi_game_pin_failed_attempts',
                'trading_game_pin_locked_until',
                'fopi_game_pin_locked_until',
            ]);
        });
    }
};

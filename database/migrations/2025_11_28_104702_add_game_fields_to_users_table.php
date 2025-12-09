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
            $table->string('game_pin_hash')->nullable()->after('password'); // bcrypt-hashed 4-digit PIN
            $table->boolean('is_in_game')->default(false)->after('game_pin_hash');
            $table->timestamp('game_pin_locked_until')->nullable()->after('is_in_game');
            $table->integer('game_pin_failed_attempts')->default(0)->after('game_pin_locked_until');
            
            $table->index('is_in_game');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['game_pin_hash', 'is_in_game', 'game_pin_locked_until', 'game_pin_failed_attempts']);
        });
    }
};

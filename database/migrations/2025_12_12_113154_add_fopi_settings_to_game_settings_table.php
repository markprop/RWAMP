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
        Schema::table('game_settings', function (Blueprint $table) {
            $table->decimal('fopi_per_rwamp', 20, 8)->default(1000)->after('game_timeout_seconds');
            $table->boolean('fopi_game_enabled')->default(true)->after('fopi_per_rwamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_settings', function (Blueprint $table) {
            $table->dropColumn(['fopi_per_rwamp', 'fopi_game_enabled']);
        });
    }
};

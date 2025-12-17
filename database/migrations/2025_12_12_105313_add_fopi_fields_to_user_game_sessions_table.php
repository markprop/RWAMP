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
        Schema::table('user_game_sessions', function (Blueprint $table) {
            $table->enum('type', ['trading', 'fopi'])->default('trading')->after('user_id');
            $table->longText('state_json')->nullable()->after('chart_state');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_game_sessions', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'state_json']);
        });
    }
};

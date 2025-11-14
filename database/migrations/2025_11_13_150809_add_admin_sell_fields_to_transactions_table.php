<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('sender_id')->nullable()->after('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->nullable()->after('sender_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price_per_coin', 10, 2)->nullable()->after('amount');
            $table->decimal('total_price', 18, 2)->nullable()->after('price_per_coin');
            $table->string('sender_type')->nullable()->after('total_price'); // 'admin', 'reseller', 'user'
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropColumn(['sender_id', 'recipient_id', 'price_per_coin', 'total_price', 'sender_type']);
        });
    }
};

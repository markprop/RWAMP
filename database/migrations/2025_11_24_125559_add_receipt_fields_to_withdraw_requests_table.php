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
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('notes');
            $table->string('transaction_hash')->nullable()->after('receipt_path');
            $table->timestamp('transfer_completed_at')->nullable()->after('transaction_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->dropColumn(['receipt_path', 'transaction_hash', 'transfer_completed_at']);
        });
    }
};

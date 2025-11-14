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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('reference'); // 'usdt', 'bank', 'cash'
            }
            if (!Schema::hasColumn('transactions', 'payment_hash')) {
                $table->string('payment_hash')->nullable()->after('payment_type'); // Transaction hash for USDT
            }
            if (!Schema::hasColumn('transactions', 'payment_receipt')) {
                $table->string('payment_receipt')->nullable()->after('payment_hash'); // File path for bank receipt/screenshot
            }
            if (!Schema::hasColumn('transactions', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('payment_receipt'); // 'pending', 'verified', 'rejected'
            }
            if (!Schema::hasColumn('transactions', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('payment_status')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('transactions', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'verified_by')) {
                $table->dropForeign(['verified_by']);
            }
            $columnsToDrop = [];
            if (Schema::hasColumn('transactions', 'payment_type')) {
                $columnsToDrop[] = 'payment_type';
            }
            if (Schema::hasColumn('transactions', 'payment_hash')) {
                $columnsToDrop[] = 'payment_hash';
            }
            if (Schema::hasColumn('transactions', 'payment_receipt')) {
                $columnsToDrop[] = 'payment_receipt';
            }
            if (Schema::hasColumn('transactions', 'payment_status')) {
                $columnsToDrop[] = 'payment_status';
            }
            if (Schema::hasColumn('transactions', 'verified_by')) {
                $columnsToDrop[] = 'verified_by';
            }
            if (Schema::hasColumn('transactions', 'verified_at')) {
                $columnsToDrop[] = 'verified_at';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

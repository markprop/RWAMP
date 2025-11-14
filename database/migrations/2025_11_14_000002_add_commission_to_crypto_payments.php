<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('crypto_payments', 'reseller_commission_awarded')) {
                $table->boolean('reseller_commission_awarded')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crypto_payments', function (Blueprint $table) {
            if (Schema::hasColumn('crypto_payments', 'reseller_commission_awarded')) {
                $table->dropColumn('reseller_commission_awarded');
            }
        });
    }
};


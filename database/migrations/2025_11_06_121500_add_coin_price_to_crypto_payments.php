<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crypto_payments')) {
            Schema::table('crypto_payments', function (Blueprint $table) {
                $table->decimal('coin_price_rs', 12, 4)->nullable()->after('pkr_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crypto_payments')) {
            Schema::table('crypto_payments', function (Blueprint $table) {
                $table->dropColumn('coin_price_rs');
            });
        }
    }
};



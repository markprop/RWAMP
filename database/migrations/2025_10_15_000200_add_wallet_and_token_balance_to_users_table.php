<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'wallet_address')) {
                $table->string('wallet_address')->nullable()->after('experience');
            }
            if (! Schema::hasColumn('users', 'token_balance')) {
                $table->decimal('token_balance', 16, 2)->default(0)->after('wallet_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'token_balance')) {
                $table->dropColumn('token_balance');
            }
            if (Schema::hasColumn('users', 'wallet_address')) {
                $table->dropColumn('wallet_address');
            }
        });
    }
};

?>



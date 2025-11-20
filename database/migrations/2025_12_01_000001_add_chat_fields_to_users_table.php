<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('name');
            $table->string('status')->default('online')->after('avatar'); // online, offline, busy
            $table->string('receipt_screenshot')->nullable()->after('status'); // Linked receipt from chat
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'status', 'receipt_screenshot']);
        });
    }
};


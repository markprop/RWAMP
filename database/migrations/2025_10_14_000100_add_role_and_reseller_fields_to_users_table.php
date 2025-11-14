<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('investor')->after('password');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'investment_capacity')) {
                $table->string('investment_capacity')->nullable()->after('company_name');
            }
            if (! Schema::hasColumn('users', 'experience')) {
                $table->text('experience')->nullable()->after('investment_capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'experience')) $table->dropColumn('experience');
            if (Schema::hasColumn('users', 'investment_capacity')) $table->dropColumn('investment_capacity');
            if (Schema::hasColumn('users', 'company_name')) $table->dropColumn('company_name');
            if (Schema::hasColumn('users', 'phone')) $table->dropColumn('phone');
            if (Schema::hasColumn('users', 'role')) $table->dropColumn('role');
        });
    }
};



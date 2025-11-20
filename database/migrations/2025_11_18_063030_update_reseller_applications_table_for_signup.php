<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            // Add password field for reseller applications
            $table->string('password')->after('phone');
            // Add experience field
            $table->text('experience')->nullable()->after('investment_capacity');
        });

        // Change investment_capacity from enum to string using raw SQL
        DB::statement("ALTER TABLE reseller_applications MODIFY investment_capacity VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert investment_capacity back to enum using raw SQL
        DB::statement("ALTER TABLE reseller_applications MODIFY investment_capacity ENUM('1-10k', '10-50k', '50-100k', '100k+') NOT NULL");

        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->dropColumn(['password', 'experience']);
        });
    }
};

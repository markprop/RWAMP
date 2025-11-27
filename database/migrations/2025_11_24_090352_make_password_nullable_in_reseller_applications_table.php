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
        // Use raw SQL to modify the column since change() might not work for all database drivers
        DB::statement('ALTER TABLE reseller_applications MODIFY password VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Making it non-nullable again might fail if there are null values
        // In production, you'd want to set default values first
        DB::statement('ALTER TABLE reseller_applications MODIFY password VARCHAR(255) NOT NULL');
    }
};

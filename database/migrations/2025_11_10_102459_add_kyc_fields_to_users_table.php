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
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_status')->default('not_started')->after('role'); // not_started, pending, approved, rejected
            $table->string('kyc_id_type')->nullable()->after('kyc_status'); // cnic, nicop, passport
            $table->string('kyc_id_number')->nullable()->after('kyc_id_type');
            $table->string('kyc_full_name')->nullable()->after('kyc_id_number');
            $table->string('kyc_id_front_path')->nullable()->after('kyc_full_name'); // ID photo
            $table->string('kyc_id_back_path')->nullable()->after('kyc_id_front_path');  // (optional for CNIC)
            $table->string('kyc_selfie_path')->nullable()->after('kyc_id_back_path');   // Selfie with ID
            $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_selfie_path');
            $table->timestamp('kyc_approved_at')->nullable()->after('kyc_submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'kyc_id_type',
                'kyc_id_number',
                'kyc_full_name',
                'kyc_id_front_path',
                'kyc_id_back_path',
                'kyc_selfie_path',
                'kyc_submitted_at',
                'kyc_approved_at',
            ]);
        });
    }
};

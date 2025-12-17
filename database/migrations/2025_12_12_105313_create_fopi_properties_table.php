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
        Schema::create('fopi_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('user_game_sessions')->onDelete('cascade');
            $table->string('property_id', 50); // e.g., 'pk1', 'ae1'
            $table->string('name');
            $table->string('region', 10); // PK, AE, INTL, CLUB
            $table->enum('status', ['READY', 'UC'])->default('READY');
            $table->decimal('price_per_sqft', 20, 8);
            $table->decimal('annual_yield_pct', 8, 4)->default(0);
            $table->decimal('annual_appreciation_pct', 8, 4)->default(0);
            $table->integer('handover_month')->default(0);
            $table->integer('original_handover')->default(0);
            $table->json('price_history')->nullable();
            $table->decimal('delay_risk_pct', 8, 4)->nullable();
            $table->integer('max_delay_months')->nullable();
            $table->boolean('has_uc_yield')->default(false);
            $table->decimal('uc_annual_yield_pct', 8, 4)->default(0);
            $table->decimal('min_rwamp', 20, 8)->default(0);
            $table->decimal('base_fair_value', 20, 8)->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fopi_properties');
    }
};

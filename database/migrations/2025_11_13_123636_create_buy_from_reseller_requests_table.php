<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buy_from_reseller_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reseller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('coin_quantity', 16, 2);
            $table->decimal('coin_price', 10, 2)->comment('Price per coin at time of request');
            $table->decimal('total_amount', 16, 2)->comment('Total amount to pay');
            $table->string('status')->default('pending')->comment('pending, approved, rejected, completed');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['reseller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buy_from_reseller_requests');
    }
};

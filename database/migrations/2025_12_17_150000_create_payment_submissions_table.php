<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->enum('recipient_type', ['admin', 'reseller'])->default('admin');

            $table->decimal('token_amount', 18, 8);
            $table->decimal('fiat_amount', 18, 2);
            $table->string('currency', 10)->default('PKR');

            $table->string('bank_name')->nullable();
            $table->string('account_last4', 10)->nullable();
            $table->string('bank_reference')->nullable();

            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
    }
};


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
        Schema::dropIfExists('crypto_payments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('crypto_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token_amount');
            $table->string('usd_amount');
            $table->string('pkr_amount');
            $table->enum('network', ['TRC20', 'ERC20', 'BTC']);
            $table->string('tx_hash');
            $table->string('screenshot')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_crypto_tx', function (Blueprint $table) {
            $table->id();
            $table->string('tx_hash')->unique();
            $table->string('network');
            $table->decimal('amount_usd', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_crypto_tx');
    }
};

?>




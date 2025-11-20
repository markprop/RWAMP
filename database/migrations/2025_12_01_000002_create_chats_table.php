<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator
            $table->string('type')->default('private'); // 'private', 'group'
            $table->string('name')->nullable(); // For groups
            $table->json('participants')->nullable(); // [user_id, ...] for quick lookup
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('type');
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('last_read_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->timestamps();
            
            $table->unique(['chat_id', 'user_id']);
            $table->index('user_id');
            $table->index('chat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
};


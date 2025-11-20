<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->string('media_type')->nullable(); // 'image', 'file', 'location', 'receipt', 'voice'
            $table->string('media_path')->nullable();
            $table->string('media_name')->nullable(); // Original filename
            $table->integer('media_size')->nullable(); // File size in bytes
            $table->json('location_data')->nullable(); // {lat, lng, address}
            $table->boolean('is_deleted')->default(false); // Soft delete flag
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index('chat_id');
            $table->index('sender_id');
            $table->index('created_at');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};


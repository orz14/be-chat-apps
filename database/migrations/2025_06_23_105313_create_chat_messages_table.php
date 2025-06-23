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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->char('room_id', 24)->index();
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreignId('sender_id')->constrained('users');
            $table->enum('type', ['text', 'image', 'file']);
            $table->text('content');
            $table->timestamp('sent_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};

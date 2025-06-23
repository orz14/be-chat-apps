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
        Schema::create('room_details', function (Blueprint $table) {
            $table->char('room_id', 24)->unique();
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreignId('owner_id')->constrained('users');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('display_picture')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_details');
    }
};

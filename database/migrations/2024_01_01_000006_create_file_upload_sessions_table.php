<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_upload_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('disk');
            $table->string('path');
            $table->uuid('upload_id')->index();
            $table->integer('total_chunks');
            $table->integer('received_chunks')->default(0);
            $table->unsignedBigInteger('size');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_upload_sessions');
    }
};

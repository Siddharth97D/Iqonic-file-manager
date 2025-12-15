<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('disk');
            $table->string('path');
            $table->string('basename');
            $table->string('extension');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('hash')->nullable()->index();
            $table->enum('visibility', ['private', 'shared', 'public'])->default('private');
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};

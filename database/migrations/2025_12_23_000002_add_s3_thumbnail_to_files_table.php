<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('s3_thumbnail_path')->nullable()->after('s3_url');
            $table->text('s3_thumbnail_url')->nullable()->after('s3_thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['s3_thumbnail_path', 's3_thumbnail_url']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('context', 50)->index();
            $table->string('slot', 50)->nullable()->index();
            $table->string('visibility', 20)->default('private')->index();
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('checksum', 64)->index();
            $table->binary('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};

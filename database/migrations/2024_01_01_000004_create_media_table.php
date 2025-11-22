<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('alt_text')->nullable();
            $table->string('folder')->nullable();
            $table->json('thumbnails')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('folder');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

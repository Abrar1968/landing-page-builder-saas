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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
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
            $table->index('mime_type');
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'folder']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

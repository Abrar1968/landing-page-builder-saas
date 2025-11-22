<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->json('content');
            $table->unsignedInteger('version_number');
            $table->timestamps();

            $table->index('page_id');
            $table->unique(['page_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_versions');
    }
};

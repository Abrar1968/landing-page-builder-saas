<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('form_id')->nullable();
            $table->json('data');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('form_id');
            $table->index('is_read');
            $table->index(['page_id', 'created_at']);
            $table->index(['page_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};

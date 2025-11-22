<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->string('domain')->unique();
            $table->enum('status', ['pending', 'verified', 'failed'])->default('pending');
            $table->enum('ssl_status', ['pending', 'active', 'expired'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->string('visitor_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('device_type')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('page_id');
            $table->index('created_at');
            $table->index('visitor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};

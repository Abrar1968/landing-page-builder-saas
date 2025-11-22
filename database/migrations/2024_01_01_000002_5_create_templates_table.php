<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('template_categories')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('preview_image')->nullable();
            $table->json('content');
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_public')->default(true);
            $table->integer('usage_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('is_featured');
            $table->index('is_public');
            $table->index(['is_public', 'is_featured']);
            $table->index('created_at');
        });

        // Pivot table for template tags
        Schema::create('template_tag', function (Blueprint $table) {
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_tag_id')->constrained('template_tags')->cascadeOnDelete();
            $table->primary(['template_id', 'template_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_tag');
        Schema::dropIfExists('templates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('template_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('template_tag', function (Blueprint $table) {
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_tag_id')->constrained('template_tags')->onDelete('cascade');
            $table->primary(['template_id', 'template_tag_id']);
        });

        // Add new columns to templates table
        Schema::table('templates', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('template_categories')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->string('slug')->unique()->after('name');
            $table->string('preview_image')->nullable()->after('thumbnail');
            $table->json('settings')->nullable()->after('content');
            $table->json('meta')->nullable()->after('settings');
            $table->boolean('is_premium')->default(false)->after('is_featured');
            $table->boolean('is_public')->default(true)->after('is_premium');
            $table->integer('usage_count')->default(0)->after('is_public');
            $table->decimal('rating', 3, 2)->default(0)->after('usage_count');
            $table->integer('rating_count')->default(0)->after('rating');
            $table->softDeletes();

            // Remove old columns that are being replaced
            $table->dropColumn(['is_system', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'category_id', 'user_id', 'slug', 'preview_image',
                'settings', 'meta', 'is_premium', 'is_public',
                'usage_count', 'rating', 'rating_count', 'deleted_at'
            ]);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_published')->default(false);
        });

        Schema::dropIfExists('template_tag');
        Schema::dropIfExists('template_tags');
        Schema::dropIfExists('template_categories');
    }
};

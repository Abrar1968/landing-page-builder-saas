<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pages indexes
        Schema::table('pages', function (Blueprint $table) {
            $table->index(['user_id', 'updated_at']);
            $table->index(['status', 'slug']);
            $table->index('created_at');
        });

        // Templates indexes
        Schema::table('templates', function (Blueprint $table) {
            $table->index(['is_public', 'is_featured']);
            $table->index(['category_id', 'is_public']);
            $table->index('created_at');
        });

        // Media indexes
        Schema::table('media', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index('mime_type');
        });

        // Domains indexes
        Schema::table('domains', function (Blueprint $table) {
            $table->index(['user_id', 'is_verified']);
            $table->index('domain');
        });

        // Subscriptions indexes
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index('stripe_subscription_id');
        });

        // Form submissions indexes
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->index(['page_id', 'created_at']);
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'updated_at']);
            $table->dropIndex(['status', 'slug']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->dropIndex(['is_public', 'is_featured']);
            $table->dropIndex(['category_id', 'is_public']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['mime_type']);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_verified']);
            $table->dropIndex(['domain']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['stripe_subscription_id']);
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropIndex(['page_id', 'created_at']);
            $table->dropIndex(['is_read']);
        });
    }
};

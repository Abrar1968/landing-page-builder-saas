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
            $table->index(['is_published', 'slug']);
            $table->index('created_at');
        });

        // Templates indexes
        Schema::table('templates', function (Blueprint $table) {
            $table->index(['is_public', 'is_featured']);
            $table->index(['category', 'is_public']);
            $table->index('created_at');
        });

        // Media indexes
        Schema::table('media', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index('type');
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
            $table->dropIndex(['is_published', 'slug']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->dropIndex(['is_public', 'is_featured']);
            $table->dropIndex(['category', 'is_public']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['type']);
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

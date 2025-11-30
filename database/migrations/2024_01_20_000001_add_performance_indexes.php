<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pages indexes - skip as they already exist in create_pages_table migration
        // Schema::table('pages', function (Blueprint $table) {
        //     $table->index(['user_id', 'updated_at']);
        //     $table->index(['status', 'slug']);
        //     $table->index('created_at');
        // });

        // Templates indexes - skip is_public/is_featured/created_at as they already exist
        Schema::table('templates', function (Blueprint $table) {
            // $table->index(['is_public', 'is_featured']); // Already exists in create_templates_table
            $table->index(['category_id', 'is_public']);
            // $table->index('created_at'); // Already exists in create_templates_table
        });

        // Media indexes - both already exist in create_media_table
        Schema::table('media', function (Blueprint $table) {
            // $table->index(['user_id', 'created_at']); // Already exists
            // $table->index('mime_type'); // Already exists
        });

        // Domains indexes - is_verified column doesn't exist, table has 'status' column instead
        Schema::table('domains', function (Blueprint $table) {
            // $table->index(['user_id', 'is_verified']); // Column doesn't exist - table uses 'status' enum
            // domain is already unique constraint, no need for index
        });

        // Subscriptions indexes - user_id/status already exists, stripe_subscription_id already unique
        Schema::table('subscriptions', function (Blueprint $table) {
            // $table->index(['user_id', 'status']); // Already exists
            // $table->index('stripe_subscription_id'); // Already unique constraint
        });

        // Form submissions indexes - both already exist in create_form_submissions_table
        Schema::table('form_submissions', function (Blueprint $table) {
            // $table->index(['page_id', 'created_at']); // Already exists
            // $table->index('is_read'); // Already exists
        });
    }

    public function down(): void
    {
        // Pages indexes - skip as they are part of create_pages_table migration
        // Schema::table('pages', function (Blueprint $table) {
        //     $table->dropIndex(['user_id', 'updated_at']);
        //     $table->dropIndex(['status', 'slug']);
        //     $table->dropIndex(['created_at']);
        // });

        // Templates indexes - skip is_public/is_featured/created_at as they are from original table
        Schema::table('templates', function (Blueprint $table) {
            // $table->dropIndex(['is_public', 'is_featured']); // Part of create_templates_table
            $table->dropIndex(['category_id', 'is_public']);
            // $table->dropIndex(['created_at']); // Part of create_templates_table
        });

        // Media indexes - both are from original table
        Schema::table('media', function (Blueprint $table) {
            // $table->dropIndex(['user_id', 'created_at']); // Part of create_media_table
            // $table->dropIndex(['mime_type']); // Part of create_media_table
        });

        // Domains indexes - only user_id/is_verified was supposed to be added, but column doesn't exist
        Schema::table('domains', function (Blueprint $table) {
            // $table->dropIndex(['user_id', 'is_verified']); // Column doesn't exist
            // $table->dropIndex(['domain']); // Unique constraint, not index
        });

        // Subscriptions indexes - both are from original table
        Schema::table('subscriptions', function (Blueprint $table) {
            // $table->dropIndex(['user_id', 'status']); // Part of create_subscriptions_table
            // $table->dropIndex(['stripe_subscription_id']); // Unique constraint
        });

        // Form submissions indexes - both are from original table
        Schema::table('form_submissions', function (Blueprint $table) {
            // $table->dropIndex(['page_id', 'created_at']); // Part of create_form_submissions_table
            // $table->dropIndex(['is_read']); // Part of create_form_submissions_table
        });
    }
};

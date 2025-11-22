<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->enum('role', ['user', 'admin'])->default('user')->after('avatar');
            $table->string('timezone')->default('UTC')->after('role');
            $table->string('stripe_customer_id')->nullable()->unique()->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('stripe_customer_id');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['avatar', 'role', 'timezone', 'stripe_customer_id', 'last_login_at']);
        });
    }
};

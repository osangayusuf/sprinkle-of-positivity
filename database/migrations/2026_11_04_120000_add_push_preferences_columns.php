<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('push_chat_enabled')->default(true);
            $table->boolean('push_insights_enabled')->default(true);
        });

        Schema::table('group_user', function (Blueprint $table) {
            $table->boolean('notifications_muted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['push_chat_enabled', 'push_insights_enabled']);
        });

        Schema::table('group_user', function (Blueprint $table) {
            $table->dropColumn('notifications_muted');
        });
    }
};

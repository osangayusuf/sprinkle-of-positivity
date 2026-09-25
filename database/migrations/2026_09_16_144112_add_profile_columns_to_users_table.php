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
            $table->string('whatsapp_number')->after('email')->nullable();
            // Only day/month are collected (see onboarding design) — no
            // birth year — so this can't be a single `date` column.
            $table->unsignedTinyInteger('birthday_day')->after('whatsapp_number')->nullable();
            $table->unsignedTinyInteger('birthday_month')->after('birthday_day')->nullable();
            $table->json('goals')->after('birthday_month')->nullable();
            $table->unsignedInteger('points')->after('goals')->default(0);
            $table->timestamp('onboarding_completed_at')->after('points')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number',
                'birthday_day',
                'birthday_month',
                'goals',
                'points',
                'onboarding_completed_at',
            ]);
        });
    }
};

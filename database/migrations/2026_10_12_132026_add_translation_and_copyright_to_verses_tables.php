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
        foreach (['daily_verses', 'group_verses'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('translation', 32)->nullable()->after('text');
                $table->string('copyright')->nullable()->after('translation');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['daily_verses', 'group_verses'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['translation', 'copyright']);
            });
        }
    }
};

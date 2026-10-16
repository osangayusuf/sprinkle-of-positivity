<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                $table->text('text')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['daily_verses', 'group_verses'] as $table) {
            DB::table($table)->whereNull('text')->update(['text' => '']);

            Schema::table($table, function (Blueprint $table) {
                $table->text('text')->nullable(false)->change();
            });
        }
    }
};

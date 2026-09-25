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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_verse_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            // No FK constraint: quiz_options doesn't exist yet when this table
            // is created, and the two tables would otherwise reference each
            // other in a cycle.
            $table->unsignedBigInteger('correct_quiz_option_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};

<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'group_verse_id' => GroupVerse::factory(),
            'question' => fake()->sentence().'?',
            'created_by' => User::factory(),
        ];
    }
}

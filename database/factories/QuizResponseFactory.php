<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizResponse>
 */
class QuizResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory(),
            'quiz_option_id' => QuizOption::factory(),
        ];
    }
}

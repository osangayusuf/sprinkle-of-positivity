<?php

namespace Database\Factories;

use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insight>
 */
class InsightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'verseable_id' => GroupVerse::factory(),
            'verseable_type' => GroupVerse::class,
            'body' => fake()->paragraph(),
        ];
    }
}

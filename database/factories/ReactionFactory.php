<?php

namespace Database\Factories;

use App\Models\Insight;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reaction>
 */
class ReactionFactory extends Factory
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
            'reactable_id' => Insight::factory(),
            'reactable_type' => Insight::class,
            'emoji' => fake()->randomElement(Reaction::EMOJIS),
        ];
    }
}

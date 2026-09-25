<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'purpose' => fake()->paragraph(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the group runs a fixed-length daily challenge starting
     * today.
     */
    public function challenge(int $days = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_days' => $days,
            'starts_on' => today(),
        ]);
    }
}

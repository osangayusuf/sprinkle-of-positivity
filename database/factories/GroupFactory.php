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
        $name = rtrim(fake()->unique()->sentence(3, false), '.');

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'purpose' => fake()->paragraph(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that only approved members can read the group's verses and
     * insights.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
        ]);
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

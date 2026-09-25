<?php

namespace Database\Factories;

use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplaceListing>
 */
class MarketplaceListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->company().' Sale',
            'description' => fake()->sentence(),
            'cta_label' => 'Read More',
            'cta_url' => fake()->url(),
            'position' => 0,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the listing is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}

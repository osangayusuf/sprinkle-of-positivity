<?php

namespace Database\Factories;

use App\Models\BirthdayWish;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BirthdayWish>
 */
class BirthdayWishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'recipient_id' => User::factory(),
            'year' => today()->year,
        ];
    }
}

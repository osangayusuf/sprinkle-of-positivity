<?php

namespace Database\Factories;

use App\Models\DailyVerse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyVerse>
 */
class DailyVerseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => today(),
            'reference' => 'Joshua 2:1',
            'text' => 'Then Joshua son of Nun secretly sent two spies from Shittim, saying, "Go, look over the land, especially Jericho."',
            'created_by' => User::factory(),
        ];
    }
}

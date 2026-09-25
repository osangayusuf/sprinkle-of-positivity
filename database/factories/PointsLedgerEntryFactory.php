<?php

namespace Database\Factories;

use App\Models\PointsLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointsLedgerEntry>
 */
class PointsLedgerEntryFactory extends Factory
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
            'points' => 10,
            'reason' => PointsLedgerEntry::REASON_INSIGHT_SUBMITTED,
        ];
    }
}

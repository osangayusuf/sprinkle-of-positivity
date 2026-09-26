<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
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
            'group_id' => Group::factory(),
            'code' => Certificate::generateCode(),
            'recipient_name' => fake()->name(),
            'duration_days' => 60,
            'year' => (int) now()->format('Y'),
            'issued_at' => now(),
        ];
    }

    /**
     * Indicate that the certificate has been revoked by an admin.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now(),
            'revoked_by' => User::factory(),
            'revoke_reason' => 'Issued in error.',
        ]);
    }
}

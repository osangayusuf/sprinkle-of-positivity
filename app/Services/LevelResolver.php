<?php

namespace App\Services;

class LevelResolver
{
    /**
     * The highest level tier a given points total has reached, or null if
     * the user hasn't hit the first tier's threshold yet.
     *
     * @return array{key: string, label: string, threshold: int}|null
     */
    public function forPoints(int $points): ?array
    {
        $reached = null;

        foreach ($this->all() as $level) {
            if ($points >= $level['threshold']) {
                $reached = $level;
            }
        }

        return $reached;
    }

    /**
     * All level tiers, lowest first.
     *
     * @return array<int, array{key: string, label: string, threshold: int}>
     */
    public function all(): array
    {
        return config('levels');
    }
}

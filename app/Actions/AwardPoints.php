<?php

namespace App\Actions;

use App\Models\PointsLedgerEntry;
use App\Models\User;
use App\Services\LevelResolver;
use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;

class AwardPoints
{
    public function __construct(private LevelResolver $levels) {}

    /**
     * Record a point award and bump the user's cached counter — the only
     * place points are ever written. Flashes a celebration prop when the
     * award crosses the user into a new level tier.
     */
    public function handle(User $user, int $points, string $reason, ?Model $reference = null): PointsLedgerEntry
    {
        $levelBefore = $this->levels->forPoints($user->points);

        $entry = new PointsLedgerEntry(['points' => $points, 'reason' => $reason]);
        $entry->user_id = $user->id;

        if ($reference) {
            $entry->reference_id = $reference->getKey();
            $entry->reference_type = $reference->getMorphClass();
        }

        $entry->save();

        $user->increment('points', $points);

        $levelAfter = $this->levels->forPoints($user->points);

        if ($levelAfter && $levelAfter['key'] !== ($levelBefore['key'] ?? null)) {
            Inertia::flash('celebration', [
                'type' => 'level_up',
                'title' => 'New Level Unlocked!',
                'body' => $levelAfter['label'],
            ]);
        }

        return $entry;
    }
}

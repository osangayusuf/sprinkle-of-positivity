<?php

namespace App\Concerns;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Reactable
{
    /**
     * @return MorphMany<Reaction, $this>
     */
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * A compact summary for feed/thread display: the total reaction count
     * plus up to 3 distinct emoji currently in use, most common first.
     *
     * @return array{emojis: array<int, string>, count: int}
     */
    public function reactionSummary(): array
    {
        $reactions = $this->relationLoaded('reactions') ? $this->reactions : $this->reactions()->get();

        return [
            'emojis' => $reactions->countBy('emoji')->sortDesc()->keys()->take(3)->values()->all(),
            'count' => $reactions->count(),
        ];
    }
}

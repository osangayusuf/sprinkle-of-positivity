<?php

namespace App\Actions;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ToggleReaction
{
    /**
     * Set the user's reaction on the given item. Picking the same emoji
     * again removes it; picking a different one replaces it — a user only
     * ever has one active reaction per item.
     */
    public function handle(Model $reactable, User $user, string $emoji): ?Reaction
    {
        $existing = Reaction::query()
            ->where('user_id', $user->id)
            ->where('reactable_type', $reactable->getMorphClass())
            ->where('reactable_id', $reactable->getKey())
            ->first();

        if ($existing && $existing->emoji === $emoji) {
            $existing->delete();

            return null;
        }

        $reaction = $existing ?? new Reaction;
        $reaction->user_id = $user->id;
        $reaction->reactable_id = $reactable->getKey();
        $reaction->reactable_type = $reactable->getMorphClass();
        $reaction->emoji = $emoji;
        $reaction->save();

        return $reaction;
    }
}

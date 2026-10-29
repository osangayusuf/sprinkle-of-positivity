<?php

namespace App\Actions;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ResolveMentionedMembers
{
    /**
     * Work out who a chat message or comment tags. Tokens (`@[id]`) count only
     * for approved members of the group other than the author. `@everyone`
     * notifies every approved member but the author without recording each of
     * them as an individual mention.
     *
     * @return array{mentioned: Collection<int, User>, notify: Collection<int, User>}
     */
    public function handle(Group $group, User $author, string $body): array
    {
        preg_match_all('/@\[(\d+)\]/', $body, $matches);

        $ids = collect($matches[1])->map(fn (string $id) => (int) $id)->unique()->all();

        $mentioned = $ids === []
            ? new Collection
            : $group->approvedMembers()->whereIn('users.id', $ids)->whereKeyNot($author->id)->get();

        $notify = preg_match('/(?<![\w@])@everyone\b/i', $body)
            ? $group->approvedMembers()->whereKeyNot($author->id)->get()
            : $mentioned;

        return ['mentioned' => $mentioned, 'notify' => $notify];
    }
}

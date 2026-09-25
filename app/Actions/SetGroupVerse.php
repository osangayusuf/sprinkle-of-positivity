<?php

namespace App\Actions;

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class SetGroupVerse
{
    /**
     * Set (or update) a group's verse for the given date — defaults to
     * today, since that's the only day a manager can ever post for.
     */
    public function handle(Group $group, User $author, string $reference, string $text, ?Carbon $date = null, ?UploadedFile $image = null): GroupVerse
    {
        $date ??= today();

        $verse = GroupVerse::query()
            ->where('group_id', $group->id)
            ->whereDate('date', $date)
            ->first() ?? new GroupVerse;

        $verse->group_id = $group->id;
        $verse->date = $date;
        $verse->reference = $reference;
        $verse->text = $text;
        $verse->created_by = $author->id;

        if ($image) {
            $verse->image_path = $image->store('group-verses', 'public');
        }

        $verse->save();

        return $verse;
    }
}

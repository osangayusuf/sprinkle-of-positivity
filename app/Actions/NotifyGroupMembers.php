<?php

namespace App\Actions;

use App\Enums\PushCategory;
use App\Models\Group;
use App\Models\User;
use App\Notifications\GroupActivityNotification;
use Illuminate\Support\Facades\Notification;

class NotifyGroupMembers
{
    /**
     * Push an alert about activity in a group to its approved members, except
     * the author, anyone in $excludeIds (already told another way, e.g. by an
     * @mention), members who muted the group, members who switched the
     * category off globally, and members with no push subscription at all.
     * When $onlyIds is given, recipients are limited to those members.
     *
     * @param  array<int, int>  $excludeIds
     * @param  array<int, int>|null  $onlyIds
     */
    public function handle(
        Group $group,
        User $author,
        PushCategory $category,
        GroupActivityNotification $notification,
        array $excludeIds = [],
        ?array $onlyIds = null,
    ): void {
        $recipients = $group->approvedMembers()
            ->whereKeyNot([$author->id, ...$excludeIds])
            ->when($onlyIds !== null, fn ($query) => $query->whereKey($onlyIds))
            ->wherePivot('notifications_muted', false)
            ->where('users.'.$category->preferenceColumn(), true)
            ->whereNull('users.disabled_at')
            ->whereHas('pushSubscriptions')
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, $notification);
    }
}

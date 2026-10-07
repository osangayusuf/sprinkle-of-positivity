<?php

namespace App\Actions;

use App\Models\GroupMembership;
use App\Models\User;
use App\Notifications\ChallengeAlert;

class ReassignParticipant
{
    /**
     * An admin hands a participant to a specific partner of the group.
     */
    public function handle(GroupMembership $membership, User $partner): GroupMembership
    {
        $membership->partner_id = $partner->id;
        $membership->save();

        $partner->notify(new ChallengeAlert(
            __('New participant assigned'),
            __(':name is now assigned to you in :group.', ['name' => $membership->user->name, 'group' => $membership->group->name]),
        ));

        return $membership;
    }
}

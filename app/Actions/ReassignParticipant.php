<?php

namespace App\Actions;

use App\Models\GroupMembership;
use App\Models\User;
use App\Notifications\ParticipantAssigned;

class ReassignParticipant
{
    /**
     * An admin hands a participant to a specific partner of the group.
     */
    public function handle(GroupMembership $membership, User $partner): GroupMembership
    {
        $membership->partner_id = $partner->id;
        $membership->save();

        $partner->notify(new ParticipantAssigned($membership->user, $membership->group));

        return $membership;
    }
}

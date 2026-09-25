<?php

namespace App\Actions;

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;

class ApplyToGroup
{
    /**
     * Create a pending membership application for the given user.
     */
    public function handle(Group $group, User $user): GroupMembership
    {
        $membership = new GroupMembership;
        $membership->group_id = $group->id;
        $membership->user_id = $user->id;
        $membership->role = GroupMembershipRole::Member;
        $membership->status = GroupMembershipStatus::Pending;
        $membership->code_of_conduct_accepted_at = now();
        $membership->applied_at = now();
        $membership->save();

        return $membership;
    }
}

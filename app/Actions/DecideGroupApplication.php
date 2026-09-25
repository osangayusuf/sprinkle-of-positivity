<?php

namespace App\Actions;

use App\Enums\GroupMembershipStatus;
use App\Models\GroupMembership;
use App\Models\User;

class DecideGroupApplication
{
    /**
     * Record a manager's decision on a membership — approve, reject, or
     * remove an already-approved member. All three are just a status
     * transition stamped with who decided and when.
     */
    public function handle(GroupMembership $membership, GroupMembershipStatus $decision, User $decidedBy): GroupMembership
    {
        $membership->status = $decision;
        $membership->decided_at = now();
        $membership->decided_by = $decidedBy->id;
        $membership->save();

        return $membership;
    }
}

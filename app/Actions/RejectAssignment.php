<?php

namespace App\Actions;

use App\Models\GroupMembership;
use App\Models\User;

class RejectAssignment
{
    public function __construct(private AssignParticipant $assignParticipant) {}

    /**
     * A partner declines a participant, who moves to the next least-loaded
     * partner in the group. Without another partner they await an admin.
     */
    public function handle(GroupMembership $membership, User $decliningPartner): ?User
    {
        return $this->assignParticipant->handle($membership, [$decliningPartner->id]);
    }
}

<?php

namespace App\Policies;

use App\Enums\GroupMembershipStatus;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;

class GroupPolicy
{
    /**
     * Anyone, including guests, can browse group listings.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Group details (purpose, members, join/manage) are visible to anyone,
     * including guests and users who haven't joined yet — matches the
     * "preview before joining" screen in the design. Private groups are
     * still listed; only their content is restricted (see viewContent).
     */
    public function view(?User $user, Group $group): bool
    {
        return true;
    }

    /**
     * A group's verses, insights, comments, and quizzes are readable by
     * anyone, including guests, unless the group is private — then only
     * approved members and platform admins can read them.
     */
    public function viewContent(?User $user, Group $group): bool
    {
        if (! $group->is_private) {
            return true;
        }

        return $user !== null && $this->participate($user, $group);
    }

    /**
     * Only platform admins create groups.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(Role::ADMIN);
    }

    /**
     * Only platform admins edit a group's own fields (name, purpose, cover
     * image, duration, start date, status).
     */
    public function update(User $user): bool
    {
        return $user->hasRole(Role::ADMIN);
    }

    /**
     * Only platform admins delete a group.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(Role::ADMIN);
    }

    /**
     * A user can apply to join if they don't already have a membership
     * record (pending, approved, or otherwise) for this group.
     */
    public function join(User $user, Group $group): bool
    {
        return ! $user->groupMemberships->contains('group_id', $group->id);
    }

    /**
     * Manage covers approving/rejecting/removing members, setting the daily
     * verse, and posting quizzes — the group's manager, or a platform admin.
     */
    public function manage(User $user, Group $group): bool
    {
        return $user->isManagerOf($group) || $user->hasRole(Role::ADMIN);
    }

    /**
     * Sharing insights, commenting, reacting, and answering quizzes are all
     * gated the same way: an approved member (of any role) of the group, or
     * a platform admin.
     */
    public function participate(User $user, Group $group): bool
    {
        if ($user->hasRole(Role::ADMIN)) {
            return true;
        }

        return $user->groupMemberships->contains(
            fn ($membership) => $membership->group_id === $group->id
                && $membership->status === GroupMembershipStatus::Approved
        );
    }
}

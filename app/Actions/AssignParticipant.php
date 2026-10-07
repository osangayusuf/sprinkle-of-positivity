<?php

namespace App\Actions;

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Models\GroupMembership;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;

class AssignParticipant
{
    /**
     * Assign a participant to the group's accountability partner who looks
     * after the fewest participants, notifying them. Without a partner to
     * pick, the participant stays unassigned and admins are alerted.
     *
     * `$excluding` skips partners who already declined this participant.
     *
     * @param  array<int, int>  $excluding
     */
    public function handle(GroupMembership $membership, array $excluding = []): ?User
    {
        $partner = $this->leastLoadedPartner($membership, $excluding);

        $membership->partner_id = $partner?->id;
        $membership->save();

        $participant = $membership->user;
        $group = $membership->group;

        if ($partner) {
            $partner->notify(new ChallengeAlert(
                __('New participant assigned'),
                __(':name is now assigned to you in :group.', ['name' => $participant->name, 'group' => $group->name]),
            ));

            return $partner;
        }

        Notification::send(
            User::query()->whereHas('roles', fn ($roles) => $roles->where('name', Role::ADMIN))->get(),
            new ChallengeAlert(
                __('Participant needs a partner'),
                __(':name in :group has no accountability partner assigned.', ['name' => $participant->name, 'group' => $group->name]),
            ),
        );

        return null;
    }

    /**
     * @param  array<int, int>  $excluding
     */
    private function leastLoadedPartner(GroupMembership $membership, array $excluding): ?User
    {
        $partners = $membership->group->approvedMembers()
            ->wherePivot('role', GroupMembershipRole::Manager->value)
            ->whereNotIn('users.id', [...$excluding, $membership->user_id])
            ->get();

        if ($partners->isEmpty()) {
            return null;
        }

        $load = GroupMembership::query()
            ->where('group_id', $membership->group_id)
            ->where('role', GroupMembershipRole::Member->value)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->whereIn('partner_id', $partners->modelKeys())
            ->selectRaw('partner_id, count(*) as total')
            ->groupBy('partner_id')
            ->pluck('total', 'partner_id');

        return $partners
            ->sortBy([fn (User $partner) => $load[$partner->id] ?? 0, fn (User $partner) => $partner->id])
            ->first();
    }
}

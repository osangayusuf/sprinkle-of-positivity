<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ReassignParticipant;
use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    /**
     * A group's participants and the partner each is assigned to.
     */
    public function index(Group $group): Response
    {
        $this->authorize('access-admin');

        $participants = $group->memberships()
            ->with('user')
            ->where('role', GroupMembershipRole::Member->value)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->get()
            ->sortBy(fn (GroupMembership $membership) => $membership->user->name)
            ->values()
            ->map(fn (GroupMembership $membership) => [
                'id' => $membership->id,
                'name' => $membership->user->name,
                'partner_id' => $membership->partner_id,
            ]);

        return Inertia::render('admin/groups/assignments', [
            'group' => ['id' => $group->id, 'name' => $group->name],
            'participants' => $participants,
            'partners' => $group->managers()->orderBy('name')->get(['users.id', 'users.name']),
        ]);
    }

    /**
     * Hand a participant to one of the group's partners.
     */
    public function update(Request $request, Group $group, GroupMembership $membership, ReassignParticipant $action): RedirectResponse
    {
        $this->authorize('access-admin');
        abort_unless($membership->group_id === $group->id && $membership->role === GroupMembershipRole::Member, 404);

        $partnerId = $request->validate(['partner_id' => ['required', 'integer']])['partner_id'];
        $partner = $group->managers()->whereKey($partnerId)->first();

        abort_if($partner === null, 422, 'That user is not a partner of this group.');

        $action->handle($membership, $partner);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Participant reassigned.')]);

        return back();
    }
}

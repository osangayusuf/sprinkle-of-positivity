<?php

namespace App\Http\Controllers;

use App\Actions\ApplyToGroup;
use App\Actions\DecideGroupApplication;
use App\Enums\GroupMembershipStatus;
use App\Http\Requests\Groups\ApplyToGroupRequest;
use App\Http\Requests\Groups\DecideGroupApplicationRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupApplicationController extends Controller
{
    /**
     * Show the code-of-conduct step before applying.
     */
    public function create(Group $group): Response
    {
        $this->authorize('join', $group);

        return Inertia::render('groups/join', [
            'group' => new GroupResource($group),
        ]);
    }

    /**
     * Apply to join a group, accepting its code of conduct.
     */
    public function store(ApplyToGroupRequest $request, Group $group, ApplyToGroup $action): RedirectResponse
    {
        $action->handle($group, $request->user());

        return to_route('groups.show', $group);
    }

    /**
     * A manager approves or rejects a pending application.
     */
    public function update(
        DecideGroupApplicationRequest $request,
        Group $group,
        GroupMembership $membership,
        DecideGroupApplication $action,
    ): RedirectResponse {
        abort_unless($membership->group_id === $group->id, 404);

        $action->handle(
            $membership,
            GroupMembershipStatus::from($request->string('decision')->value()),
            $request->user(),
        );

        return back();
    }

    /**
     * A manager removes an approved member.
     */
    public function destroy(Request $request, Group $group, GroupMembership $membership, DecideGroupApplication $action): RedirectResponse
    {
        $this->authorize('manage', $group);
        abort_unless($membership->group_id === $group->id, 404);

        $action->handle($membership, GroupMembershipStatus::Removed, $request->user());

        return back();
    }
}

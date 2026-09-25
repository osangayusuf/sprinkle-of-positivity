<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupMembershipResource;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupManagementController extends Controller
{
    /**
     * The manager's dashboard for a group: pending applications and the
     * current member list.
     */
    public function edit(Request $request, Group $group): Response
    {
        $this->authorize('manage', $group);

        return Inertia::render('groups/manage', [
            'group' => new GroupResource($group),
            'pendingApplications' => GroupMembershipResource::collection(
                $group->pendingApplications()->get()
            ),
            'members' => GroupMembershipResource::collection(
                $group->approvedMembers()->get()
            ),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\GroupStatus;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    /**
     * List the user's own groups and other groups they can discover/join.
     * Guests see every active group under "discover".
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $myGroupIds = $user?->groupMemberships->pluck('group_id') ?? collect();

        $discoverGroups = Group::query()
            ->where('status', GroupStatus::Active)
            ->whereNotIn('id', $myGroupIds)
            ->withCount('approvedMembers')
            ->latest()
            ->get();

        return Inertia::render('groups/index', [
            'myGroups' => GroupResource::collection(
                $user?->approvedGroups()->withCount('approvedMembers')->get() ?? collect()
            ),
            'discoverGroups' => GroupResource::collection($discoverGroups),
        ]);
    }

    /**
     * Show a group's purpose, member preview, and the member's own
     * relationship to it (none / pending / approved / manager).
     */
    public function show(Request $request, Group $group): Response
    {
        $this->authorize('view', $group);

        $user = $request->user();
        $membership = $user?->groupMemberships->firstWhere('group_id', $group->id);

        return Inertia::render('groups/show', [
            'group' => new GroupResource($group->loadCount('approvedMembers')),
            'membership' => $membership ? [
                'role' => $membership->role->value,
                'status' => $membership->status->value,
            ] : null,
            'canManage' => $user?->can('manage', $group) ?? false,
            'canViewContent' => Gate::forUser($user)->allows('viewContent', $group),
        ]);
    }
}

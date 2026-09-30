<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Enums\GroupStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Http\Requests\Admin\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    /**
     * List every group on the platform.
     */
    public function index(): Response
    {
        $this->authorize('create', Group::class);

        return Inertia::render('admin/groups/index', [
            'groups' => GroupResource::collection(
                Group::query()->withCount('approvedMembers')->latest()->get()
            ),
        ]);
    }

    /**
     * Show the group-creation form.
     */
    public function create(): Response
    {
        $this->authorize('create', Group::class);

        return Inertia::render('admin/groups/create', [
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Create a group and appoint its manager.
     */
    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $group = new Group($request->safe()->only(['name', 'purpose', 'duration_days', 'starts_on']));
        $group->slug = $this->uniqueSlug($request->string('name')->value());
        $group->created_by = $request->user()->id;
        $group->is_private = $request->boolean('is_private');

        if ($request->hasFile('cover_image')) {
            $group->cover_image_path = $request->file('cover_image')->store('groups', 'public');
        }

        $group->save();

        $membership = new GroupMembership;
        $membership->group_id = $group->id;
        $membership->user_id = $request->integer('manager_id');
        $membership->role = GroupMembershipRole::Manager;
        $membership->status = GroupMembershipStatus::Approved;
        $membership->applied_at = now();
        $membership->decided_at = now();
        $membership->decided_by = $request->user()->id;
        $membership->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Group created.')]);

        return to_route('admin.groups.index');
    }

    /**
     * Show the group-edit form.
     */
    public function edit(Group $group): Response
    {
        $this->authorize('update', $group);

        return Inertia::render('admin/groups/edit', [
            'group' => new GroupResource($group),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'managerId' => $group->managers()->value('users.id'),
        ]);
    }

    /**
     * Update a group's own fields, reassigning its manager if changed.
     */
    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $group->fill($request->safe()->only(['name', 'purpose', 'duration_days', 'starts_on']));
        $group->status = $request->enum('status', GroupStatus::class);
        $group->is_private = $request->boolean('is_private');

        if ($request->hasFile('cover_image')) {
            $group->cover_image_path = $request->file('cover_image')->store('groups', 'public');
        }

        $group->save();

        $this->reassignManager($group, $request->integer('manager_id'), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Group updated.')]);

        return to_route('admin.groups.index');
    }

    /**
     * Move the "manager" role to the given user, demoting the current
     * manager to a regular member. A no-op if they're already the manager.
     */
    private function reassignManager(Group $group, int $managerId, User $actingAdmin): void
    {
        $currentManager = $group->managers()->first();

        if ($currentManager && $currentManager->id === $managerId) {
            return;
        }

        if ($currentManager) {
            $currentManager->pivot->role = GroupMembershipRole::Member;
            $currentManager->pivot->save();
        }

        $newManagerMembership = $group->memberships()->where('user_id', $managerId)->first();

        if ($newManagerMembership) {
            $newManagerMembership->role = GroupMembershipRole::Manager;
            $newManagerMembership->status = GroupMembershipStatus::Approved;
            $newManagerMembership->save();

            return;
        }

        $membership = new GroupMembership;
        $membership->group_id = $group->id;
        $membership->user_id = $managerId;
        $membership->role = GroupMembershipRole::Manager;
        $membership->status = GroupMembershipStatus::Approved;
        $membership->applied_at = now();
        $membership->decided_at = now();
        $membership->decided_by = $actingAdmin->id;
        $membership->save();
    }

    /**
     * Delete a group and everything scoped to it.
     */
    public function destroy(Group $group): RedirectResponse
    {
        $this->authorize('delete', $group);

        $group->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Group deleted.')]);

        return back();
    }

    /**
     * Generate a unique, URL-safe slug from the group's name.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Group::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}

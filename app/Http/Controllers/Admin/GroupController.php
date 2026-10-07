<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AssignParticipant;
use App\Actions\StorePublicUpload;
use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Enums\GroupStatus;
use App\Enums\PartnerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Http\Requests\Admin\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function __construct(private AssignParticipant $assignParticipant) {}

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
            'users' => $this->approvedPartners(),
        ]);
    }

    /**
     * Create a group and appoint its accountability partners.
     */
    public function store(StoreGroupRequest $request, StorePublicUpload $storePublicUpload): RedirectResponse
    {
        $group = new Group($request->safe()->only(['name', 'purpose', 'duration_days', 'starts_on']));
        $group->slug = $this->uniqueSlug($request->string('name')->value());
        $group->created_by = $request->user()->id;
        $group->is_private = $request->boolean('is_private');

        if ($request->hasFile('cover_image')) {
            $group->cover_image_path = $storePublicUpload->handle($request->file('cover_image'), 'groups');
        }

        $group->save();

        $this->syncManagers($group, $request->array('manager_ids'), $request->user());

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
            'users' => $this->approvedPartners(),
            'managerIds' => $group->managers()->pluck('users.id'),
        ]);
    }

    /**
     * Update a group's own fields, reassigning its manager if changed.
     */
    public function update(UpdateGroupRequest $request, Group $group, StorePublicUpload $storePublicUpload): RedirectResponse
    {
        $group->fill($request->safe()->only(['name', 'purpose', 'duration_days', 'starts_on']));
        $group->status = $request->enum('status', GroupStatus::class);
        $group->is_private = $request->boolean('is_private');

        if ($request->hasFile('cover_image')) {
            $group->cover_image_path = $storePublicUpload->handle($request->file('cover_image'), 'groups');
        }

        $group->save();

        $this->syncManagers($group, $request->array('manager_ids'), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Group updated.')]);

        return to_route('admin.groups.index');
    }

    /**
     * Make exactly the given accountability partners the group's managers.
     * Removed managers become regular members and their participants are
     * handed to the remaining partners; any unassigned participants are
     * assigned to a partner.
     *
     * @param  array<int, int|string>  $managerIds
     */
    private function syncManagers(Group $group, array $managerIds, User $actingAdmin): void
    {
        $managerIds = array_map('intval', $managerIds);

        $group->managers()->get()
            ->reject(fn (User $manager) => in_array($manager->id, $managerIds, true))
            ->each(function (User $manager) use ($group) {
                $manager->pivot->role = GroupMembershipRole::Member;
                $manager->pivot->save();

                $group->memberships()->where('partner_id', $manager->id)->update(['partner_id' => null]);
            });

        foreach ($managerIds as $managerId) {
            $membership = $group->memberships()->where('user_id', $managerId)->first() ?? new GroupMembership;

            if ($membership->exists && $membership->role === GroupMembershipRole::Manager && $membership->status === GroupMembershipStatus::Approved) {
                continue;
            }

            $membership->group_id = $group->id;
            $membership->user_id = $managerId;
            $membership->role = GroupMembershipRole::Manager;
            $membership->status = GroupMembershipStatus::Approved;
            $membership->partner_id = null;
            $membership->applied_at ??= now();
            $membership->decided_at = now();
            $membership->decided_by = $actingAdmin->id;
            $membership->save();
        }

        $group->memberships()
            ->with(['user', 'group'])
            ->where('role', GroupMembershipRole::Member->value)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->whereNull('partner_id')
            ->get()
            ->each(fn (GroupMembership $membership) => $this->assignParticipant->handle($membership));
    }

    /**
     * Accountability partners an admin has approved, the only users who can
     * manage a group.
     *
     * @return Collection<int, User>
     */
    private function approvedPartners(): Collection
    {
        return User::query()
            ->where('partner_status', PartnerStatus::Approved->value)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
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

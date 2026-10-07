<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * List every user on the platform.
     */
    public function index(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/users/index', [
            'users' => UserResource::collection(
                User::query()->with('roles')->orderBy('name')->get()
            ),
        ]);
    }

    /**
     * Promote or demote a user between the platform's global roles.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = $request->string('role')->value();

        if ($user->is($request->user()) && $role !== Role::ADMIN) {
            throw ValidationException::withMessages([
                'role' => __('You cannot remove your own admin access.'),
            ]);
        }

        if ($role !== Role::PARTNER && $user->managedGroups()->exists()) {
            throw ValidationException::withMessages([
                'role' => __('Remove this partner from the groups they manage first.'),
            ]);
        }

        $roleId = Role::query()->firstOrCreate(['name' => $role])->id;
        $user->roles()->sync([$roleId]);

        if ($role === Role::PARTNER) {
            $user->partner_status = PartnerStatus::Approved;
            $user->partner_decided_at = now();
        } else {
            $user->partner_status = null;
            $user->partner_decided_at = null;
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role updated.')]);

        return back();
    }
}

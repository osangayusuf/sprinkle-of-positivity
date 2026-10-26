<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SetUserDisabled;
use App\Enums\PartnerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Promote or demote a user between the platform's global roles. An admin
     * can also keep the accountability partner role (`also_partner`) so one
     * account does both jobs.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = $request->string('role')->value();

        if ($user->is($request->user()) && $role !== Role::ADMIN) {
            throw ValidationException::withMessages([
                'role' => __('You cannot remove your own admin access.'),
            ]);
        }

        $alsoPartner = $role === Role::ADMIN && $request->boolean('also_partner');
        $isPartner = $role === Role::PARTNER || $alsoPartner;

        if (! $isPartner && $user->managedGroups()->exists()) {
            throw ValidationException::withMessages([
                'role' => __('Remove this partner from the groups they manage first.'),
            ]);
        }

        $roleNames = $alsoPartner ? [Role::ADMIN, Role::PARTNER] : [$role];
        $user->roles()->sync(
            collect($roleNames)->map(fn (string $name) => Role::query()->firstOrCreate(['name' => $name])->id)->all()
        );

        if ($isPartner) {
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

    /**
     * Disable or re-enable a user's account. A disabled user is signed out
     * and cannot sign back in until re-enabled.
     */
    public function updateDisabled(Request $request, User $user, SetUserDisabled $action): RedirectResponse
    {
        $this->authorize('access-admin');

        $disabled = $request->validate(['disabled' => ['required', 'boolean']])['disabled'];

        if ($disabled && $user->is($request->user())) {
            throw ValidationException::withMessages([
                'disabled' => __('You cannot disable your own account.'),
            ]);
        }

        $action->handle($user, (bool) $disabled);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $disabled ? __('Account disabled.') : __('Account enabled.'),
        ]);

        return back();
    }
}

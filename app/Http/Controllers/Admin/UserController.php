<?php

namespace App\Http\Controllers\Admin;

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
     * Promote or demote a user between the platform's two global roles.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = $request->string('role')->value();

        if ($user->is($request->user()) && $role !== Role::ADMIN) {
            throw ValidationException::withMessages([
                'role' => __('You cannot remove your own admin access.'),
            ]);
        }

        $roleId = Role::query()->where('name', $role)->value('id');
        $user->roles()->sync([$roleId]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role updated.')]);

        return back();
    }
}

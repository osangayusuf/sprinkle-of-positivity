<?php

namespace App\Http\Responses;

use App\Models\Role;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * Mirrors Fortify's default LoginResponse, except admins are sent to
     * the admin dashboard instead of the member home page.
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $home = $request->user()?->hasRole(Role::ADMIN)
            ? route('admin.dashboard')
            : Fortify::redirects('login');

        return redirect()->intended($home);
    }
}

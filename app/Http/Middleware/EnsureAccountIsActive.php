<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Sign out users an admin has disabled, whether they are mid-session or
     * have just authenticated (password, passkey or two-factor).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDisabled()) {
            Auth::guard()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Your account has been disabled. Please contact an admin.'),
            ]);

            return redirect()->route('login');
        }

        return $next($request);
    }
}

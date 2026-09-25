<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingIsComplete
{
    /**
     * Redirect authenticated users who haven't finished the one-time
     * profile-completion flow back to it.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->onboarding_completed_at) {
            return redirect()->route('onboarding.edit');
        }

        return $next($request);
    }
}

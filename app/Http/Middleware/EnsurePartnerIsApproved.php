<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerIsApproved
{
    /**
     * Keep accountability partners on the waiting screen until an admin
     * approves them; they may only view it or sign out.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPartner() && ! $user->isApprovedPartner() && ! $request->routeIs('partner.pending', 'logout')) {
            return redirect()->route('partner.pending');
        }

        return $next($request);
    }
}

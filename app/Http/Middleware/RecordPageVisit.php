<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class RecordPageVisit
{
    /**
     * The cookie that identifies a returning browser as the same visitor.
     */
    public const VISITOR_COOKIE = 'visitor_id';

    /**
     * Record each successful full page view for the admin analytics page.
     * Skips anything that isn't a person looking at a page: form submissions,
     * JSON endpoints, Inertia prefetches and partial reloads, the admin area
     * itself, and known bots.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldRecord($request, $response)) {
            return $response;
        }

        $visitorId = $request->cookie(self::VISITOR_COOKIE);

        if (! is_string($visitorId) || ! Str::isUuid($visitorId)) {
            $visitorId = (string) Str::uuid();
            $response->headers->setCookie(new Cookie(self::VISITOR_COOKIE, $visitorId, now()->addYear()));
        }

        PageVisit::query()->create([
            'visitor_id' => $visitorId,
            'user_id' => $request->user()?->id,
            'path' => Str::limit('/'.ltrim($request->path(), '/'), 500, ''),
            'route_name' => $request->route()?->getName(),
            'referrer' => $this->externalReferrer($request),
        ]);

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && $request->route() !== null
            && ! $request->is('admin', 'admin/*', 'up')
            && ! $request->header('X-Inertia-Partial-Data')
            && ! $request->prefetch()
            && ($request->header('X-Inertia') || str_contains((string) $response->headers->get('Content-Type'), 'text/html'))
            && ! preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|headless/i', (string) $request->userAgent());
    }

    /**
     * Only referrers from other sites are useful ("where did visitors come
     * from"); in-app navigation would just repeat the path list.
     */
    private function externalReferrer(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');

        if (! $referrer || parse_url($referrer, PHP_URL_HOST) === $request->getHost()) {
            return null;
        }

        return Str::limit($referrer, 500, '');
    }
}

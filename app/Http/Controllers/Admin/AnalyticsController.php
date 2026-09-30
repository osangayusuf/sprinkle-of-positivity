<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteAnalytics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /**
     * The date ranges an admin can pick, in days.
     */
    public const RANGES = [7, 30, 90];

    /**
     * Show visitor, sign-in, and member activity numbers for the chosen range.
     */
    public function index(Request $request, SiteAnalytics $analytics): Response
    {
        $this->authorize('access-admin');

        $days = in_array($request->integer('days'), self::RANGES, true) ? $request->integer('days') : 30;
        $since = today()->subDays($days - 1);

        return Inertia::render('admin/analytics', [
            'days' => $days,
            'ranges' => self::RANGES,
            'summary' => $analytics->summary($since),
            'daily' => $analytics->dailyVisits($since),
            'topPages' => $analytics->topPages($since),
            'topReferrers' => $analytics->topReferrers($since),
            'recentLogins' => $analytics->recentLogins(),
        ]);
    }
}

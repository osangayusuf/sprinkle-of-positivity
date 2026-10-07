<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnerStatus;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\MarketplaceListing;
use App\Models\ProgressReset;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the admin landing page with links into every admin section.
     */
    public function index(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/dashboard', [
            'counts' => [
                'groups' => Group::query()->count(),
                'users' => User::query()->count(),
                'activeListings' => MarketplaceListing::query()->active()->count(),
                'partners' => User::query()->where('partner_status', PartnerStatus::Approved->value)->count(),
                'pendingPartners' => User::query()->where('partner_status', PartnerStatus::Pending->value)->count(),
                'resetsThisWeek' => ProgressReset::query()->where('missed_on', '>=', today()->subDays(6))->count(),
            ],
        ]);
    }
}

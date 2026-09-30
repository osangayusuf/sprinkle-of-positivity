<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\MarketplaceListingResource;
use App\Http\Resources\VerseResource;
use App\Models\DailyVerse;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\MarketplaceListing;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * The home feed: today's global verse of the day, the member's most
     * recently active groups (none for guests), and featured marketplace
     * listings.
     */
    public function show(Request $request): Response
    {
        $verse = DailyVerse::query()->whereDate('date', today())->first();

        return Inertia::render('home/index', [
            'verse' => $verse ? new VerseResource($verse) : null,
            'yourGroups' => GroupResource::collection(
                $request->user() ? $this->recentlyActiveGroups($request->user()) : collect()
            ),
            'featuredListings' => MarketplaceListingResource::collection(
                MarketplaceListing::active()->limit(7)->get()
            ),
        ]);
    }

    /**
     * The member's approved groups, most recently active first, capped at 3.
     * "Activity" is the latest of: a verse posted, a quiz posted, or an
     * insight shared under one of the group's verses — computed as three
     * grouped queries total rather than one query per group.
     *
     * @return Collection<int, Group>
     */
    private function recentlyActiveGroups(User $user): Collection
    {
        $groupIds = $user->approvedGroups()->pluck('groups.id');

        if ($groupIds->isEmpty()) {
            return collect();
        }

        $lastVerseAt = GroupVerse::query()
            ->whereIn('group_id', $groupIds)
            ->selectRaw('group_id, MAX(created_at) as last_at')
            ->groupBy('group_id')
            ->pluck('last_at', 'group_id');

        $lastQuizAt = Quiz::query()
            ->whereIn('group_id', $groupIds)
            ->selectRaw('group_id, MAX(created_at) as last_at')
            ->groupBy('group_id')
            ->pluck('last_at', 'group_id');

        $lastInsightAt = Insight::query()
            ->join('group_verses', function ($join) {
                $join->on('group_verses.id', '=', 'insights.verseable_id')
                    ->where('insights.verseable_type', GroupVerse::class);
            })
            ->whereIn('group_verses.group_id', $groupIds)
            ->selectRaw('group_verses.group_id as group_id, MAX(insights.created_at) as last_at')
            ->groupBy('group_verses.group_id')
            ->pluck('last_at', 'group_id');

        return Group::query()
            ->whereIn('id', $groupIds)
            ->withCount('approvedMembers')
            ->get()
            ->sortByDesc(function (Group $group) use ($lastVerseAt, $lastQuizAt, $lastInsightAt) {
                return collect([
                    $lastVerseAt->get($group->id),
                    $lastQuizAt->get($group->id),
                    $lastInsightAt->get($group->id),
                    $group->created_at?->toDateTimeString(),
                ])->filter()->max();
            })
            ->take(3)
            ->values();
    }
}

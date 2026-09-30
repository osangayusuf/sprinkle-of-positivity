<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\GroupMembership;
use App\Models\Insight;
use App\Models\PageVisit;
use App\Models\QuizResponse;
use App\Models\Reaction;
use App\Models\User;
use App\Models\UserLogin;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * First-party site analytics for the admin area. Visits and sign-ins come
 * from their own tables; interactions (insights, comments, reactions, quiz
 * answers, group applications) are counted straight from the records those
 * actions already create, so nothing is tracked twice.
 */
class SiteAnalytics
{
    /**
     * Headline numbers for everything since the given moment.
     *
     * @return array{page_views: int, unique_visitors: int, guest_visitors: int, signed_in_visitors: int, logins: int, users_logged_in: int, new_signups: int, active_users: int, interactions: array{insights: int, comments: int, reactions: int, quiz_answers: int, group_applications: int}}
     */
    public function summary(CarbonInterface $since): array
    {
        $visits = PageVisit::query()->where('created_at', '>=', $since);
        $logins = UserLogin::query()->where('created_at', '>=', $since);

        return [
            'page_views' => (clone $visits)->count(),
            'unique_visitors' => (clone $visits)->distinct()->count('visitor_id'),
            'guest_visitors' => (clone $visits)->whereNull('user_id')->distinct()->count('visitor_id'),
            'signed_in_visitors' => (clone $visits)->whereNotNull('user_id')->distinct()->count('user_id'),
            'logins' => (clone $logins)->count(),
            'users_logged_in' => (clone $logins)->distinct()->count('user_id'),
            'new_signups' => User::query()->where('created_at', '>=', $since)->count(),
            'active_users' => $this->activeUserCount($since),
            'interactions' => [
                'insights' => Insight::query()->where('created_at', '>=', $since)->count(),
                'comments' => Comment::query()->where('created_at', '>=', $since)->count(),
                'reactions' => Reaction::query()->where('created_at', '>=', $since)->count(),
                'quiz_answers' => QuizResponse::query()->where('created_at', '>=', $since)->count(),
                'group_applications' => GroupMembership::query()->where('applied_at', '>=', $since)->count(),
            ],
        ];
    }

    /**
     * Page views and unique visitors for each day from `$since` to today,
     * including days with no visits.
     *
     * @return list<array{date: string, page_views: int, unique_visitors: int}>
     */
    public function dailyVisits(CarbonInterface $since): array
    {
        $rows = PageVisit::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as page_views, COUNT(DISTINCT visitor_id) as unique_visitors')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $days = [];

        for ($day = $since->copy()->startOfDay(); $day->lte(today()); $day = $day->addDay()) {
            $row = $rows->get($day->toDateString());

            $days[] = [
                'date' => $day->toDateString(),
                'page_views' => (int) ($row->page_views ?? 0),
                'unique_visitors' => (int) ($row->unique_visitors ?? 0),
            ];
        }

        return $days;
    }

    /**
     * The most viewed pages.
     *
     * @return list<array{path: string, page_views: int, unique_visitors: int}>
     */
    public function topPages(CarbonInterface $since, int $limit = 10): array
    {
        return PageVisit::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('path, COUNT(*) as page_views, COUNT(DISTINCT visitor_id) as unique_visitors')
            ->groupBy('path')
            ->orderByDesc('page_views')
            ->limit($limit)
            ->get()
            ->map(fn (PageVisit $visit) => [
                'path' => $visit->path,
                'page_views' => (int) $visit->getAttribute('page_views'),
                'unique_visitors' => (int) $visit->getAttribute('unique_visitors'),
            ])
            ->all();
    }

    /**
     * The other sites that sent the most visitors.
     *
     * @return list<array{referrer: string, page_views: int}>
     */
    public function topReferrers(CarbonInterface $since, int $limit = 5): array
    {
        return PageVisit::query()
            ->where('created_at', '>=', $since)
            ->whereNotNull('referrer')
            ->selectRaw('referrer, COUNT(*) as page_views')
            ->groupBy('referrer')
            ->orderByDesc('page_views')
            ->limit($limit)
            ->get()
            ->map(fn (PageVisit $visit) => [
                'referrer' => (string) $visit->referrer,
                'page_views' => (int) $visit->getAttribute('page_views'),
            ])
            ->all();
    }

    /**
     * The most recent sign-ins, newest first.
     *
     * @return list<array{id: int, user: array{id: int, name: string, avatar: string|null}, logged_in_at: string}>
     */
    public function recentLogins(int $limit = 15): array
    {
        return UserLogin::query()
            ->with('user')
            ->latest('created_at')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (UserLogin $login) => [
                'id' => $login->id,
                'user' => [
                    'id' => $login->user->id,
                    'name' => $login->user->name,
                    'avatar' => $login->user->avatar,
                ],
                'logged_in_at' => $login->created_at->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Members who did something (shared an insight, commented, reacted,
     * answered a quiz, or applied to a group), counted once each.
     */
    private function activeUserCount(CarbonInterface $since): int
    {
        $userIds = Insight::query()->select('user_id')->where('created_at', '>=', $since)
            ->union(Comment::query()->select('user_id')->where('created_at', '>=', $since))
            ->union(Reaction::query()->select('user_id')->where('created_at', '>=', $since))
            ->union(QuizResponse::query()->select('user_id')->where('created_at', '>=', $since))
            ->union(GroupMembership::query()->select('user_id')->where('applied_at', '>=', $since));

        return DB::query()->fromSub($userIds, 'active_users')->count();
    }
}

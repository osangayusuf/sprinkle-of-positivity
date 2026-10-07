<?php

namespace App\Http\Controllers\Partner;

use App\Actions\RejectAssignment;
use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use App\Services\ChallengeProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantController extends Controller
{
    /**
     * Every participant assigned to the signed-in partner, with their
     * progress at a glance. Admins see each partner's participants.
     */
    public function index(Request $request, ChallengeProgress $challengeProgress): Response
    {
        $user = $request->user();
        $isAdmin = $user->hasRole(Role::ADMIN);

        abort_unless($isAdmin || $user->isApprovedPartner(), 403);

        $memberships = GroupMembership::query()
            ->with(['user', 'group', 'partner'])
            ->where('role', GroupMembershipRole::Member->value)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->whereHas('group', fn ($groups) => $groups->whereNotNull('starts_on')->whereNotNull('duration_days'))
            ->when(! $isAdmin, fn ($query) => $query->where('partner_id', $user->id))
            ->get();

        $progress = $challengeProgress->forMemberships($memberships);
        $lastPostedAt = $memberships->groupBy('group_id')
            ->map(fn ($groupMemberships) => $this->lastPostedAt($groupMemberships->first()->group, $groupMemberships->pluck('user_id')->all()));

        $rows = $memberships->map(function (GroupMembership $membership) use ($progress, $lastPostedAt) {
            $memberProgress = $progress[$membership->id] ?? null;

            return [
                'id' => $membership->id,
                'user' => ['id' => $membership->user->id, 'name' => $membership->user->name, 'avatar' => $membership->user->avatar],
                'group' => ['id' => $membership->group->id, 'name' => $membership->group->name, 'slug' => $membership->group->slug, 'duration_days' => $membership->group->duration_days],
                'partner' => $membership->partner ? ['id' => $membership->partner->id, 'name' => $membership->partner->name] : null,
                'status' => $memberProgress['status'] ?? 'lagging',
                'run_day' => $memberProgress['run_day'] ?? 1,
                'current_streak' => $memberProgress['current_streak'] ?? 0,
                'completed_count' => $memberProgress['completed_count'] ?? 0,
                'reset_count' => $memberProgress['reset_count'] ?? 0,
                'completed_today' => $memberProgress['completed_today'] ?? false,
                'last_posted_at' => $lastPostedAt[$membership->group_id][$membership->user_id] ?? null,
            ];
        })->sortBy([['status', 'desc'], ['user.name', 'asc']])->values();

        return Inertia::render('partner/participants', [
            'participants' => $rows,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Encourage a participant, once per day per participant.
     */
    public function nudge(Request $request, GroupMembership $membership): RedirectResponse
    {
        $this->authorizeOwnParticipant($request->user(), $membership);

        $key = "nudge:{$membership->id}:".today()->toDateString();

        if (! Cache::add($key, true, now()->endOfDay())) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('You already nudged this participant today.')]);

            return back();
        }

        $membership->user->notify(new ChallengeAlert(
            __('Your partner is cheering you on'),
            __(':name is checking in on you. Take a moment for today\'s insight.', ['name' => $request->user()->name]),
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Nudge sent.')]);

        return back();
    }

    /**
     * Decline a participant so they move to another partner.
     */
    public function reject(Request $request, GroupMembership $membership, RejectAssignment $action): RedirectResponse
    {
        $this->authorizeOwnParticipant($request->user(), $membership, adminAllowed: false);

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $action->handle($membership, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Participant moved to another partner.')]);

        return back();
    }

    private function authorizeOwnParticipant(User $user, GroupMembership $membership, bool $adminAllowed = true): void
    {
        $isOwn = $user->isApprovedPartner() && $membership->partner_id === $user->id;

        abort_unless($isOwn || ($adminAllowed && $user->hasRole(Role::ADMIN)), 403);
    }

    /**
     * @param  array<int, int>  $userIds
     * @return array<int, string>
     */
    private function lastPostedAt(Group $group, array $userIds): array
    {
        return Insight::query()
            ->where('verseable_type', (new GroupVerse)->getMorphClass())
            ->whereIn('verseable_id', GroupVerse::query()->where('group_id', $group->id)->select('id'))
            ->whereIn('user_id', $userIds)
            ->selectRaw('user_id, max(created_at) as last_at')
            ->groupBy('user_id')
            ->pluck('last_at', 'user_id')
            ->map(fn ($at) => Carbon::parse($at)->toIso8601String())
            ->all();
    }
}

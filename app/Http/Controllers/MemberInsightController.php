<?php

namespace App\Http\Controllers;

use App\Enums\GroupMembershipStatus;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MemberInsightController extends Controller
{
    /**
     * Every insight one participant has shared in the group, newest day
     * first, for the group's managers and admins.
     */
    public function index(Group $group, User $user): Response
    {
        $this->authorize('manage', $group);

        abort_unless(
            $group->memberships()
                ->where('user_id', $user->id)
                ->where('status', GroupMembershipStatus::Approved->value)
                ->exists(),
            404,
        );

        $insights = Insight::query()
            ->where('user_id', $user->id)
            ->where('verseable_type', (new GroupVerse)->getMorphClass())
            ->whereIn('verseable_id', GroupVerse::query()->where('group_id', $group->id)->select('id'))
            ->with('verseable')
            ->withCount('comments')
            ->get()
            ->sortByDesc(fn (Insight $insight) => $insight->verseable->date)
            ->values()
            ->map(fn (Insight $insight) => [
                'id' => $insight->id,
                'body' => $insight->body,
                'image_url' => $insight->image_path ? Storage::disk('public')->url($insight->image_path) : null,
                'created_at' => $insight->created_at->toIso8601String(),
                'comments_count' => $insight->comments_count,
                'verse' => [
                    'date' => $insight->verseable->date->toDateString(),
                    'reference' => $insight->verseable->reference,
                ],
            ]);

        return Inertia::render('groups/member-insights', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'member' => ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar],
            'insights' => $insights,
        ]);
    }
}

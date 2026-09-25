<?php

namespace App\Http\Controllers;

use App\Actions\SubmitInsight;
use App\Http\Requests\Groups\SubmitInsightRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\InsightResource;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsightController extends Controller
{
    /**
     * Show the form for sharing a reflection on today's group verse.
     */
    public function create(Group $group): Response
    {
        $this->authorize('participate', $group);

        $verse = GroupVerse::query()
            ->where('group_id', $group->id)
            ->whereDate('date', today())
            ->firstOrFail();

        return Inertia::render('insights/create', [
            'group' => new GroupResource($group),
            'verse' => $verse->only(['id', 'reference']),
        ]);
    }

    /**
     * Share a reflection on today's group verse.
     */
    public function store(SubmitInsightRequest $request, Group $group, SubmitInsight $action): RedirectResponse
    {
        $verse = GroupVerse::query()
            ->where('group_id', $group->id)
            ->whereDate('date', today())
            ->firstOrFail();

        $insight = $action->handle(
            $verse,
            $request->user(),
            $request->string('body')->value(),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your insight has been shared. +10 points')]);

        return to_route('groups.insights.show', [$group, $insight]);
    }

    /**
     * Show a single insight with its comment thread.
     */
    public function show(Request $request, Group $group, Insight $insight): Response
    {
        $this->authorize('view', $group);
        abort_unless($this->belongsToGroup($insight, $group), 404);

        $insight->load(['user', 'reactions']);
        $insight->loadCount('comments');

        $comments = $insight->rootComments()
            ->with(['user', 'reactions', 'replies.user', 'replies.reactions'])
            ->oldest()
            ->get();

        return Inertia::render('insights/show', [
            'group' => new GroupResource($group),
            'insight' => new InsightResource($insight),
            'comments' => CommentResource::collection($comments),
            'canParticipate' => $request->user()->can('participate', $group),
        ]);
    }

    /**
     * Confirm the given insight was posted under this group's verse — the
     * usual nested-resource IDOR guard.
     */
    private function belongsToGroup(Insight $insight, Group $group): bool
    {
        return $insight->verseable instanceof GroupVerse
            && $insight->verseable->group_id === $group->id;
    }
}

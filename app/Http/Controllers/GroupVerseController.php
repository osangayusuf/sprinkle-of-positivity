<?php

namespace App\Http\Controllers;

use App\Actions\SetGroupVerse;
use App\Http\Requests\Groups\SetGroupVerseRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\InsightResource;
use App\Http\Resources\QuizResource;
use App\Http\Resources\VerseResource;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Services\ChallengeProgress;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupVerseController extends Controller
{
    /**
     * Show the group's verse for today, or for an earlier day via `?date=`
     * (an empty state if no verse was set for it).
     */
    public function show(Request $request, Group $group, ChallengeProgress $challengeProgress): Response|RedirectResponse
    {
        if (Gate::denies('viewContent', $group)) {
            return $this->privateGroupRedirect($group);
        }

        $user = $request->user();
        $date = $this->requestedDate($request);
        $isToday = $date->isSameDay(today());

        $verse = GroupVerse::query()
            ->where('group_id', $group->id)
            ->whereDate('date', $date)
            ->first();

        $insights = $verse
            ? $verse->insights()->with(['user', 'reactions'])->withCount('comments')->latest()->get()
            : collect();

        $quizzes = $verse
            ? $verse->quizzes()->with(['creator', 'options', 'responses'])->latest()->get()
            : collect();

        return Inertia::render('verses/show', [
            'group' => new GroupResource($group),
            'verse' => $verse ? new VerseResource($verse) : null,
            'canManage' => $user?->can('manage', $group) ?? false,
            'canParticipate' => $isToday && ($user?->can('participate', $group) ?? false),
            'viewingDate' => $date->toDateString(),
            'isToday' => $isToday,
            'pastDays' => GroupVerse::query()
                ->where('group_id', $group->id)
                ->whereDate('date', '<=', today())
                ->withCount('insights')
                ->orderByDesc('date')
                ->limit(120)
                ->get()
                ->map(fn (GroupVerse $past) => [
                    'date' => $past->date->toDateString(),
                    'reference' => $past->reference,
                    'insights_count' => $past->insights_count,
                ]),
            'insights' => InsightResource::collection($insights),
            'quizzes' => QuizResource::collection($quizzes),
            'progress' => $user ? $challengeProgress->forMember($group, $user) : null,
        ]);
    }

    /**
     * The day being viewed: a past date from `?date=`, otherwise today.
     * Future and malformed dates fall back to today.
     */
    private function requestedDate(Request $request): CarbonInterface
    {
        $requested = $request->query('date');

        if (is_string($requested) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requested)) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $requested)->startOfDay();

                if ($date->lte(today())) {
                    return $date;
                }
            } catch (InvalidFormatException) {
            }
        }

        return today();
    }

    /**
     * Show the manager's form for setting today's verse.
     */
    public function edit(Request $request, Group $group): Response
    {
        $this->authorize('manage', $group);

        $verse = GroupVerse::query()
            ->where('group_id', $group->id)
            ->whereDate('date', today())
            ->first();

        return Inertia::render('verses/edit', [
            'group' => new GroupResource($group),
            'verse' => $verse ? new VerseResource($verse) : null,
        ]);
    }

    /**
     * Set today's verse for the group.
     */
    public function update(SetGroupVerseRequest $request, Group $group, SetGroupVerse $action): RedirectResponse
    {
        $action->handle(
            $group,
            $request->user(),
            $request->string('reference')->value(),
            $request->string('text')->value(),
            image: $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __("Today's verse was updated.")]);

        return to_route('groups.verse.show', $group);
    }

    /**
     * Send visitors who can't read a private group's content back to its
     * public page, which explains that membership is required.
     */
    private function privateGroupRedirect(Group $group): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'info', 'message' => __('This is a private group. Join it to read its Bible Study Insights.')]);

        return to_route('groups.show', $group);
    }
}

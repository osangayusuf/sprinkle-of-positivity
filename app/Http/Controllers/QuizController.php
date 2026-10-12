<?php

namespace App\Http\Controllers;

use App\Actions\CreateQuiz;
use App\Actions\DeleteQuiz;
use App\Actions\UpdateQuiz;
use App\Http\Requests\Groups\StoreQuizRequest;
use App\Http\Requests\Groups\UpdateQuizRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    /**
     * A manager posts a Q&A question or a poll for today's group verse.
     */
    public function store(StoreQuizRequest $request, Group $group, CreateQuiz $action): RedirectResponse
    {
        $verse = GroupVerse::query()
            ->where('group_id', $group->id)
            ->whereDate('date', today())
            ->firstOrFail();

        $action->handle(
            $verse,
            $request->user(),
            $request->string('question')->value(),
            $request->array('options'),
            $request->isPoll() ? null : $request->integer('correct_index'),
            $request->isPoll() ? Quiz::TYPE_POLL : Quiz::TYPE_QUIZ,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => $request->isPoll() ? __('Poll posted.') : __('Question posted.')]);

        return back();
    }

    /**
     * Show the edit form for a quiz — only reachable while it has zero
     * responses (see UpdateQuizRequest).
     */
    public function edit(Group $group, Quiz $quiz): Response
    {
        $this->authorize('manage', $group);
        abort_unless($quiz->group_id === $group->id, 404);
        abort_if($quiz->responses()->exists(), 403);

        return Inertia::render('quizzes/edit', [
            'group' => new GroupResource($group),
            'quiz' => [
                'id' => $quiz->id,
                'type' => $quiz->type,
                'question' => $quiz->question,
                'options' => $quiz->options()->orderBy('position')->get(['id', 'label', 'position']),
                'correct_quiz_option_id' => $quiz->correct_quiz_option_id,
            ],
        ]);
    }

    /**
     * Replace a quiz's question and answer options.
     */
    public function update(UpdateQuizRequest $request, Group $group, Quiz $quiz, UpdateQuiz $action): RedirectResponse
    {
        abort_unless($quiz->group_id === $group->id, 404);

        $action->handle(
            $quiz,
            $request->string('question')->value(),
            $request->array('options'),
            $quiz->isPoll() ? null : $request->integer('correct_index'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => $quiz->isPoll() ? __('Poll updated.') : __('Question updated.')]);

        return to_route('groups.verse.show', $group);
    }

    /**
     * Close a poll so no more votes (or changed votes) are accepted; the
     * results become visible to everyone.
     */
    public function close(Group $group, Quiz $quiz): RedirectResponse
    {
        return $this->setPollClosed($group, $quiz, closed: true);
    }

    /**
     * Reopen a closed poll for voting.
     */
    public function reopen(Group $group, Quiz $quiz): RedirectResponse
    {
        return $this->setPollClosed($group, $quiz, closed: false);
    }

    private function setPollClosed(Group $group, Quiz $quiz, bool $closed): RedirectResponse
    {
        $this->authorize('manage', $group);
        abort_unless($quiz->group_id === $group->id && $quiz->isPoll(), 404);

        $quiz->closed_at = $closed ? now() : null;
        $quiz->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => $closed ? __('Poll closed.') : __('Poll reopened.')]);

        return back();
    }

    /**
     * Delete a quiz, reversing any points its responses earned.
     */
    public function destroy(Group $group, Quiz $quiz, DeleteQuiz $action): RedirectResponse
    {
        $this->authorize('manage', $group);
        abort_unless($quiz->group_id === $group->id, 404);

        $hadResponses = $quiz->responses()->exists();

        $action->handle($quiz);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $hadResponses
                ? __('Question deleted. Points earned from it were reversed.')
                : __('Question deleted.'),
        ]);

        return to_route('groups.verse.show', $group);
    }
}

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
     * A manager posts a Q&A question for today's group verse.
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
            $request->integer('correct_index'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Question posted.')]);

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
            $request->integer('correct_index'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Question updated.')]);

        return to_route('groups.verse.show', $group);
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

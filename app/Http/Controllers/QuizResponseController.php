<?php

namespace App\Http\Controllers;

use App\Actions\RespondToQuiz;
use App\Http\Requests\Groups\RespondToQuizRequest;
use App\Models\Group;
use App\Models\Quiz;
use App\Models\QuizOption;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class QuizResponseController extends Controller
{
    /**
     * A member answers a group's Q&A question.
     */
    public function store(RespondToQuizRequest $request, Group $group, Quiz $quiz, RespondToQuiz $action): RedirectResponse
    {
        abort_unless($quiz->group_id === $group->id, 404);

        $option = QuizOption::query()->findOrFail($request->integer('quiz_option_id'));

        $action->handle($quiz, $request->user(), $option);

        $isCorrect = $quiz->correct_quiz_option_id !== null && $quiz->correct_quiz_option_id === $option->id;

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $isCorrect
                ? __('Answer submitted. +20 points — correct!')
                : __('Answer submitted. +5 points'),
        ]);

        return back();
    }
}

<?php

namespace App\Actions;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizResponse;
use App\Models\User;

class RespondToQuiz
{
    /**
     * Record a member's answer. Each member gets exactly one response per
     * quiz — enforced by the request (checking for an existing response)
     * and the database's unique constraint. A poll vote can be changed, which
     * moves the member's single response to the new option.
     */
    public function handle(Quiz $quiz, User $user, QuizOption $option): QuizResponse
    {
        $response = $quiz->isPoll()
            ? $quiz->responses()->where('user_id', $user->id)->first()
            : null;

        $response ??= new QuizResponse;
        $response->quiz_id = $quiz->id;
        $response->user_id = $user->id;
        $response->quiz_option_id = $option->id;
        $response->save();

        return $response;
    }
}

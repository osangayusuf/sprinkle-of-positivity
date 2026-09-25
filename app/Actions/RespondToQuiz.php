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
     * and the database's unique constraint.
     */
    public function handle(Quiz $quiz, User $user, QuizOption $option): QuizResponse
    {
        $response = new QuizResponse;
        $response->quiz_id = $quiz->id;
        $response->user_id = $user->id;
        $response->quiz_option_id = $option->id;
        $response->save();

        return $response;
    }
}

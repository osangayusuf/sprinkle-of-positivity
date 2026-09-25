<?php

namespace App\Observers;

use App\Actions\AwardPoints;
use App\Models\PointsLedgerEntry;
use App\Models\QuizResponse;

class QuizResponseObserver
{
    public function __construct(private AwardPoints $awardPoints) {}

    /**
     * Award points for answering a quiz, plus a bonus when the quiz has a
     * correct answer set and the member got it right.
     */
    public function created(QuizResponse $response): void
    {
        $this->awardPoints->handle($response->user, 5, PointsLedgerEntry::REASON_QUIZ_PARTICIPATED, $response);

        $quiz = $response->quiz;

        if ($quiz->correct_quiz_option_id !== null && $quiz->correct_quiz_option_id === $response->quiz_option_id) {
            $this->awardPoints->handle($response->user, 15, PointsLedgerEntry::REASON_QUIZ_CORRECT, $response);
        }
    }
}

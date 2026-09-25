<?php

namespace App\Actions;

use App\Models\Quiz;
use App\Models\QuizOption;

class UpdateQuiz
{
    /**
     * Replace a quiz's question and answer options. Only reachable while
     * the quiz has zero responses (enforced by UpdateQuizRequest), so it's
     * safe to drop and recreate the options wholesale.
     *
     * @param  array<int, string>  $options
     */
    public function handle(Quiz $quiz, string $question, array $options, int $correctIndex): Quiz
    {
        $quiz->question = $question;

        $quiz->options()->delete();

        $optionIds = [];

        foreach (array_values($options) as $position => $label) {
            $option = new QuizOption(['label' => $label, 'position' => $position]);
            $option->quiz_id = $quiz->id;
            $option->save();

            $optionIds[$position] = $option->id;
        }

        $quiz->correct_quiz_option_id = $optionIds[$correctIndex];
        $quiz->save();

        return $quiz;
    }
}

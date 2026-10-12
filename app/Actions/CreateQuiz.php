<?php

namespace App\Actions;

use App\Models\GroupVerse;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\User;

class CreateQuiz
{
    /**
     * Post a Q&A question — or a poll, which has no correct answer — with
     * its options for a group's verse.
     *
     * @param  array<int, string>  $options
     */
    public function handle(GroupVerse $verse, User $author, string $question, array $options, ?int $correctIndex = null, string $type = Quiz::TYPE_QUIZ): Quiz
    {
        $quiz = new Quiz(['question' => $question]);
        $quiz->type = $type;
        $quiz->group_id = $verse->group_id;
        $quiz->group_verse_id = $verse->id;
        $quiz->created_by = $author->id;
        $quiz->save();

        $optionIds = [];

        foreach (array_values($options) as $position => $label) {
            $option = new QuizOption(['label' => $label, 'position' => $position]);
            $option->quiz_id = $quiz->id;
            $option->save();

            $optionIds[$position] = $option->id;
        }

        $quiz->correct_quiz_option_id = $correctIndex === null ? null : $optionIds[$correctIndex];
        $quiz->save();

        return $quiz;
    }
}

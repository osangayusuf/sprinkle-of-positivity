<?php

namespace App\Actions;

use App\Models\PointsLedgerEntry;
use App\Models\Quiz;
use App\Models\QuizResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteQuiz
{
    /**
     * Delete a quiz. If it already has responses, first reverse the points
     * those responses earned — otherwise deleting the quiz (which cascades
     * to its options/responses) would leave stale points on users' totals.
     */
    public function handle(Quiz $quiz): void
    {
        DB::transaction(function () use ($quiz) {
            $responseIds = $quiz->responses()->pluck('id');

            if ($responseIds->isNotEmpty()) {
                $entries = PointsLedgerEntry::query()
                    ->where('reference_type', (new QuizResponse)->getMorphClass())
                    ->whereIn('reference_id', $responseIds)
                    ->get();

                $entries->groupBy('user_id')->each(function ($userEntries, $userId) {
                    User::query()->whereKey($userId)->decrement('points', $userEntries->sum('points'));
                });

                PointsLedgerEntry::query()->whereIn('id', $entries->pluck('id'))->delete();
            }

            $quiz->delete();
        });
    }
}

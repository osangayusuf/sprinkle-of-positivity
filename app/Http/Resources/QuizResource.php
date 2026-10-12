<?php

namespace App\Http\Resources;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quiz */
class QuizResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $myResponseOptionId = $this->responses
            ->firstWhere('user_id', $request->user()?->id)
            ?->quiz_option_id;

        // Poll results stay hidden until the member has voted, the poll is
        // closed, or they manage the group, so they don't sway the vote.
        $showResults = $this->isPoll() && (
            $myResponseOptionId !== null
            || $this->isClosed()
            || ($request->user()?->can('manage', $this->group) ?? false)
        );

        return [
            'id' => $this->id,
            'type' => $this->type,
            'closed' => $this->isClosed(),
            'question' => $this->question,
            'created_by_name' => $this->creator->name,
            'created_by_avatar' => $this->creator->avatar,
            'created_at' => $this->created_at->toIso8601String(),
            'options' => $this->options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'votes' => $showResults
                    ? $this->responses->where('quiz_option_id', $option->id)->count()
                    : null,
            ]),
            'responses_count' => $this->responses->count(),
            'my_response_option_id' => $myResponseOptionId,
            // Only reveal the correct option once the current user has answered.
            'correct_quiz_option_id' => $myResponseOptionId !== null ? $this->correct_quiz_option_id : null,
        ];
    }
}

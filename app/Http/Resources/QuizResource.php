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

        return [
            'id' => $this->id,
            'question' => $this->question,
            'created_by_name' => $this->creator->name,
            'created_at' => $this->created_at->toIso8601String(),
            'options' => $this->options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
            ]),
            'responses_count' => $this->responses->count(),
            'my_response_option_id' => $myResponseOptionId,
            // Only reveal the correct option once the current user has answered.
            'correct_quiz_option_id' => $myResponseOptionId !== null ? $this->correct_quiz_option_id : null,
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Comment */
class CommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar,
            ],
            'body' => $this->body,
            'created_at' => $this->created_at->toIso8601String(),
            'reaction_summary' => $this->reactionSummary(),
            'my_reaction' => $this->reactions
                ->firstWhere('user_id', $request->user()?->id)
                ?->emoji,
            'replies' => $this->relationLoaded('replies')
                ? CommentResource::collection($this->replies)
                : [],
        ];
    }
}

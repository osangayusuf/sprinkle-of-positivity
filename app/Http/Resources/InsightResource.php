<?php

namespace App\Http\Resources;

use App\Models\Insight;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Insight */
class InsightResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reactions = $this->reactionSummary();

        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar,
            ],
            'body' => $this->body,
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
            'created_at' => $this->created_at->toIso8601String(),
            'comments_count' => $this->whenCounted('comments'),
            'reaction_summary' => $reactions,
            'my_reaction' => $this->reactions
                ->firstWhere('user_id', $request->user()?->id)
                ?->emoji,
        ];
    }
}

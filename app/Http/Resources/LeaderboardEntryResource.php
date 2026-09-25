<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 *
 * @property int $rank
 * @property float $progress
 * @property bool $is_me
 */
class LeaderboardEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rank' => $this->rank,
            'id' => $this->id,
            'name' => $this->name,
            'points' => $this->points,
            'progress' => $this->progress,
            'is_me' => $this->is_me,
        ];
    }
}

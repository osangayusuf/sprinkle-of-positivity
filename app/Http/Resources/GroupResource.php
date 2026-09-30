<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Group */
class GroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'purpose' => $this->purpose,
            'cover_image_url' => $this->cover_image_path
                ? Storage::disk('public')->url($this->cover_image_path)
                : null,
            'duration_days' => $this->duration_days,
            'starts_on' => $this->starts_on?->toDateString(),
            'status' => $this->status->value,
            'is_private' => $this->is_private,
            'members_count' => $this->whenCounted('approvedMembers'),
            'current_day' => $this->currentDayNumber(),
        ];
    }
}

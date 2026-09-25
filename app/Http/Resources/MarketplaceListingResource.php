<?php

namespace App\Http\Resources;

use App\Models\MarketplaceListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin MarketplaceListing */
class MarketplaceListingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'starts_at' => $this->starts_at?->toDateTimeString(),
            'ends_at' => $this->ends_at?->toDateTimeString(),
        ];
    }
}

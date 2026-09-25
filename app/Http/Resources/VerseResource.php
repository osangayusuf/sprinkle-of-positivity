<?php

namespace App\Http\Resources;

use App\Models\DailyVerse;
use App\Models\GroupVerse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Shared shape for both DailyVerse and GroupVerse — they carry the same
 * fields, just scoped differently.
 *
 * @mixin DailyVerse|GroupVerse
 */
class VerseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'reference' => $this->reference,
            'text' => $this->text,
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
        ];
    }
}

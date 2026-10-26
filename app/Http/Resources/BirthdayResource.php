<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class BirthdayResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'date' => today()->format('F jS'),
            'is_you' => $request->user()?->is($this->resource) ?? false,
            'wished' => (bool) ($this->wished ?? false),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Certificate */
class CertificateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->can('access-admin') ?? false;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'recipient_name' => $this->recipient_name,
            'duration_days' => $this->duration_days,
            'year' => $this->year,
            'issued_at' => $this->issued_at->toDateString(),
            'revoked' => $this->isRevoked(),
            'group_name' => $this->whenLoaded('group', fn () => $this->group->name),
            'override_reason' => $this->when($isAdmin, $this->override_reason),
            'revoke_reason' => $this->when($isAdmin, $this->revoke_reason),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'points' => $this->points,
            'role' => match (true) {
                $this->hasRole(Role::ADMIN) => Role::ADMIN,
                $this->hasRole(Role::PARTNER) => Role::PARTNER,
                default => Role::MEMBER,
            },
            'partner_status' => $this->partner_status?->value,
        ];
    }
}

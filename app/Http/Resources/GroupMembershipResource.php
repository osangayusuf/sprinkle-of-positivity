<?php

namespace App\Http\Resources;

use App\Models\GroupMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps a User loaded through a Group's membership relations, where
 * `$this->pivot` is the underlying GroupMembership record.
 *
 * @mixin User
 */
class GroupMembershipResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var GroupMembership $membership */
        $membership = $this->pivot;

        return [
            'membership_id' => $membership->id,
            'user_id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'role' => $membership->role->value,
            'status' => $membership->status->value,
            'applied_at' => $membership->applied_at?->toIso8601String(),
        ];
    }
}

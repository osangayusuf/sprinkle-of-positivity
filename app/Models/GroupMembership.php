<?php

namespace App\Models;

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property GroupMembershipRole $role
 * @property GroupMembershipStatus $status
 * @property Carbon|null $code_of_conduct_accepted_at
 * @property Carbon|null $applied_at
 * @property Carbon|null $decided_at
 * @property int|null $decided_by
 */
class GroupMembership extends Pivot
{
    protected $table = 'group_user';

    public $incrementing = true;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => GroupMembershipRole::class,
            'status' => GroupMembershipStatus::class,
            'code_of_conduct_accepted_at' => 'datetime',
            'applied_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}

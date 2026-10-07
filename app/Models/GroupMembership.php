<?php

namespace App\Models;

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property GroupMembershipRole $role
 * @property GroupMembershipStatus $status
 * @property CarbonInterface|null $code_of_conduct_accepted_at
 * @property CarbonInterface|null $applied_at
 * @property CarbonInterface|null $decided_at
 * @property int|null $decided_by
 * @property int|null $partner_id
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

    /**
     * The accountability partner this participant is assigned to.
     *
     * @return BelongsTo<User, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}

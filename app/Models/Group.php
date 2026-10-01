<?php

namespace App\Models;

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Enums\GroupStatus;
use Carbon\CarbonInterface;
use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $purpose
 * @property string|null $cover_image_path
 * @property int|null $duration_days
 * @property CarbonInterface|null $starts_on
 * @property GroupStatus $status
 * @property bool $is_private
 * @property int $created_by
 */
#[Fillable(['name', 'slug', 'purpose', 'cover_image_path', 'duration_days', 'starts_on', 'is_private'])]
class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GroupStatus::class,
            'starts_on' => 'date',
            'is_private' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<GroupMembership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * @return HasMany<Certificate, $this>
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * @return BelongsToMany<User, $this, GroupMembership>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(GroupMembership::class)
            ->withPivot(['id', 'role', 'status', 'code_of_conduct_accepted_at', 'applied_at', 'decided_at', 'decided_by'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this, GroupMembership>
     */
    public function approvedMembers(): BelongsToMany
    {
        return $this->users()->wherePivot('status', GroupMembershipStatus::Approved->value);
    }

    /**
     * @return BelongsToMany<User, $this, GroupMembership>
     */
    public function pendingApplications(): BelongsToMany
    {
        return $this->users()->wherePivot('status', GroupMembershipStatus::Pending->value);
    }

    /**
     * @return BelongsToMany<User, $this, GroupMembership>
     */
    public function managers(): BelongsToMany
    {
        return $this->approvedMembers()->wherePivot('role', GroupMembershipRole::Manager->value);
    }

    /**
     * The 1-indexed day number of this group's challenge for today, or null
     * when there's no active challenge window (no duration/start date set,
     * today is before it starts, or the challenge has already ended).
     */
    public function currentDayNumber(): ?int
    {
        if (! $this->starts_on || ! $this->duration_days) {
            return null;
        }

        $startsOn = $this->starts_on->startOfDay();
        $today = today();

        if ($today->lt($startsOn)) {
            return null;
        }

        $day = (int) $startsOn->diffInDays($today) + 1;

        return $day > $this->duration_days ? null : $day;
    }

    /**
     * The last day of this group's challenge, or null when none is configured.
     */
    public function challengeEndsOn(): ?CarbonInterface
    {
        if (! $this->starts_on || ! $this->duration_days) {
            return null;
        }

        return $this->starts_on->copy()->startOfDay()->addDays($this->duration_days - 1);
    }

    /**
     * Whether the challenge's final day is already behind us.
     */
    public function challengeHasEnded(): bool
    {
        $endsOn = $this->challengeEndsOn();

        return $endsOn !== null && today()->gt($endsOn);
    }
}

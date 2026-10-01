<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use NotificationChannels\WebPush\HasPushSubscriptions;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar_path
 * @property-read string|null $avatar
 * @property CarbonInterface|null $email_verified_at
 * @property string $password
 * @property string|null $whatsapp_number
 * @property int|null $birthday_day
 * @property int|null $birthday_month
 * @property array<int, string>|null $goals
 * @property int $points
 * @property CarbonInterface|null $onboarding_completed_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonInterface|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'whatsapp_number', 'goals', 'birthday_day', 'birthday_month'])]
#[Hidden(['password', 'avatar_path', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['avatar'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'birthday_day' => 'integer',
            'birthday_month' => 'integer',
            'goals' => 'array',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    /**
     * The public URL of the user's profile picture, or null to fall back to
     * their initials.
     *
     * @return Attribute<string|null, never>
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null,
        );
    }

    /**
     * The platform-wide roles assigned to the user.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Determine if the user has been assigned the given platform-wide role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    /**
     * @return HasMany<GroupMembership, $this>
     */
    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    /**
     * @return BelongsToMany<Group, $this, GroupMembership>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->using(GroupMembership::class)
            ->withPivot(['id', 'role', 'status', 'code_of_conduct_accepted_at', 'applied_at', 'decided_at', 'decided_by'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Group, $this, GroupMembership>
     */
    public function approvedGroups(): BelongsToMany
    {
        return $this->groups()->wherePivot('status', GroupMembershipStatus::Approved->value);
    }

    /**
     * @return BelongsToMany<Group, $this, GroupMembership>
     */
    public function managedGroups(): BelongsToMany
    {
        return $this->approvedGroups()->wherePivot('role', GroupMembershipRole::Manager->value);
    }

    /**
     * Determine if the user is an approved manager of the given group.
     */
    public function isManagerOf(Group $group): bool
    {
        return $this->groupMemberships->contains(
            fn (GroupMembership $membership) => $membership->group_id === $group->id
                && $membership->role === GroupMembershipRole::Manager
                && $membership->status === GroupMembershipStatus::Approved
        );
    }
}

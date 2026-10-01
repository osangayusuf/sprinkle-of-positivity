<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A completion certificate for one member of one group's challenge. A revoked
 * row is kept (not deleted) so the daily issuing command never re-creates it.
 *
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property string $code
 * @property string $recipient_name
 * @property int $duration_days
 * @property int $year
 * @property CarbonInterface $issued_at
 * @property int|null $issued_by
 * @property string|null $override_reason
 * @property CarbonInterface|null $revoked_at
 * @property int|null $revoked_by
 * @property string|null $revoke_reason
 */
#[Fillable(['recipient_name'])]
class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * A short, unguessable code that doubles as the public share/verify URL.
     */
    public static function generateCode(): string
    {
        do {
            $code = Str::lower(Str::random(12));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}

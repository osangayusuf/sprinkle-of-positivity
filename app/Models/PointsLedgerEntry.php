<?php

namespace App\Models;

use Database\Factories\PointsLedgerEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An auditable, append-only record of every point award — the source of
 * truth behind the cached User::$points counter.
 *
 * @property int $id
 * @property int $user_id
 * @property int $points
 * @property string $reason
 * @property string|null $reference_type
 * @property int|null $reference_id
 */
#[Fillable(['points', 'reason'])]
class PointsLedgerEntry extends Model
{
    /** @use HasFactory<PointsLedgerEntryFactory> */
    use HasFactory;

    protected $table = 'points_ledger';

    public const REASON_INSIGHT_SUBMITTED = 'insight_submitted';

    public const REASON_COMMENT_POSTED = 'comment_posted';

    public const REASON_QUIZ_PARTICIPATED = 'quiz_participated';

    public const REASON_QUIZ_CORRECT = 'quiz_correct';

    public const REASON_STREAK_BONUS = 'streak_bonus';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}

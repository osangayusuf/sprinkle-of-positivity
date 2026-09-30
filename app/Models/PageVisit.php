<?php

namespace App\Models;

use Database\Factories\PageVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A single full page view, recorded by the RecordPageVisit middleware for
 * the admin analytics page. `visitor_id` comes from a long-lived cookie so
 * a returning browser counts as one unique visitor, signed in or not.
 *
 * @property int $id
 * @property string $visitor_id
 * @property int|null $user_id
 * @property string $path
 * @property string|null $route_name
 * @property string|null $referrer
 * @property Carbon $created_at
 */
#[Fillable(['visitor_id', 'user_id', 'path', 'route_name', 'referrer'])]
class PageVisit extends Model
{
    /** @use HasFactory<PageVisitFactory> */
    use HasFactory, MassPrunable;

    /**
     * How long raw visits are kept before being pruned.
     */
    public const RETENTION_MONTHS = 12;

    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subMonths(self::RETENTION_MONTHS));
    }
}

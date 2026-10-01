<?php

namespace App\Models;

use App\Concerns\Commentable;
use App\Concerns\Reactable;
use Carbon\CarbonInterface;
use Database\Factories\InsightFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $verseable_type
 * @property int $verseable_id
 * @property string $body
 * @property string|null $image_path
 * @property CarbonInterface $created_at
 */
#[Fillable(['body'])]
class Insight extends Model
{
    /** @use HasFactory<InsightFactory> */
    use Commentable, HasFactory, Reactable;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The verse (global or group) this insight reflects on.
     *
     * @return MorphTo<Model, $this>
     */
    public function verseable(): MorphTo
    {
        return $this->morphTo();
    }
}

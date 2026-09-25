<?php

namespace App\Models;

use Database\Factories\ReactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $reactable_type
 * @property int $reactable_id
 * @property string $emoji
 */
#[Fillable(['emoji'])]
class Reaction extends Model
{
    /** @use HasFactory<ReactionFactory> */
    use HasFactory;

    /**
     * The emoji options members can react with — kept small and fixed to
     * match the reaction cluster shown in the design.
     *
     * @var array<int, string>
     */
    public const EMOJIS = ['🔥', '😊', '😲', '❤️', '🙏'];

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
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}

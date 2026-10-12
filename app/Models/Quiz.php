<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $group_id
 * @property int|null $group_verse_id
 * @property string $type
 * @property string $question
 * @property int $created_by
 * @property int|null $correct_quiz_option_id
 * @property CarbonInterface|null $closed_at
 * @property CarbonInterface $created_at
 */
#[Fillable(['question'])]
class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    public const TYPE_QUIZ = 'quiz';

    public const TYPE_POLL = 'poll';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    /**
     * A poll has no correct answer and no points: it only collects votes.
     */
    public function isPoll(): bool
    {
        return $this->type === self::TYPE_POLL;
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return BelongsTo<GroupVerse, $this>
     */
    public function groupVerse(): BelongsTo
    {
        return $this->belongsTo(GroupVerse::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<QuizOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('position');
    }

    /**
     * @return HasMany<QuizResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(QuizResponse::class);
    }

    /**
     * @return BelongsTo<QuizOption, $this>
     */
    public function correctOption(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class, 'correct_quiz_option_id');
    }
}

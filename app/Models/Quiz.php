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
 * @property string $question
 * @property int $created_by
 * @property int|null $correct_quiz_option_id
 * @property CarbonInterface $created_at
 */
#[Fillable(['question'])]
class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

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

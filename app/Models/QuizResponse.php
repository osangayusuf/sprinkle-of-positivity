<?php

namespace App\Models;

use Database\Factories\QuizResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $quiz_id
 * @property int $user_id
 * @property int $quiz_option_id
 */
class QuizResponse extends Model
{
    /** @use HasFactory<QuizResponseFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<QuizOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }
}

<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\DailyVerseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property CarbonInterface $date
 * @property string $reference
 * @property string $text
 * @property string|null $image_path
 * @property int $created_by
 */
#[Fillable(['reference', 'text', 'image_path'])]
class DailyVerse extends Model
{
    /** @use HasFactory<DailyVerseFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

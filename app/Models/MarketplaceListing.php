<?php

namespace App\Models;

use Database\Factories\MarketplaceListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $image_path
 * @property string|null $cta_label
 * @property string|null $cta_url
 * @property int $position
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property int $created_by
 */
#[Fillable(['title', 'description', 'cta_label', 'cta_url', 'position', 'starts_at', 'ends_at'])]
class MarketplaceListing extends Model
{
    /** @use HasFactory<MarketplaceListingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Active listings within their optional date window, ordered for
     * display — shared by the marketplace page and the Home carousel.
     *
     * @param  Builder<MarketplaceListing>  $query
     * @return Builder<MarketplaceListing>
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('position')
            ->latest();
    }
}

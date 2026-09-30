<?php

namespace App\Models;

use Database\Factories\UserLoginFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One successful sign-in, recorded for the admin analytics page.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $created_at
 */
class UserLogin extends Model
{
    /** @use HasFactory<UserLoginFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

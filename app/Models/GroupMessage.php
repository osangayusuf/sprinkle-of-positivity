<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\GroupMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property int|null $reply_to_id
 * @property string $body
 * @property CarbonInterface|null $edited_at
 * @property CarbonInterface $created_at
 */
#[Fillable(['body'])]
class GroupMessage extends Model
{
    /** @use HasFactory<GroupMessageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['edited_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The earlier message this one replies to, if it still exists.
     *
     * @return BelongsTo<GroupMessage, $this>
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    /**
     * A short plain-text excerpt for quoting this message in a reply, with
     * `@[id]` mention tokens swapped for the tagged member's name.
     */
    public function preview(): string
    {
        $names = $this->mentions->pluck('name', 'id');

        $text = preg_replace_callback(
            '/@\[(\d+)\]/',
            fn (array $match) => '@'.($names[(int) $match[1]] ?? __('someone')),
            $this->body,
        );

        return Str::limit($text, 120);
    }

    /**
     * The members tagged in this message with an `@[id]` token in its body.
     *
     * @return BelongsToMany<User, $this>
     */
    public function mentions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_message_mentions')->withTimestamps();
    }
}

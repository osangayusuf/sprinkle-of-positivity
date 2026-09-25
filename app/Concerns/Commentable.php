<?php

namespace App\Concerns;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    /**
     * All comments left on this model, including replies.
     *
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Only the top-level comments — replies are nested under these via
     * Comment::replies().
     *
     * @return MorphMany<Comment, $this>
     */
    public function rootComments(): MorphMany
    {
        return $this->comments()->whereNull('parent_id');
    }
}

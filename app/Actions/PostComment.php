<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Insight;
use App\Models\User;

class PostComment
{
    /**
     * Post a comment (or a threaded reply, when $parent is given) on an
     * insight.
     */
    public function handle(Insight $insight, User $author, string $body, ?Comment $parent = null): Comment
    {
        $comment = new Comment(['body' => $body]);
        $comment->user_id = $author->id;
        $comment->commentable_id = $insight->id;
        $comment->commentable_type = $insight->getMorphClass();
        $comment->parent_id = $parent?->id;
        $comment->save();

        return $comment;
    }
}

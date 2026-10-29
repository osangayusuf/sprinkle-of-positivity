<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\GroupVerse;
use App\Notifications\MentionedInComment;

class UpdateComment
{
    public function __construct(private ResolveMentionedMembers $resolveMentions) {}

    /**
     * Edit a comment's text. Its mentions are re-synced, and only people
     * newly tagged by the edit are notified.
     */
    public function handle(Comment $comment, string $body): Comment
    {
        if ($comment->body === $body) {
            return $comment;
        }

        $verse = $comment->commentable->verseable;
        $author = $comment->user;

        $before = $verse instanceof GroupVerse
            ? $this->resolveMentions->handle($verse->group, $author, $comment->body)
            : null;
        $after = $verse instanceof GroupVerse
            ? $this->resolveMentions->handle($verse->group, $author, $body)
            : null;

        $comment->body = $body;
        $comment->edited_at = now();
        $comment->save();

        if ($before && $after) {
            $comment->mentions()->sync($after['mentioned']->modelKeys());

            $after['notify']
                ->reject(fn ($user) => $before['notify']->contains($user))
                ->each(fn ($user) => $user->notify(new MentionedInComment($author, $verse->group)));
        }

        return $comment;
    }
}

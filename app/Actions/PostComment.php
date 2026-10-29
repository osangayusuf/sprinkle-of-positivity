<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use App\Notifications\MentionedInComment;

class PostComment
{
    public function __construct(private ResolveMentionedMembers $resolveMentions) {}

    /**
     * Post a comment (or a threaded reply, when $parent is given) on an
     * insight. Members tagged with an `@[id]` token are recorded and
     * notified, and `@everyone` notifies the whole group.
     */
    public function handle(Insight $insight, User $author, string $body, ?Comment $parent = null): Comment
    {
        $comment = new Comment(['body' => $body]);
        $comment->user_id = $author->id;
        $comment->commentable_id = $insight->id;
        $comment->commentable_type = $insight->getMorphClass();
        $comment->parent_id = $parent?->id;
        $comment->save();

        $verse = $insight->verseable;

        if ($verse instanceof GroupVerse) {
            ['mentioned' => $mentioned, 'notify' => $notify] = $this->resolveMentions->handle($verse->group, $author, $body);

            if ($mentioned->isNotEmpty()) {
                $comment->mentions()->attach($mentioned->modelKeys());
            }

            $notify->each(fn (User $user) => $user->notify(new MentionedInComment($author, $verse->group)));
        }

        return $comment;
    }
}

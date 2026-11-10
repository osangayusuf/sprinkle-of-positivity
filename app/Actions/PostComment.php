<?php

namespace App\Actions;

use App\Enums\PushCategory;
use App\Models\Comment;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use App\Notifications\MentionedInComment;
use App\Notifications\NewInsightComment;

class PostComment
{
    public function __construct(private ResolveMentionedMembers $resolveMentions, private NotifyGroupMembers $notifyMembers) {}

    /**
     * Post a comment (or a threaded reply, when $parent is given) on an
     * insight. Members tagged with an `@[id]` token are recorded and
     * notified, and `@everyone` notifies the whole group. The insight's author
     * and everyone who has already commented on it get a plain new-comment
     * push.
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

            $participantIds = Comment::query()
                ->where('commentable_type', $insight->getMorphClass())
                ->where('commentable_id', $insight->id)
                ->pluck('user_id')
                ->push($insight->user_id)
                ->unique()
                ->values()
                ->all();

            $this->notifyMembers->handle(
                $verse->group,
                $author,
                PushCategory::Insights,
                new NewInsightComment($author, $verse->group, $insight, $comment),
                $notify->modelKeys(),
                $participantIds,
            );
        }

        return $comment;
    }
}

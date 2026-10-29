<?php

namespace App\Actions;

use App\Models\GroupMessage;
use App\Notifications\MentionedInGroupChat;

class UpdateGroupMessage
{
    public function __construct(private ResolveMentionedMembers $resolveMentions) {}

    /**
     * Edit a chat message's text. Its mentions are re-synced, and only people
     * newly tagged by the edit are notified.
     */
    public function handle(GroupMessage $message, string $body): GroupMessage
    {
        if ($message->body === $body) {
            return $message;
        }

        $group = $message->group;
        $author = $message->user;

        $before = $this->resolveMentions->handle($group, $author, $message->body);
        $after = $this->resolveMentions->handle($group, $author, $body);

        $message->body = $body;
        $message->edited_at = now();
        $message->save();

        $message->mentions()->sync($after['mentioned']->modelKeys());

        $after['notify']
            ->reject(fn ($user) => $before['notify']->contains($user))
            ->each(fn ($user) => $user->notify(new MentionedInGroupChat($author, $group)));

        return $message;
    }
}

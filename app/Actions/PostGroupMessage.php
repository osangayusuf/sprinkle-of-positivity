<?php

namespace App\Actions;

use App\Enums\PushCategory;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use App\Notifications\MentionedInGroupChat;
use App\Notifications\NewGroupMessage;

class PostGroupMessage
{
    public function __construct(private ResolveMentionedMembers $resolveMentions, private NotifyGroupMembers $notifyMembers) {}

    /**
     * Post a message to a group's chat, optionally as a reply to an earlier
     * message in the same chat. Members tagged with an `@[id]` token in the
     * body are recorded and notified, and `@everyone` notifies the whole group.
     * Everyone else in the group gets a plain new-message push.
     */
    public function handle(Group $group, User $author, string $body, ?int $replyToId = null): GroupMessage
    {
        $message = new GroupMessage(['body' => $body]);
        $message->group_id = $group->id;
        $message->user_id = $author->id;
        $message->reply_to_id = $replyToId;
        $message->save();

        ['mentioned' => $mentioned, 'notify' => $notify] = $this->resolveMentions->handle($group, $author, $body);

        if ($mentioned->isNotEmpty()) {
            $message->mentions()->attach($mentioned->modelKeys());
        }

        $notify->each(fn (User $user) => $user->notify(new MentionedInGroupChat($author, $group)));

        $this->notifyMembers->handle(
            $group,
            $author,
            PushCategory::Chat,
            new NewGroupMessage($author, $group, $message),
            $notify->modelKeys(),
        );

        return $message;
    }
}

<?php

namespace App\Actions;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;

class PostGroupMessage
{
    /**
     * Post a message to a group's chat.
     */
    public function handle(Group $group, User $author, string $body): GroupMessage
    {
        $message = new GroupMessage(['body' => $body]);
        $message->group_id = $group->id;
        $message->user_id = $author->id;
        $message->save();

        return $message;
    }
}

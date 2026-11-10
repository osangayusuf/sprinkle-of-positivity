<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;

class NewGroupMessage extends GroupActivityNotification
{
    public function __construct(public User $author, public Group $group, public GroupMessage $message) {}

    protected function title(): string
    {
        return $this->group->name;
    }

    protected function body(): string
    {
        return $this->author->name.': '.$this->message->preview();
    }

    protected function url(): string
    {
        return route('groups.chat.show', $this->group, absolute: false);
    }

    protected function tag(): string
    {
        return 'group-chat-'.$this->group->id;
    }
}

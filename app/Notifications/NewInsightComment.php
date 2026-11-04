<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Group;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Support\Str;

class NewInsightComment extends GroupActivityNotification
{
    public function __construct(public User $author, public Group $group, public Insight $insight, public Comment $comment) {}

    protected function title(): string
    {
        return __(':name commented on an insight', ['name' => $this->author->name]);
    }

    protected function body(): string
    {
        return $this->group->name.': '.Str::limit($this->comment->body, 120);
    }

    protected function url(): string
    {
        return route('groups.insights.show', [$this->group, $this->insight], absolute: false);
    }

    protected function tag(): string
    {
        return 'group-insight-'.$this->insight->id;
    }
}

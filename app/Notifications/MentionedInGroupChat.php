<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class MentionedInGroupChat extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $author, public Group $group) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title())
            ->body($this->body());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
        ];
    }

    private function title(): string
    {
        return __('You were mentioned');
    }

    private function body(): string
    {
        return __(':name mentioned you in :group chat.', [
            'name' => $this->author->name,
            'group' => $this->group->name,
        ]);
    }
}

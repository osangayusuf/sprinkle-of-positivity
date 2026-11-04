<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * A push-only alert about activity in a group the member belongs to. These
 * are deliberately not stored as database notifications: chat in particular
 * would flood the in-app list.
 */
abstract class GroupActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title())
            ->body($this->body())
            ->icon('/icons/icon-192.png')
            ->tag($this->tag())
            ->data(['url' => $this->url()]);
    }

    abstract protected function title(): string;

    abstract protected function body(): string;

    /**
     * The path to open when the alert is tapped.
     */
    abstract protected function url(): string;

    /**
     * Alerts sharing a tag replace each other instead of stacking.
     */
    abstract protected function tag(): string;
}

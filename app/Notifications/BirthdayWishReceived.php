<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BirthdayWishReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $sender) {}

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
            ->title(__('Happy Birthday! 🎉'))
            ->body(__(':name wished you a happy birthday.', ['name' => $this->sender->name]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Happy Birthday! 🎉'),
            'body' => __(':name wished you a happy birthday.', ['name' => $this->sender->name]),
        ];
    }
}

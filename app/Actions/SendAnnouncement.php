<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\Announcement;
use Illuminate\Support\Facades\Notification;

class SendAnnouncement
{
    /**
     * Fan out an announcement to every user on the platform.
     */
    public function handle(string $title, string $body): void
    {
        $notification = new Announcement($title, $body);

        User::query()->chunkById(200, function ($users) use ($notification) {
            Notification::send($users, $notification);
        });
    }
}

<?php

namespace App\Actions;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\Announcement as AnnouncementNotification;
use Illuminate\Support\Facades\Notification;

class SendAnnouncement
{
    /**
     * Record an announcement and fan it out to every user on the platform,
     * the sending admin included.
     */
    public function handle(User $author, string $title, string $body): Announcement
    {
        $announcement = new Announcement(['title' => $title, 'body' => $body]);
        $announcement->created_by = $author->id;
        $announcement->save();

        $this->deliver($announcement);

        return $announcement;
    }

    /**
     * Notify every user about an already-recorded announcement.
     */
    public function deliver(Announcement $announcement): void
    {
        $notification = new AnnouncementNotification($announcement->title, $announcement->body, $announcement->id);

        User::query()->chunkById(200, function ($users) use ($notification) {
            Notification::send($users, $notification);
        });

        $announcement->last_sent_at = now();
        $announcement->save();
    }
}

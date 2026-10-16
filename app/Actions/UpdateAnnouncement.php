<?php

namespace App\Actions;

use App\Models\Announcement;
use App\Notifications\Announcement as AnnouncementNotification;
use Illuminate\Notifications\DatabaseNotification;

class UpdateAnnouncement
{
    public function __construct(private SendAnnouncement $sendAnnouncement) {}

    /**
     * Correct an announcement. Notifications already delivered are rewritten
     * in place so every member sees the corrected text, and the announcement
     * can optionally be sent out again as a fresh, unread notification.
     */
    public function handle(Announcement $announcement, string $title, string $body, bool $resend = false): Announcement
    {
        $announcement->fill(['title' => $title, 'body' => $body]);
        $announcement->save();

        DatabaseNotification::query()
            ->where('type', AnnouncementNotification::class)
            ->where('data->announcement_id', $announcement->id)
            ->chunkById(200, function ($notifications) use ($announcement) {
                foreach ($notifications as $notification) {
                    $notification->data = [
                        ...$notification->data,
                        'title' => $announcement->title,
                        'body' => $announcement->body,
                    ];
                    $notification->save();
                }
            });

        if ($resend) {
            $this->sendAnnouncement->deliver($announcement);
        }

        return $announcement;
    }
}

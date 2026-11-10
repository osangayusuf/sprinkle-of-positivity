<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipantAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $participant, public Group $group) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__(':name is now assigned to you as their accountability partner in :group.', [
                'name' => $this->participant->name,
                'group' => $this->group->name,
            ]))
            ->line(__('Please reach out and engage with them within the next 24 hours.'))
            ->action(__('View your participants'), route('partner.participants'));
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
        return __('New participant assigned');
    }

    private function body(): string
    {
        return __(':name is now assigned to you in :group. Please engage with them within the next 24 hours.', [
            'name' => $this->participant->name,
            'group' => $this->group->name,
        ]);
    }
}

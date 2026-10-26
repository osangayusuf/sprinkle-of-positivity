<?php

namespace App\Actions;

use App\Models\BirthdayWish;
use App\Models\User;
use App\Notifications\BirthdayWishReceived;

class SendBirthdayWish
{
    /**
     * Wish the recipient a happy birthday. A member can wish each person
     * once per year; repeats are ignored so the recipient isn't spammed.
     * Returns whether a new wish was sent.
     */
    public function handle(User $sender, User $recipient): bool
    {
        $wish = BirthdayWish::query()->firstOrCreate([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'year' => today()->year,
        ]);

        if (! $wish->wasRecentlyCreated) {
            return false;
        }

        $recipient->notify(new BirthdayWishReceived($sender));

        return true;
    }
}

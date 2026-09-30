<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Auth\Events\Login;

class RecordUserLogin
{
    /**
     * Log every successful sign-in (password, passkey, or "remember me") for
     * the admin analytics page.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $login = new UserLogin;
        $login->user_id = $event->user->id;
        $login->save();
    }
}

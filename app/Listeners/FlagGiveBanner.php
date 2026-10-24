<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class FlagGiveBanner
{
    /**
     * Ask the home page to show the giving banner once after each sign-in.
     */
    public function handle(Login $event): void
    {
        session()->put('show_give_banner', true);
    }
}

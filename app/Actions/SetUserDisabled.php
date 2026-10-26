<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SetUserDisabled
{
    /**
     * Disable or re-enable a user's account. Disabling also drops their
     * stored sessions so they are signed out everywhere straight away.
     */
    public function handle(User $user, bool $disabled): User
    {
        $user->disabled_at = $disabled ? now() : null;
        $user->save();

        if ($disabled && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        return $user;
    }
}

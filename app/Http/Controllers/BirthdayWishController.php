<?php

namespace App\Http\Controllers;

use App\Actions\SendBirthdayWish;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BirthdayWishController extends Controller
{
    /**
     * Send the birthday person a wish. Only possible on their birthday, for
     * active accounts, and never to yourself.
     */
    public function store(Request $request, User $user, SendBirthdayWish $action): RedirectResponse
    {
        abort_if($request->user()->is($user), 403);
        abort_unless(User::query()->hasBirthdayToday()->whereKey($user->id)->exists(), 404);

        $action->handle($request->user(), $user);

        return back();
    }
}

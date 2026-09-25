<?php

namespace App\Actions;

use App\Models\User;

class CompleteOnboarding
{
    /**
     * Persist the user's profile-completion details and mark onboarding
     * as finished.
     *
     * @param  array{name: string, whatsapp_number: string, goals: array<int, string>, birthday_day: int|null, birthday_month: int|null}  $input
     */
    public function handle(User $user, array $input): User
    {
        $user->fill([
            'name' => $input['name'],
            'whatsapp_number' => $input['whatsapp_number'],
            'goals' => $input['goals'],
            'birthday_day' => $input['birthday_day'],
            'birthday_month' => $input['birthday_month'],
        ]);

        // Not mass-assignable by design (see the #[Fillable] attribute on
        // User) — set directly so it can only ever be touched here.
        $user->onboarding_completed_at = now();

        $user->save();

        return $user;
    }
}

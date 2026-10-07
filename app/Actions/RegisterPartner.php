<?php

namespace App\Actions;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\PartnerStatus;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class RegisterPartner
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Create an accountability partner account that stays locked until an
     * admin approves it.
     *
     * @param  array<string, string>  $input
     */
    public function handle(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        $user->partner_status = PartnerStatus::Pending;
        $user->onboarding_completed_at = now();
        $user->save();
        $user->roles()->attach(Role::query()->firstOrCreate(['name' => Role::PARTNER]));

        Notification::send(
            User::query()->whereHas('roles', fn ($roles) => $roles->where('name', Role::ADMIN))->get(),
            new ChallengeAlert(
                __('New partner awaiting approval'),
                __(':name signed up as an accountability partner.', ['name' => $user->name]),
            ),
        );

        return $user;
    }
}

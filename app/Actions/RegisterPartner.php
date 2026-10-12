<?php

namespace App\Actions;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class RegisterPartner
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private ApplyToBePartner $applyToBePartner) {}

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

        $user->onboarding_completed_at = now();
        $user->save();
        $user->roles()->attach(Role::query()->firstOrCreate(['name' => Role::PARTNER]));

        return $this->applyToBePartner->handle($user);
    }
}

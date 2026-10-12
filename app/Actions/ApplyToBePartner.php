<?php

namespace App\Actions;

use App\Enums\PartnerStatus;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;

class ApplyToBePartner
{
    /**
     * Mark the user's accountability partner application as pending and
     * alert the admins. A signed-in applicant keeps their current role (and
     * full access) until an admin approves — see DecidePartnerApplication.
     */
    public function handle(User $user): User
    {
        $user->partner_status = PartnerStatus::Pending;
        $user->partner_decided_at = null;
        $user->save();

        Notification::send(
            User::query()->whereHas('roles', fn ($roles) => $roles->where('name', Role::ADMIN))->get(),
            new ChallengeAlert(
                __('New partner awaiting approval'),
                __(':name applied to be an accountability partner.', ['name' => $user->name]),
            ),
        );

        return $user;
    }
}

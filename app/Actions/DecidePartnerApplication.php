<?php

namespace App\Actions;

use App\Enums\PartnerStatus;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChallengeAlert;

class DecidePartnerApplication
{
    /**
     * Approve or reject an accountability partner's application. Approving
     * someone who applied while signed in as a regular member swaps them to
     * the partner role, the same as an admin changing their role; admins
     * keep theirs.
     */
    public function handle(User $partner, PartnerStatus $decision): User
    {
        $partner->partner_status = $decision;
        $partner->partner_decided_at = now();
        $partner->save();

        if ($decision === PartnerStatus::Approved && ! $partner->isPartner() && ! $partner->hasRole(Role::ADMIN)) {
            $partner->roles()->sync([Role::query()->firstOrCreate(['name' => Role::PARTNER])->id]);
        }

        $partner->notify(new ChallengeAlert(
            $decision === PartnerStatus::Approved ? __('Your partner account is approved') : __('Your partner application was declined'),
            $decision === PartnerStatus::Approved
                ? __('You can now manage participants and help them stay accountable.')
                : __('An admin did not approve your accountability partner sign-up.'),
        ));

        return $partner;
    }
}

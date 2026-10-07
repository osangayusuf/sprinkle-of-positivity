<?php

namespace App\Actions;

use App\Enums\PartnerStatus;
use App\Models\User;
use App\Notifications\ChallengeAlert;

class DecidePartnerApplication
{
    /**
     * Approve or reject an accountability partner's sign-up.
     */
    public function handle(User $partner, PartnerStatus $decision): User
    {
        $partner->partner_status = $decision;
        $partner->partner_decided_at = now();
        $partner->save();

        $partner->notify(new ChallengeAlert(
            $decision === PartnerStatus::Approved ? __('Your partner account is approved') : __('Your partner application was declined'),
            $decision === PartnerStatus::Approved
                ? __('You can now manage participants and help them stay accountable.')
                : __('An admin did not approve your accountability partner sign-up.'),
        ));

        return $partner;
    }
}

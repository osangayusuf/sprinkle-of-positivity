<?php

namespace App\Actions;

use App\Models\Certificate;
use App\Models\User;

class RevokeCertificate
{
    /**
     * Withdraw a certificate that was issued in error. The row is kept so the
     * daily issuing command does not create it again.
     */
    public function handle(Certificate $certificate, User $admin, string $reason): Certificate
    {
        $certificate->revoked_at = now();
        $certificate->revoked_by = $admin->id;
        $certificate->revoke_reason = $reason;
        $certificate->save();

        return $certificate;
    }
}

<?php

namespace App\Actions;

use App\Models\Certificate;

class RestoreCertificate
{
    /**
     * Reinstate a previously revoked certificate.
     */
    public function handle(Certificate $certificate): Certificate
    {
        $certificate->revoked_at = null;
        $certificate->revoked_by = null;
        $certificate->revoke_reason = null;
        $certificate->save();

        return $certificate;
    }
}

<?php

namespace App\Observers;

use App\Actions\AwardPoints;
use App\Models\Insight;
use App\Models\PointsLedgerEntry;

class InsightObserver
{
    public function __construct(private AwardPoints $awardPoints) {}

    /**
     * Award points for sharing a reflection.
     */
    public function created(Insight $insight): void
    {
        $this->awardPoints->handle($insight->user, 10, PointsLedgerEntry::REASON_INSIGHT_SUBMITTED, $insight);
    }
}

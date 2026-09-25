<?php

namespace App\Observers;

use App\Actions\AwardPoints;
use App\Models\Comment;
use App\Models\PointsLedgerEntry;

class CommentObserver
{
    public function __construct(private AwardPoints $awardPoints) {}

    /**
     * Award points for posting a comment or reply.
     */
    public function created(Comment $comment): void
    {
        $this->awardPoints->handle($comment->user, 5, PointsLedgerEntry::REASON_COMMENT_POSTED, $comment);
    }
}

<?php

namespace App\Enums;

enum GroupMembershipStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Removed = 'removed';
}

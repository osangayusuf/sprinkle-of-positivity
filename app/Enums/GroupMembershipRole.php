<?php

namespace App\Enums;

enum GroupMembershipRole: string
{
    case Manager = 'manager';
    case Member = 'member';
}

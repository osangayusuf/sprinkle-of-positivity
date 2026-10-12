<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recalibration go-live date
    |--------------------------------------------------------------------------
    |
    | Days before this date (Y-m-d, Africa/Lagos) can be completed but never
    | reset a member's progress, so a cohort already running is not penalised
    | retroactively. Leave empty to apply the rule to every day.
    |
    */

    'recalibration_starts_on' => env('CHALLENGE_RECALIBRATION_STARTS_ON'),

    /*
    |--------------------------------------------------------------------------
    | Evening reminder time
    |--------------------------------------------------------------------------
    |
    | When (HH:MM, Africa/Lagos) members who have not yet posted today's
    | insight are reminded.
    |
    */

    'reminder_time' => env('CHALLENGE_REMINDER_TIME', '18:00'),

    /*
    |--------------------------------------------------------------------------
    | Past days window
    |--------------------------------------------------------------------------
    |
    | How many months back a group's verse page lists earlier days (and their
    | insights) to open.
    |
    */

    'history_months' => (int) env('CHALLENGE_HISTORY_MONTHS', 6),

];

<?php

// The 8 fixed gamification tiers, lowest first. Content-only and not
// admin-editable in the design, so this stays config-driven rather than a
// database table.

return [
    ['key' => 'amateur', 'label' => 'Amateur', 'threshold' => 500],
    ['key' => 'novice', 'label' => 'Novice', 'threshold' => 1_000],
    ['key' => 'leader', 'label' => 'Leader', 'threshold' => 2_000],
    ['key' => 'senior', 'label' => 'Senior', 'threshold' => 10_000],
    ['key' => 'moderator', 'label' => 'Moderator', 'threshold' => 20_000],
    ['key' => 'master', 'label' => 'Master', 'threshold' => 200_000],
    ['key' => 'professor', 'label' => 'Professor', 'threshold' => 500_000],
    ['key' => 'noble', 'label' => 'Noble', 'threshold' => 5_000_000],
];

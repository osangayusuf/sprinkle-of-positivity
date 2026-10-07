---
paths:
  - 'app/Services/ChallengeProgress.php,app/Services/StreakCalculator.php'
---

# Services Services

## A missed verse day restarts the member's run at day one
Progress is still derived from insights (no completion table). A missed day that had a verse ends the run and sets reset_days; days with no verse are skipped, and days before join date (decided_at) or config('challenge.recalibration_starts_on') can never break a run. Certificate eligibility is longest_span >= duration_days (a run's calendar span), not "every day of the group window", so it can fire after the group's original end date. The day boundary is Africa/Lagos (APP_TIMEZONE). progress_resets only makes reset notifications idempotent; never read progress from it.

---
paths:
  - app/Services/ChallengeProgress.php
---

# Services

## A challenge day is completed by posting an insight; certificates need every day that had a verse
Streaks and certificate eligibility are derived from insights on the group's GroupVerse for each day (no completion table). Days with no verse set are not required, since nobody could complete them. Eligibility only applies after the challenge ends. Compare group_verses.date with whereDate, not whereBetween on date strings: a datetime-stored date sorts after the bare end date and silently drops the last day.

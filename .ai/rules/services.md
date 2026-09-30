---
paths:
  - app/Services/ChallengeProgress.php
  - app/Services/SiteAnalytics.php
---

# Services

## A challenge day is completed by posting an insight; certificates need every day that had a verse
Streaks and certificate eligibility are derived from insights on the group's GroupVerse for each day (no completion table). Days with no verse set are not required, since nobody could complete them. Eligibility only applies after the challenge ends. Compare group_verses.date with whereDate, not whereBetween on date strings: a datetime-stored date sorts after the bare end date and silently drops the last day.

## Analytics derives interactions from existing tables
Only page views (page_visits, via RecordPageVisit middleware) and sign-ins (user_logins, via the Login listener) have their own tables. Insights, comments, reactions, quiz answers and group applications are counted from their own records — don't add a separate activity log that duplicates them. The middleware skips admin pages, non-page responses, prefetches, partial reloads and bots; page visits are pruned after 12 months by the scheduled model:prune.

---
paths:
  - 'app/Policies/GroupPolicy.php,app/Http/Requests/Groups/*.php'
---

# Groups

## Use GroupPolicy::participate() for member write-actions
Sharing insights, commenting, reacting, and answering quizzes are all gated by the same rule: an approved member (any role) of the group, or a platform admin. Reuse `$user->can('participate', $group)` for any new member-facing write action rather than writing a bespoke check — this was consolidated deliberately after the first few FormRequests duplicated the same logic.

---
paths:
  - app/Policies/GroupPolicy.php
---

# Policies

## Gate group content reads with viewContent, not view
Guests can browse groups, verses and insights (these GET routes sit outside `auth`), so GroupPolicy read abilities take `?User`. Private groups (`is_private`) are still listed and their page is public; only verses/insights/comments/quizzes are limited to approved members and admins via `viewContent`. Any new page showing group content must check `viewContent` (non-members get redirected to groups.show with a toast), not `view`.

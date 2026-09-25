---
paths:
  - 'app/Models/Insight.php,app/Models/Comment.php,app/Http/Controllers/InsightController.php,app/Http/Controllers/CommentController.php,app/Http/Controllers/QuizController.php'
---

# Controllers

## Insights/comments/reactions/quizzes are scoped to GroupVerse only
Insight::verseable and the data model support both DailyVerse and GroupVerse (per the original plan), but only the GroupVerse flow has Figma coverage and a real UI. InsightController/QuizController only ever create records against a group's GroupVerse for today — don't wire a DailyVerse insight-sharing flow without new design input.

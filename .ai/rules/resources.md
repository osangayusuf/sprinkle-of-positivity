---
paths:
  - 'app/Models/Quiz.php,app/Observers/QuizResponseObserver.php,app/Actions/RespondToQuiz.php,app/Http/Resources/QuizResource.php'
---

# Resources

## Polls are quizzes with type=poll: no points, changeable votes
A poll is a Quiz row with type='poll' (no new tables). It has no correct option, earns no points (QuizResponseObserver returns early for polls), and a vote can be changed until a manager sets closed_at — RespondToQuiz then moves the member's single response instead of creating one. QuizResource only sends per-option votes once the viewer has voted, the poll is closed, or they manage the group, so results don't sway the vote. Polls attach to today's GroupVerse like questions do; there are no standalone polls yet. QuizResponse has no #[Fillable], so assign its columns directly (updateOrCreate silently drops them).

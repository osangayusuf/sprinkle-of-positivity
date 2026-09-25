---
paths:
  - 'tests/**/*.php'
---

# Tests

## Fillable-guarded columns need direct assignment in tests too
Columns deliberately excluded from a model's #[Fillable] (User::points, Quiz::correct_quiz_option_id, etc.) are silently dropped by ->update([...]) and ->fill([...]) in tests exactly like in app code — this bit PointsTest.php twice. Set them via direct property assignment + save() in tests, e.g. `$user->points = 495; $user->save();`. Model factories are unaffected (Eloquent factories bypass guarding via Model::unguarded()).

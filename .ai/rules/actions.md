---
paths:
  - 'app/Models/Reaction.php,app/Actions/ToggleReaction.php'
---

# Actions

## Reactions are single-pick per user from a fixed emoji set
Reaction::EMOJIS is the full allowed set (kept small to match the design's reaction cluster). A user has at most one reaction per reactable item: picking the same emoji again removes it, picking a different one replaces it (see ToggleReaction). Don't add multi-emoji-per-user support without a design reason.

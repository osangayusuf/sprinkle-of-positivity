---
paths:
  - resources/js/lib/progress-width.ts
---

# Lib

## Shared progressWidthClass for data-driven bar widths
progressWidthClass() now lives in resources/js/lib/progress-width.ts (it moved out of leaderboard/index.tsx) and is shared by the leaderboard and poll result bars. Use it for any width driven by runtime data; never interpolate a Tailwind class or use inline style.

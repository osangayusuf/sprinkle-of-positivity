---
paths:
  - 'resources/js/**/*.tsx'
---

# Js

## Never build a Tailwind class from an interpolated runtime value
Tailwind's scanner extracts class names by static text matching, so `` `bg-[url('${x}')]` `` or `` `w-[${n}%]` `` never generates a matching CSS rule — the class exists in the DOM but has no styles (found and fixed a real bug: `groups/show.tsx`'s cover image never rendered). For a dynamic image, render a real `<img>` positioned with `absolute inset-0 object-cover` instead of a CSS background. For a dynamic numeric value (like a progress bar), snap to the nearest of a small set of fully-literal Tailwind classes (see `progressWidthClass()` in `leaderboard/index.tsx`) rather than interpolating. Inline `style` remains off-limits per project instructions.

---
paths:
  - vite.config.ts
---

# General

## vite-plugin-pwa needs navigateFallback disabled and scope widened
This app is server-rendered (Blade + Inertia), not a static SPA, so vite-plugin-pwa's `generateSW` default of precaching an `index.html` navigation fallback is wrong here — there is no index.html, and leaving it enabled makes the service worker try to serve a nonexistent cached page for every navigation. Must set `workbox.navigateFallback: null` (found and fixed this before it shipped). Also: the SW file is emitted to `/build/sw.js` (Vite's outDir under Laravel), so its default scope is `/build/` — set the plugin's top-level `scope: '/'` AND `manifest.scope: '/'`, AND rely on `public/.htaccess`'s `Service-Worker-Allowed: /` header for sw.js (the browser enforces that header as a hard ceiling; `php artisan serve` doesn't read .htaccess, so this only takes effect under real Apache/cPanel hosting).

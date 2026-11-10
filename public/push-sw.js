/*
 * Web Push handlers, pulled into the Workbox-generated service worker via
 * `workbox.importScripts` (see vite.config.ts). Kept as plain JS in public/
 * so the precaching build stays untouched.
 */

const DEFAULT_URL = '/notifications';

self.addEventListener('push', (event) => {
    event.waitUntil(showNotification(readPayload(event)));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const target = new URL(
        event.notification.data?.url || DEFAULT_URL,
        self.location.origin,
    );

    event.waitUntil(openTarget(target));
});

function readPayload(event) {
    if (!event.data) {
        return {};
    }

    try {
        return event.data.json();
    } catch {
        return { body: event.data.text() };
    }
}

async function showNotification(payload) {
    const url = payload.data?.url || DEFAULT_URL;

    // Skip the alert when the member is already looking at that very page
    // (e.g. the chat they're reading) — the page itself shows the update.
    if (await isViewing(url)) {
        return;
    }

    await self.registration.showNotification(payload.title || 'Sprinkle of Positivity', {
        body: payload.body || '',
        icon: payload.icon || '/icons/icon-192.png',
        badge: payload.badge || '/icons/icon-192.png',
        // Same tag = one notification per group/thread instead of a stack.
        tag: payload.tag,
        renotify: Boolean(payload.tag),
        data: { ...(payload.data || {}), url },
    });
}

async function isViewing(url) {
    const path = new URL(url, self.location.origin).pathname;
    const windows = await self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true,
    });

    return windows.some(
        (client) =>
            client.visibilityState === 'visible' &&
            client.focused &&
            new URL(client.url).pathname === path,
    );
}

async function openTarget(target) {
    const windows = await self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true,
    });

    for (const client of windows) {
        if (new URL(client.url).origin === target.origin) {
            await client.focus();

            if (client.url !== target.href && 'navigate' in client) {
                await client.navigate(target.href);
            }

            return;
        }
    }

    await self.clients.openWindow(target.href);
}

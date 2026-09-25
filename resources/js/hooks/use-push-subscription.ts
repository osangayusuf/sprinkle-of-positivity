import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { csrfHeader } from '@/lib/csrf';

type PushSubscriptionState = {
    supported: boolean;
    permission: NotificationPermission | null;
    subscribed: boolean;
    subscribing: boolean;
    subscribe: () => Promise<void>;
    unsubscribe: () => Promise<void>;
};

function urlBase64ToUint8Array(base64: string): Uint8Array {
    const padding = '='.repeat((4 - (base64.length % 4)) % 4);
    const raw = window.atob(
        (base64 + padding).replace(/-/g, '+').replace(/_/g, '/'),
    );
    const bytes = new Uint8Array(raw.length);

    for (let i = 0; i < raw.length; i++) {
        bytes[i] = raw.charCodeAt(i);
    }

    return bytes;
}

/**
 * Notification-permission prompt + PushManager subscribe/unsubscribe,
 * syncing the browser's subscription with the server via
 * PushSubscriptionController.
 */
export function usePushSubscription(): PushSubscriptionState {
    const { vapidPublicKey } = usePage<{ vapidPublicKey: string | null }>()
        .props;

    const supported =
        typeof window !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        Boolean(vapidPublicKey);

    const [permission, setPermission] = useState<NotificationPermission | null>(
        supported ? Notification.permission : null,
    );
    const [subscribed, setSubscribed] = useState(false);
    const [subscribing, setSubscribing] = useState(false);

    useEffect(() => {
        if (!supported) {
            return;
        }

        navigator.serviceWorker.ready
            .then((registration) => registration.pushManager.getSubscription())
            .then((subscription) => setSubscribed(subscription !== null))
            .catch(() => setSubscribed(false));
    }, [supported]);

    const subscribe = useCallback(async () => {
        if (!supported || !vapidPublicKey) {
            return;
        }

        setSubscribing(true);

        try {
            const result = await Notification.requestPermission();
            setPermission(result);

            if (result !== 'granted') {
                return;
            }

            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    vapidPublicKey,
                ) as BufferSource,
            });

            await fetch('/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    ...csrfHeader(),
                },
                body: JSON.stringify(subscription.toJSON()),
            });

            setSubscribed(true);
        } finally {
            setSubscribing(false);
        }
    }, [supported, vapidPublicKey]);

    const unsubscribe = useCallback(async () => {
        if (!supported) {
            return;
        }

        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            setSubscribed(false);
            return;
        }

        const endpoint = subscription.endpoint;
        await subscription.unsubscribe();

        await fetch('/push-subscriptions', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...csrfHeader(),
            },
            body: JSON.stringify({ endpoint }),
        });

        setSubscribed(false);
    }, [supported]);

    return {
        supported,
        permission,
        subscribed,
        subscribing,
        subscribe,
        unsubscribe,
    };
}

import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/page-header';
import type { Notification } from '@/types/models';

export default function NotificationShow({
    notification,
}: {
    notification: Notification;
}) {
    return (
        <>
            <Head title={notification.title} />
            <PageHeader title={notification.title} backHref="/notifications" />

            <div className="bg-muted min-h-full px-4 py-6">
                <p className="text-foreground text-sm leading-relaxed whitespace-pre-line">
                    {notification.body}
                </p>
            </div>
        </>
    );
}

import { Head, Link } from '@inertiajs/react';
import { Bell, BellOff, ChevronRight } from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import { cn } from '@/lib/utils';
import type { Notification } from '@/types/models';

function dateGroupLabel(isoDate: string): string {
    const date = new Date(isoDate);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    }

    if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    }

    return date.toLocaleDateString(undefined, {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

export default function NotificationsIndex({
    notifications,
}: {
    notifications: Notification[];
}) {
    const groups = new Map<string, Notification[]>();

    for (const notification of notifications) {
        const label = dateGroupLabel(notification.created_at);
        groups.set(label, [...(groups.get(label) ?? []), notification]);
    }

    return (
        <>
            <Head title="Notifications" />
            <PageHeader title="Notifications" backHref="/home" />

            <div className="px-4 py-4">
                {notifications.length === 0 ? (
                    <div className="text-muted-foreground flex flex-col items-center gap-3 py-20 text-center text-sm">
                        <BellOff className="size-8" />
                        No notifications yet.
                    </div>
                ) : (
                    Array.from(groups.entries()).map(([label, items]) => (
                        <div key={label} className="mb-4">
                            <div className="border-border mb-2 flex items-center gap-3 border-b pb-2">
                                <span className="text-muted-foreground text-sm">
                                    {label}
                                </span>
                            </div>

                            <div className="divide-border divide-y">
                                {items.map((notification) => (
                                    <Link
                                        key={notification.id}
                                        href={`/notifications/${notification.id}`}
                                        className="flex items-center gap-3 py-3"
                                    >
                                        <span
                                            className={cn(
                                                'flex size-10 shrink-0 items-center justify-center rounded-full',
                                                notification.read
                                                    ? 'bg-muted text-muted-foreground'
                                                    : 'bg-primary-tint text-primary-tint-foreground',
                                            )}
                                        >
                                            <Bell className="size-5" />
                                        </span>

                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-semibold">
                                                {notification.title}
                                            </p>
                                            <p className="text-muted-foreground truncate text-sm">
                                                {notification.body}
                                            </p>
                                            <p className="text-muted-foreground mt-0.5 text-xs">
                                                {new Date(
                                                    notification.created_at,
                                                ).toLocaleTimeString([], {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })}
                                            </p>
                                        </div>

                                        <ChevronRight className="text-muted-foreground size-5 shrink-0" />
                                    </Link>
                                ))}
                            </div>
                        </div>
                    ))
                )}
            </div>
        </>
    );
}

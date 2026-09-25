import { Head, Link, usePage } from '@inertiajs/react';
import { Bell, ChevronRight, LogOut, User as UserIcon } from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import { PointsBadge } from '@/components/points-badge';
import { UserAvatar } from '@/components/user-avatar';
import { usePushSubscription } from '@/hooks/use-push-subscription';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types/auth';

export default function ProfileIndex({
    points,
    level,
}: {
    points: number;
    level: string | null;
}) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const push = usePushSubscription();

    return (
        <>
            <Head title="Profile" />
            <PageHeader title="Profile" backHref="/home" />

            <div className="flex flex-col gap-4 px-4 py-4">
                <div className="bg-muted flex flex-col items-center gap-2 rounded-2xl p-6 text-center">
                    <UserAvatar
                        name={auth.user.name}
                        className="size-20 text-xl"
                    />

                    {level && (
                        <span className="bg-primary text-primary-foreground inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold">
                            👑 {level}
                        </span>
                    )}

                    <p className="text-lg font-bold">{auth.user.name}</p>

                    <Link
                        href={edit()}
                        className="text-muted-foreground inline-flex items-center gap-1 text-sm"
                    >
                        Edit profile
                        <ChevronRight className="size-3.5" />
                    </Link>
                </div>

                <PointsBadge points={points} level={level} />

                <div className="bg-muted divide-border flex flex-col divide-y rounded-2xl">
                    <p className="text-muted-foreground px-4 pt-3 text-xs font-semibold tracking-wide uppercase">
                        Account
                    </p>

                    <Link
                        href="/leaderboard"
                        className="flex items-center gap-3 px-4 py-3"
                    >
                        <UserIcon className="text-muted-foreground size-4" />
                        <span className="flex-1 text-sm font-medium">
                            Leaderboard
                        </span>
                        <ChevronRight className="text-muted-foreground size-4" />
                    </Link>

                    {push.supported && (
                        <button
                            type="button"
                            disabled={push.subscribing}
                            onClick={() =>
                                push.subscribed
                                    ? push.unsubscribe()
                                    : push.subscribe()
                            }
                            className="flex w-full items-center gap-3 px-4 py-3 text-left disabled:opacity-60"
                        >
                            <Bell className="text-muted-foreground size-4" />
                            <span className="flex-1 text-sm font-medium">
                                Push notifications
                            </span>
                            <span className="text-muted-foreground text-xs">
                                {push.subscribed ? 'On' : 'Off'}
                            </span>
                        </button>
                    )}

                    <Link
                        href={logout()}
                        as="button"
                        className="flex w-full items-center gap-3 px-4 py-3 text-left"
                    >
                        <LogOut className="text-muted-foreground size-4" />
                        <span className="flex-1 text-sm font-medium">
                            Logout
                        </span>
                        <ChevronRight className="text-muted-foreground size-4" />
                    </Link>
                </div>
            </div>
        </>
    );
}

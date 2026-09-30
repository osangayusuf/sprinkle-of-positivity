import { Head, Link } from '@inertiajs/react';
import { PageHeader } from '@/components/page-header';
import { PointsBadge } from '@/components/points-badge';
import { UserAvatar } from '@/components/user-avatar';
import { cn } from '@/lib/utils';
import type { LeaderboardEntry } from '@/types/models';

const MEDALS: Record<number, string> = { 1: '🥇', 2: '🥈', 3: '🥉' };

// Tailwind can't statically discover a class built from an interpolated
// number (e.g. `w-[${n}%]`), so progress snaps to the nearest of these
// fixed, fully-literal width utilities instead.
const WIDTH_STEPS = [
    'w-0',
    'w-1/12',
    'w-2/12',
    'w-3/12',
    'w-4/12',
    'w-5/12',
    'w-6/12',
    'w-7/12',
    'w-8/12',
    'w-9/12',
    'w-10/12',
    'w-11/12',
    'w-full',
];

function progressWidthClass(progress: number): string {
    const step = Math.round(
        Math.min(1, Math.max(0, progress)) * (WIDTH_STEPS.length - 1),
    );

    return WIDTH_STEPS[step];
}

export default function LeaderboardIndex({
    entries,
    myPoints,
    myLevel,
}: {
    entries: LeaderboardEntry[];
    myPoints: number;
    myLevel: string | null;
}) {
    return (
        <>
            <Head title="Leaderboard" />
            <PageHeader title="Leaderboard" backHref="/home" />

            <div className="flex flex-col gap-4 px-4 py-4">
                <PointsBadge points={myPoints} level={myLevel} />

                <Link
                    href="/leaderboard/levels"
                    className="text-primary self-end text-sm font-semibold"
                >
                    View levels
                </Link>

                <div className="flex flex-col">
                    {entries.map((entry) => (
                        <div
                            key={entry.id}
                            className={cn(
                                'flex items-center gap-3 rounded-xl px-2 py-3',
                                entry.is_me &&
                                    'bg-primary text-primary-foreground',
                            )}
                        >
                            <span className="w-6 shrink-0 text-center text-sm font-semibold">
                                {MEDALS[entry.rank] ?? entry.rank}
                            </span>

                            <UserAvatar
                                name={entry.name}
                                src={entry.avatar}
                                className="size-9"
                            />

                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold">
                                    {entry.is_me
                                        ? `You {${entry.name}}`
                                        : entry.name}
                                </p>
                                <div className="bg-muted/30 mt-1.5 h-1.5 w-full max-w-32 overflow-hidden rounded-full">
                                    <div
                                        className={cn(
                                            'h-full rounded-full',
                                            progressWidthClass(entry.progress),
                                            entry.is_me
                                                ? 'bg-primary-foreground'
                                                : 'bg-primary',
                                        )}
                                    />
                                </div>
                            </div>

                            <span className="shrink-0 text-sm font-semibold">
                                {entry.points.toLocaleString()}
                            </span>
                        </div>
                    ))}

                    {entries.length === 0 && (
                        <p className="text-muted-foreground py-10 text-center text-sm">
                            No one has earned points yet.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

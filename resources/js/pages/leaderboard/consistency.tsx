import { Head, Link } from '@inertiajs/react';
import { PageHeader } from '@/components/page-header';
import { UserAvatar } from '@/components/user-avatar';
import { cn } from '@/lib/utils';

type Entry = {
    rank: number;
    name: string;
    avatar: string | null;
    group: string;
    current_streak: number;
    run_day: number;
    is_me: boolean;
};

type PartnerEntry = {
    name: string;
    avatar: string | null;
    participants: number;
    on_track: number;
    percent: number;
};

type LaggingEntry = {
    name: string;
    group: string;
    partner: string | null;
    reset_count: number;
};

export default function LeaderboardConsistency({
    entries,
    partners,
    lagging,
}: {
    entries: Entry[];
    partners: PartnerEntry[];
    lagging: LaggingEntry[] | null;
}) {
    return (
        <>
            <Head title="Consistency" />
            <PageHeader title="Consistency" backHref="/leaderboard" />

            <div className="flex flex-col gap-8 px-4 py-4">
                <Link
                    href="/leaderboard"
                    className="text-primary self-end text-sm font-semibold"
                >
                    Points leaderboard
                </Link>

                <section>
                    <h2 className="mb-2 text-base font-semibold">
                        Longest current streaks
                    </h2>
                    {entries.length === 0 && (
                        <p className="text-muted-foreground py-6 text-center text-sm">
                            No one has started a streak yet.
                        </p>
                    )}
                    {entries.map((entry) => (
                        <div
                            key={`${entry.rank}-${entry.name}`}
                            className={cn(
                                'flex items-center gap-3 rounded-xl px-2 py-3',
                                entry.is_me &&
                                    'bg-primary text-primary-foreground',
                            )}
                        >
                            <span className="w-6 shrink-0 text-center text-sm font-semibold">
                                {entry.rank}
                            </span>
                            <UserAvatar
                                name={entry.name}
                                src={entry.avatar}
                                className="size-9"
                            />
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold">
                                    {entry.is_me ? 'You' : entry.name}
                                </p>
                                <p className="truncate text-xs opacity-70">
                                    {entry.group}
                                </p>
                            </div>
                            <span className="shrink-0 text-sm font-semibold">
                                {entry.current_streak} days
                            </span>
                        </div>
                    ))}
                </section>

                <section>
                    <h2 className="mb-2 text-base font-semibold">
                        Most consistent partners
                    </h2>
                    {partners.length === 0 && (
                        <p className="text-muted-foreground py-6 text-center text-sm">
                            No partners have participants yet.
                        </p>
                    )}
                    {partners.map((partner, index) => (
                        <div
                            key={partner.name}
                            className="flex items-center gap-3 px-2 py-3"
                        >
                            <span className="w-6 shrink-0 text-center text-sm font-semibold">
                                {index + 1}
                            </span>
                            <UserAvatar
                                name={partner.name}
                                src={partner.avatar}
                                className="size-9"
                            />
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold">
                                    {partner.name}
                                </p>
                                <p className="text-muted-foreground text-xs">
                                    {partner.on_track} of {partner.participants}{' '}
                                    participants keeping up
                                </p>
                            </div>
                            <span className="shrink-0 text-sm font-semibold">
                                {partner.percent}%
                            </span>
                        </div>
                    ))}
                </section>

                {lagging && (
                    <section>
                        <h2 className="mb-2 text-base font-semibold">
                            Needs a check-in
                        </h2>
                        {lagging.length === 0 && (
                            <p className="text-muted-foreground py-6 text-center text-sm">
                                Everyone is keeping up.
                            </p>
                        )}
                        {lagging.map((entry) => (
                            <div
                                key={`${entry.group}-${entry.name}`}
                                className="flex items-center justify-between gap-3 px-2 py-3"
                            >
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-semibold">
                                        {entry.name}
                                    </p>
                                    <p className="text-muted-foreground truncate text-xs">
                                        {entry.group}
                                        {entry.partner
                                            ? ` · ${entry.partner}`
                                            : ''}
                                    </p>
                                </div>
                                <span className="text-muted-foreground shrink-0 text-xs">
                                    {entry.reset_count} restart
                                    {entry.reset_count === 1 ? '' : 's'}
                                </span>
                            </div>
                        ))}
                    </section>
                )}
            </div>
        </>
    );
}

import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import AnalyticsController from '@/actions/App/Http/Controllers/Admin/AnalyticsController';
import Heading from '@/components/heading';
import { UserAvatar } from '@/components/user-avatar';
import { formatRelativeTime } from '@/lib/format';
import { cn } from '@/lib/utils';

type Summary = {
    page_views: number;
    unique_visitors: number;
    guest_visitors: number;
    signed_in_visitors: number;
    logins: number;
    users_logged_in: number;
    new_signups: number;
    active_users: number;
    interactions: {
        insights: number;
        comments: number;
        reactions: number;
        quiz_answers: number;
        group_applications: number;
    };
};

type DailyVisits = {
    date: string;
    page_views: number;
    unique_visitors: number;
};

type TopPage = {
    path: string;
    page_views: number;
    unique_visitors: number;
};

type TopReferrer = {
    referrer: string;
    page_views: number;
};

type RecentLogin = {
    id: number;
    user: { id: number; name: string; avatar: string | null };
    logged_in_at: string;
};

type Props = {
    days: number;
    ranges: number[];
    summary: Summary;
    daily: DailyVisits[];
    topPages: TopPage[];
    topReferrers: TopReferrer[];
    recentLogins: RecentLogin[];
};

const numberFormat = new Intl.NumberFormat();

export default function AdminAnalytics({
    days,
    ranges,
    summary,
    daily,
    topPages,
    topReferrers,
    recentLogins,
}: Props) {
    const tiles = [
        {
            label: 'Unique visitors',
            value: summary.unique_visitors,
            detail: `${numberFormat.format(summary.page_views)} page views`,
        },
        {
            label: 'Guest visitors',
            value: summary.guest_visitors,
            detail: 'Browsed without signing in',
        },
        {
            label: 'Signed-in visitors',
            value: summary.signed_in_visitors,
            detail: 'Members who visited',
        },
        {
            label: 'Logins',
            value: summary.logins,
            detail: `${numberFormat.format(summary.users_logged_in)} different members`,
        },
        {
            label: 'New sign-ups',
            value: summary.new_signups,
            detail: 'Accounts created',
        },
        {
            label: 'Active members',
            value: summary.active_users,
            detail: 'Shared, commented, reacted or joined',
        },
    ];

    const interactions = [
        ['Insights shared', summary.interactions.insights],
        ['Comments', summary.interactions.comments],
        ['Reactions', summary.interactions.reactions],
        ['Quiz answers', summary.interactions.quiz_answers],
        ['Group applications', summary.interactions.group_applications],
    ] as const;

    return (
        <>
            <Head title="Analytics" />

            <div className="space-y-6">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <Heading
                        variant="small"
                        title="Analytics"
                        description="Who is visiting, signing in and taking part"
                    />

                    <nav
                        aria-label="Date range"
                        className="bg-muted flex rounded-lg p-1"
                    >
                        {ranges.map((range) => (
                            <Link
                                key={range}
                                href={AnalyticsController.index({
                                    query: { days: range },
                                })}
                                preserveScroll
                                aria-current={
                                    range === days ? 'page' : undefined
                                }
                                className={cn(
                                    'rounded-md px-3 py-1.5 text-sm font-medium',
                                    range === days
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                Last {range} days
                            </Link>
                        ))}
                    </nav>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {tiles.map((tile) => (
                        <div key={tile.label} className="rounded-lg border p-4">
                            <p className="text-muted-foreground text-sm">
                                {tile.label}
                            </p>
                            <p className="mt-1 text-3xl font-semibold tabular-nums">
                                {numberFormat.format(tile.value)}
                            </p>
                            <p className="text-muted-foreground mt-1 text-xs">
                                {tile.detail}
                            </p>
                        </div>
                    ))}
                </div>

                <section className="rounded-lg border p-4">
                    <h2 className="text-sm font-semibold">
                        Unique visitors per day
                    </h2>
                    <DailyVisitorsChart daily={daily} />
                </section>

                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="rounded-lg border">
                        <h2 className="border-b px-4 py-3 text-sm font-semibold">
                            Top pages
                        </h2>
                        {topPages.length === 0 ? (
                            <EmptyRow>No visits in this period.</EmptyRow>
                        ) : (
                            <table className="w-full text-sm">
                                <thead className="text-muted-foreground text-left">
                                    <tr>
                                        <th className="px-4 py-2 font-medium">
                                            Page
                                        </th>
                                        <th className="px-4 py-2 text-right font-medium">
                                            Views
                                        </th>
                                        <th className="px-4 py-2 text-right font-medium">
                                            Visitors
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {topPages.map((page) => (
                                        <tr key={page.path}>
                                            <td className="max-w-0 truncate px-4 py-2 font-mono text-xs">
                                                {page.path}
                                            </td>
                                            <td className="px-4 py-2 text-right tabular-nums">
                                                {numberFormat.format(
                                                    page.page_views,
                                                )}
                                            </td>
                                            <td className="px-4 py-2 text-right tabular-nums">
                                                {numberFormat.format(
                                                    page.unique_visitors,
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </section>

                    <section className="rounded-lg border">
                        <h2 className="border-b px-4 py-3 text-sm font-semibold">
                            Member activity
                        </h2>
                        <ul className="divide-y text-sm">
                            {interactions.map(([label, count]) => (
                                <li
                                    key={label}
                                    className="flex justify-between px-4 py-2"
                                >
                                    <span>{label}</span>
                                    <span className="tabular-nums">
                                        {numberFormat.format(count)}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </section>

                    <section className="rounded-lg border">
                        <h2 className="border-b px-4 py-3 text-sm font-semibold">
                            Recent logins
                        </h2>
                        {recentLogins.length === 0 ? (
                            <EmptyRow>No one has logged in yet.</EmptyRow>
                        ) : (
                            <ul className="divide-y text-sm">
                                {recentLogins.map((login) => (
                                    <li
                                        key={login.id}
                                        className="flex items-center gap-3 px-4 py-2"
                                    >
                                        <UserAvatar
                                            name={login.user.name}
                                            src={login.user.avatar}
                                            className="size-8 text-xs"
                                        />
                                        <span className="min-w-0 flex-1 truncate">
                                            {login.user.name}
                                        </span>
                                        <time
                                            dateTime={login.logged_in_at}
                                            className="text-muted-foreground text-xs"
                                        >
                                            {formatRelativeTime(
                                                login.logged_in_at,
                                            )}
                                        </time>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <section className="rounded-lg border">
                        <h2 className="border-b px-4 py-3 text-sm font-semibold">
                            Top referrers
                        </h2>
                        {topReferrers.length === 0 ? (
                            <EmptyRow>
                                No visits from other sites in this period.
                            </EmptyRow>
                        ) : (
                            <ul className="divide-y text-sm">
                                {topReferrers.map((referrer) => (
                                    <li
                                        key={referrer.referrer}
                                        className="flex justify-between gap-4 px-4 py-2"
                                    >
                                        <span className="min-w-0 truncate">
                                            {referrer.referrer}
                                        </span>
                                        <span className="tabular-nums">
                                            {numberFormat.format(
                                                referrer.page_views,
                                            )}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}

function EmptyRow({ children }: { children: string }) {
    return (
        <p className="text-muted-foreground px-4 py-6 text-sm">{children}</p>
    );
}

const CHART_HEIGHT = 160;
const BAR_GAP_RATIO = 0.2;

/**
 * A single-series bar chart of unique visitors per day. Bars are drawn as
 * SVG attributes (not Tailwind classes), so their runtime heights render.
 */
function DailyVisitorsChart({ daily }: { daily: DailyVisits[] }) {
    const [hovered, setHovered] = useState<DailyVisits | null>(null);
    const max = Math.max(1, ...daily.map((day) => day.unique_visitors));
    const barWidth = 100 / daily.length;
    const shown = hovered ?? daily[daily.length - 1];

    return (
        <div className="mt-3">
            <p className="text-muted-foreground h-5 text-xs" aria-live="polite">
                {shown && (
                    <>
                        <span className="text-foreground font-medium">
                            {formatDay(shown.date)}
                        </span>
                        {' · '}
                        {numberFormat.format(shown.unique_visitors)} visitors
                        {' · '}
                        {numberFormat.format(shown.page_views)} page views
                    </>
                )}
            </p>

            <svg
                viewBox={`0 0 100 ${CHART_HEIGHT}`}
                preserveAspectRatio="none"
                className="mt-2 h-40 w-full"
                role="img"
                aria-label={`Unique visitors per day, peaking at ${max}`}
                onMouseLeave={() => setHovered(null)}
            >
                <line
                    x1="0"
                    x2="100"
                    y1={CHART_HEIGHT}
                    y2={CHART_HEIGHT}
                    className="stroke-border"
                    strokeWidth="1"
                    vectorEffect="non-scaling-stroke"
                />
                {daily.map((day, index) => {
                    const height =
                        (day.unique_visitors / max) * (CHART_HEIGHT - 4);

                    return (
                        <g key={day.date} onMouseEnter={() => setHovered(day)}>
                            <rect
                                x={index * barWidth}
                                y="0"
                                width={barWidth}
                                height={CHART_HEIGHT}
                                className="fill-transparent"
                            />
                            <rect
                                x={
                                    index * barWidth +
                                    (barWidth * BAR_GAP_RATIO) / 2
                                }
                                y={CHART_HEIGHT - height}
                                width={barWidth * (1 - BAR_GAP_RATIO)}
                                height={height}
                                className={cn(
                                    'fill-primary',
                                    hovered &&
                                        hovered.date !== day.date &&
                                        'opacity-50',
                                )}
                            >
                                <title>
                                    {`${formatDay(day.date)}: ${day.unique_visitors} visitors, ${day.page_views} page views`}
                                </title>
                            </rect>
                        </g>
                    );
                })}
            </svg>

            <div className="text-muted-foreground mt-1 flex justify-between text-xs">
                <span>{daily[0] && formatDay(daily[0].date)}</span>
                <span>
                    {daily.length > 0 &&
                        formatDay(daily[daily.length - 1].date)}
                </span>
            </div>
        </div>
    );
}

function formatDay(date: string): string {
    return new Date(`${date}T00:00:00`).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
    });
}

AdminAnalytics.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Analytics', href: '/admin/analytics' },
    ],
};

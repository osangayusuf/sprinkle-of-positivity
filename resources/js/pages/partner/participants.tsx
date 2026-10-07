import { Head, Link, router } from '@inertiajs/react';
import { BellRing, BookOpenText, UserMinus } from 'lucide-react';
import { useState } from 'react';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { UserAvatar } from '@/components/user-avatar';
import { cn } from '@/lib/utils';

type Status = 'on_track' | 'at_risk' | 'lagging';

type Participant = {
    id: number;
    user: { id: number; name: string; avatar: string | null };
    group: { id: number; name: string; slug: string; duration_days: number };
    partner: { id: number; name: string } | null;
    status: Status;
    run_day: number;
    current_streak: number;
    completed_count: number;
    reset_count: number;
    completed_today: boolean;
    last_posted_at: string | null;
};

const STATUS_LABEL: Record<Status, string> = {
    on_track: 'Done today',
    at_risk: 'Yet to post',
    lagging: 'Behind',
};

const STATUS_CLASS: Record<Status, string> = {
    on_track: 'bg-success/15 text-success',
    at_risk: 'bg-amber-500/15 text-amber-600',
    lagging: 'bg-destructive/15 text-destructive',
};

export default function PartnerParticipants({
    participants,
    isAdmin,
}: {
    participants: Participant[];
    isAdmin: boolean;
}) {
    const [rejecting, setRejecting] = useState<number | null>(null);
    const [reason, setReason] = useState('');

    const counts = {
        on_track: participants.filter((p) => p.status === 'on_track').length,
        at_risk: participants.filter((p) => p.status === 'at_risk').length,
        lagging: participants.filter((p) => p.status === 'lagging').length,
    };

    function nudge(id: number) {
        router.post(
            `/partner/participants/${id}/nudge`,
            {},
            { preserveScroll: true },
        );
    }

    function reject(id: number) {
        router.post(
            `/partner/participants/${id}/reject`,
            { reason },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setRejecting(null);
                    setReason('');
                },
            },
        );
    }

    return (
        <>
            <Head title="My participants" />
            <PageHeader
                title={isAdmin ? 'All participants' : 'My participants'}
                backHref="/home"
            />

            <div className="flex flex-col gap-4 px-4 py-4">
                <div className="grid grid-cols-3 gap-2 text-center text-xs">
                    {(Object.keys(counts) as Status[]).map((status) => (
                        <div
                            key={status}
                            className={cn(
                                'rounded-xl px-2 py-3',
                                STATUS_CLASS[status],
                            )}
                        >
                            <p className="text-lg font-bold">
                                {counts[status]}
                            </p>
                            <p>{STATUS_LABEL[status]}</p>
                        </div>
                    ))}
                </div>

                {participants.length === 0 && (
                    <p className="text-muted-foreground py-10 text-center text-sm">
                        No participants are assigned yet.
                    </p>
                )}

                {participants.map((participant) => (
                    <div
                        key={participant.id}
                        className="border-border flex flex-col gap-3 rounded-2xl border p-4"
                    >
                        <div className="flex items-center gap-3">
                            <UserAvatar
                                name={participant.user.name}
                                src={participant.user.avatar}
                                className="size-10"
                            />
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold">
                                    {participant.user.name}
                                </p>
                                <p className="text-muted-foreground truncate text-xs">
                                    {participant.group.name}
                                    {isAdmin && participant.partner
                                        ? ` · ${participant.partner.name}`
                                        : ''}
                                </p>
                            </div>
                            <span
                                className={cn(
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    STATUS_CLASS[participant.status],
                                )}
                            >
                                {STATUS_LABEL[participant.status]}
                            </span>
                        </div>

                        <dl className="grid grid-cols-3 gap-2 text-center text-xs">
                            <div>
                                <dt className="text-muted-foreground">Day</dt>
                                <dd className="text-sm font-semibold">
                                    {participant.run_day}/
                                    {participant.group.duration_days}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">
                                    Streak
                                </dt>
                                <dd className="text-sm font-semibold">
                                    {participant.current_streak}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">
                                    Restarts
                                </dt>
                                <dd className="text-sm font-semibold">
                                    {participant.reset_count}
                                </dd>
                            </div>
                        </dl>

                        <p className="text-muted-foreground text-xs">
                            Last post:{' '}
                            {participant.last_posted_at
                                ? new Date(
                                      participant.last_posted_at,
                                  ).toLocaleString(undefined, {
                                      day: '2-digit',
                                      month: 'short',
                                      hour: '2-digit',
                                      minute: '2-digit',
                                  })
                                : 'never'}
                        </p>

                        {rejecting === participant.id ? (
                            <div className="flex flex-col gap-2">
                                <Textarea
                                    value={reason}
                                    onChange={(e) => setReason(e.target.value)}
                                    placeholder="Why can't you take this participant?"
                                    rows={3}
                                />
                                <div className="flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        disabled={reason.trim() === ''}
                                        onClick={() => reject(participant.id)}
                                    >
                                        Move to another partner
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        onClick={() => setRejecting(null)}
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </div>
                        ) : (
                            <div className="flex gap-2">
                                <Button asChild size="sm" variant="outline">
                                    <Link
                                        href={`/groups/${participant.group.slug}/members/${participant.user.id}/insights`}
                                    >
                                        <BookOpenText className="size-4" />
                                        Insights
                                    </Link>
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    className="flex-1"
                                    disabled={participant.completed_today}
                                    onClick={() => nudge(participant.id)}
                                >
                                    <BellRing className="size-4" />
                                    Nudge
                                </Button>
                                {!isAdmin && (
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        onClick={() =>
                                            setRejecting(participant.id)
                                        }
                                    >
                                        <UserMinus className="size-4" />
                                        Reassign
                                    </Button>
                                )}
                            </div>
                        )}
                    </div>
                ))}

                <Link
                    href="/leaderboard/consistency"
                    className="text-primary self-center text-sm font-semibold"
                >
                    View consistency leaderboard
                </Link>
            </div>
        </>
    );
}

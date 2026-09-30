import { Link } from '@inertiajs/react';
import { ReactionBar } from '@/components/reaction-bar';
import { UserAvatar } from '@/components/user-avatar';
import { formatRelativeTime } from '@/lib/format';
import type { Insight } from '@/types/models';

export function InsightCard({
    insight,
    groupSlug,
}: {
    insight: Insight;
    groupSlug: string;
}) {
    return (
        <div className="flex flex-col gap-3 py-4">
            <div className="flex items-center gap-3">
                <UserAvatar
                    name={insight.user.name}
                    src={insight.user.avatar}
                    className="size-9"
                />
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-semibold">
                        {insight.user.name}
                    </p>
                    <p className="text-muted-foreground text-xs">
                        {formatRelativeTime(insight.created_at)}
                    </p>
                </div>
            </div>

            <Link
                href={`/groups/${groupSlug}/insights/${insight.id}`}
                className="text-foreground text-sm leading-relaxed whitespace-pre-line"
            >
                {insight.body}
            </Link>

            {insight.image_url && (
                <Link href={`/groups/${groupSlug}/insights/${insight.id}`}>
                    <img
                        src={insight.image_url}
                        alt=""
                        className="max-h-64 w-full rounded-xl object-cover"
                    />
                </Link>
            )}

            <ReactionBar
                reactUrl={`/groups/${groupSlug}/insights/${insight.id}/reactions`}
                summary={insight.reaction_summary}
                myReaction={insight.my_reaction}
                commentsCount={insight.comments_count ?? 0}
                commentsHref={`/groups/${groupSlug}/insights/${insight.id}`}
            />
        </div>
    );
}

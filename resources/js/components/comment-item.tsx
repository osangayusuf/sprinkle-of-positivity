import { ReactionBar } from '@/components/reaction-bar';
import { UserAvatar } from '@/components/user-avatar';
import { formatRelativeTime } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Comment } from '@/types/models';

export function CommentItem({
    comment,
    buildReactUrl,
    nested = false,
}: {
    comment: Comment;
    buildReactUrl: (commentId: number) => string;
    nested?: boolean;
}) {
    return (
        <div className={cn('flex flex-col gap-2 py-3', nested && 'ml-10')}>
            <div className="flex items-start gap-3">
                <UserAvatar name={comment.user.name} className="size-8" />
                <div className="min-w-0 flex-1">
                    <div className="flex items-baseline gap-2">
                        <p className="truncate text-sm font-semibold">
                            {comment.user.name}
                        </p>
                        <p className="text-muted-foreground text-xs">
                            {formatRelativeTime(comment.created_at)}
                        </p>
                    </div>
                    <p className="text-foreground mt-1 text-sm leading-relaxed">
                        {comment.body}
                    </p>
                    <div className="mt-2">
                        <ReactionBar
                            reactUrl={buildReactUrl(comment.id)}
                            summary={comment.reaction_summary}
                            myReaction={comment.my_reaction}
                        />
                    </div>
                </div>
            </div>

            {comment.replies.map((reply) => (
                <CommentItem
                    key={reply.id}
                    comment={reply}
                    buildReactUrl={buildReactUrl}
                    nested
                />
            ))}
        </div>
    );
}

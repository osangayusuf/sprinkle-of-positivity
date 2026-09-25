import { Form, Head } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import CommentController from '@/actions/App/Http/Controllers/CommentController';
import { CommentItem } from '@/components/comment-item';
import { PageHeader } from '@/components/page-header';
import { ReactionBar } from '@/components/reaction-bar';
import { UserAvatar } from '@/components/user-avatar';
import { formatRelativeTime } from '@/lib/format';
import type { Comment, Group, Insight } from '@/types/models';

export default function InsightShow({
    group,
    insight,
    comments,
    canParticipate,
}: {
    group: Group;
    insight: Insight;
    comments: Comment[];
    canParticipate: boolean;
}) {
    const totalComments = countComments(comments);

    function buildReactUrl(commentId: number): string {
        return `/groups/${group.slug}/insights/${insight.id}/comments/${commentId}/reactions`;
    }

    return (
        <>
            <Head title={`${insight.user.name}'s insight`} />
            <PageHeader
                title="Insight"
                backHref={`/groups/${group.slug}/verse`}
            />

            <div className="flex flex-col gap-4 px-4 py-4">
                <div className="flex items-center gap-3">
                    <UserAvatar name={insight.user.name} />
                    <div>
                        <p className="text-sm font-semibold">
                            {insight.user.name}
                        </p>
                        <p className="text-muted-foreground text-xs">
                            {formatRelativeTime(insight.created_at)}
                        </p>
                    </div>
                </div>

                <p className="text-foreground text-sm leading-relaxed whitespace-pre-line">
                    {insight.body}
                </p>

                {insight.image_url && (
                    <img
                        src={insight.image_url}
                        alt=""
                        className="max-h-80 w-full rounded-xl object-cover"
                    />
                )}

                <ReactionBar
                    reactUrl={`/groups/${group.slug}/insights/${insight.id}/reactions`}
                    summary={insight.reaction_summary}
                    myReaction={insight.my_reaction}
                />

                <div className="mt-4 flex items-center justify-between">
                    <h2 className="text-base font-semibold">Comments</h2>
                    <span className="text-muted-foreground text-sm">
                        {totalComments} total
                    </span>
                </div>

                <div className="divide-border divide-y">
                    {comments.map((comment) => (
                        <CommentItem
                            key={comment.id}
                            comment={comment}
                            buildReactUrl={buildReactUrl}
                        />
                    ))}

                    {comments.length === 0 && (
                        <p className="text-muted-foreground py-6 text-center text-sm">
                            No comments yet — be the first to respond.
                        </p>
                    )}
                </div>

                {canParticipate && (
                    <Form
                        key={totalComments}
                        {...CommentController.store.form({
                            group: group.slug,
                            insight: insight.id,
                        })}
                        resetOnSuccess
                        className="bg-background sticky bottom-20 flex items-center gap-2 pt-3"
                    >
                        {({ processing }) => (
                            <>
                                <input
                                    type="text"
                                    name="body"
                                    required
                                    placeholder="Add comment"
                                    className="bg-muted h-11 flex-1 rounded-full px-4 text-sm outline-none"
                                />
                                <button
                                    type="submit"
                                    disabled={processing}
                                    aria-label="Post comment"
                                    className="bg-primary text-primary-foreground flex size-11 shrink-0 items-center justify-center rounded-full"
                                >
                                    <ArrowRight className="size-5" />
                                </button>
                            </>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}

function countComments(comments: Comment[]): number {
    return comments.reduce(
        (total, comment) => total + 1 + countComments(comment.replies),
        0,
    );
}

import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { MentionTextarea } from '@/components/mention-textarea';
import { MessageBody } from '@/components/message-body';
import { ReactionBar } from '@/components/reaction-bar';
import { Button } from '@/components/ui/button';
import { UserAvatar } from '@/components/user-avatar';
import { displayMentions, serializeMentions } from '@/lib/chat-message';
import { formatRelativeTime } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { ChatMember, ChatMention, Comment } from '@/types/models';

export function CommentItem({
    comment,
    buildReactUrl,
    buildEditUrl,
    members,
    nested = false,
}: {
    comment: Comment;
    buildReactUrl: (commentId: number) => string;
    buildEditUrl: (commentId: number) => string;
    members: ChatMember[];
    nested?: boolean;
}) {
    const [isEditing, setIsEditing] = useState(false);

    return (
        <div className={cn('flex flex-col gap-2 py-3', nested && 'ml-10')}>
            <div className="flex items-start gap-3">
                <UserAvatar
                    name={comment.user.name}
                    src={comment.user.avatar}
                    className="size-8"
                />
                <div className="min-w-0 flex-1">
                    <div className="flex items-baseline gap-2">
                        <p className="truncate text-sm font-semibold">
                            {comment.user.name}
                        </p>
                        <p className="text-muted-foreground text-xs">
                            {formatRelativeTime(comment.created_at)}
                            {comment.edited && ' · edited'}
                        </p>
                        {comment.can_edit && !isEditing && (
                            <button
                                type="button"
                                onClick={() => setIsEditing(true)}
                                className="text-muted-foreground text-xs font-semibold"
                            >
                                Edit
                            </button>
                        )}
                    </div>
                    {isEditing ? (
                        <EditCommentForm
                            comment={comment}
                            editUrl={buildEditUrl(comment.id)}
                            members={members}
                            onDone={() => setIsEditing(false)}
                        />
                    ) : (
                        <p className="text-foreground mt-1 text-sm leading-relaxed">
                            <MessageBody
                                body={comment.body}
                                mentions={comment.mentions}
                            />
                        </p>
                    )}
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
                    buildEditUrl={buildEditUrl}
                    members={members}
                    nested
                />
            ))}
        </div>
    );
}

function EditCommentForm({
    comment,
    editUrl,
    members,
    onDone,
}: {
    comment: Comment;
    editUrl: string;
    members: ChatMember[];
    onDone: () => void;
}) {
    const [draft, setDraft] = useState(
        displayMentions(comment.body, comment.mentions),
    );
    const [tagged, setTagged] = useState<ChatMention[]>(comment.mentions);

    return (
        <Form
            action={editUrl}
            method="patch"
            options={{ preserveScroll: true }}
            onSuccess={onDone}
            className="mt-1 grid gap-2"
        >
            {({ processing }) => (
                <>
                    <input
                        type="hidden"
                        name="body"
                        value={serializeMentions(draft, tagged)}
                    />
                    <MentionTextarea
                        value={draft}
                        onValueChange={setDraft}
                        members={members}
                        onTag={(member) =>
                            setTagged((current) => [
                                ...current.filter(
                                    (mention) => mention.id !== member.id,
                                ),
                                { id: member.id, name: member.name },
                            ])
                        }
                        rows={2}
                        required
                        maxLength={2000}
                        aria-label="Edit comment"
                        autoFocus
                        className="bg-muted rounded-2xl border-transparent px-4 py-3"
                    />
                    <div className="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            onClick={onDone}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" size="sm" disabled={processing}>
                            Save
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}

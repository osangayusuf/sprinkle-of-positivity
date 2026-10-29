import { Form, Head, router, usePoll } from '@inertiajs/react';
import { Pencil, Reply, Send, Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import GroupChatController from '@/actions/App/Http/Controllers/GroupChatController';
import InputError from '@/components/input-error';
import { MentionTextarea } from '@/components/mention-textarea';
import { MessageBody } from '@/components/message-body';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { UserAvatar } from '@/components/user-avatar';
import { displayMentions, serializeMentions } from '@/lib/chat-message';
import { formatRelativeTime } from '@/lib/format';
import { cn } from '@/lib/utils';
import type {
    ChatMember,
    ChatMention,
    ChatMessage,
    Group,
} from '@/types/models';

const POLL_INTERVAL_MS = 5000;
const PAGE_SIZE = 50;
const NEAR_BOTTOM_PX = 160;

function isNearBottom(): boolean {
    return (
        window.innerHeight + window.scrollY >=
        document.documentElement.scrollHeight - NEAR_BOTTOM_PX
    );
}

export default function GroupChat({
    group,
    messages,
    members,
    hasMore,
    limit,
}: {
    group: Group;
    messages: ChatMessage[];
    members: ChatMember[];
    hasMore: boolean;
    limit: number;
}) {
    const bottomRef = useRef<HTMLDivElement>(null);
    const followNewMessages = useRef(true);
    const lastMessageId = messages.at(-1)?.id;
    const [draft, setDraft] = useState('');
    const [tagged, setTagged] = useState<ChatMention[]>([]);
    const [replyingTo, setReplyingTo] = useState<ChatMessage | null>(null);
    const [editing, setEditing] = useState<ChatMessage | null>(null);
    const composerRef = useRef<HTMLDivElement>(null);

    // Only the messages change between polls; the poll reuses the current
    // URL, so a raised `limit` (earlier history) is kept.
    usePoll(POLL_INTERVAL_MS, { only: ['messages', 'hasMore'] });

    useEffect(() => {
        const onScroll = () => {
            followNewMessages.current = isNearBottom();
        };

        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    // Open at the newest message. Arriving through an Inertia link resets the
    // scroll position just after the first render, so scroll again once that
    // has happened.
    useEffect(() => {
        const scrollToBottom = () =>
            bottomRef.current?.scrollIntoView({ block: 'end' });

        scrollToBottom();

        const timer = window.setTimeout(scrollToBottom, 100);

        return () => window.clearTimeout(timer);
    }, []);

    // Jump to the newest message on arrival, unless the reader has scrolled
    // up to read history.
    useEffect(() => {
        if (followNewMessages.current) {
            bottomRef.current?.scrollIntoView({ block: 'end' });
        }
    }, [lastMessageId]);

    function resetComposer() {
        setDraft('');
        setTagged([]);
        setReplyingTo(null);
        setEditing(null);
    }

    function startEdit(message: ChatMessage) {
        setReplyingTo(null);
        setEditing(message);
        setDraft(displayMentions(message.body, message.mentions));
        setTagged(message.mentions);
        requestAnimationFrame(() =>
            composerRef.current?.querySelector('textarea')?.focus(),
        );
    }

    function startReply(message: ChatMessage) {
        if (editing) {
            resetComposer();
        }

        setReplyingTo(message);
        requestAnimationFrame(() =>
            composerRef.current?.querySelector('textarea')?.focus(),
        );
    }

    function loadEarlier() {
        router.get(
            GroupChatController.show.url({ group: group.slug }),
            { limit: limit + PAGE_SIZE },
            {
                only: ['messages', 'hasMore', 'limit'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    return (
        <>
            <Head title={`${group.name} chat`} />
            <PageHeader
                title={`${group.name} chat`}
                backHref={`/groups/${group.slug}`}
            />

            <div className="flex flex-col gap-3 px-4 pt-4">
                {hasMore && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={loadEarlier}
                        className="self-center rounded-full"
                    >
                        Load earlier messages
                    </Button>
                )}

                {messages.length === 0 && (
                    <p className="text-muted-foreground py-16 text-center text-sm">
                        No messages yet. Say hello to the group!
                    </p>
                )}

                {messages.map((message) => (
                    <MessageBubble
                        key={message.id}
                        message={message}
                        onReply={() => startReply(message)}
                        onEdit={() => startEdit(message)}
                        deleteUrl={GroupChatController.destroy.url({
                            group: group.slug,
                            message: message.id,
                        })}
                    />
                ))}

                <div ref={bottomRef} className="scroll-mb-40" />
            </div>

            <div
                ref={composerRef}
                className="bg-background border-border sticky bottom-[3.75rem] z-10 mt-4 border-t px-4 py-3"
            >
                {editing && (
                    <div className="bg-muted border-l-primary mb-2 flex items-start gap-2 rounded-xl border-l-4 px-3 py-2 text-xs">
                        <div className="min-w-0 flex-1">
                            <p className="font-semibold">Editing message</p>
                            <p className="text-muted-foreground line-clamp-2 break-words">
                                {editing.preview}
                            </p>
                        </div>
                        <button
                            type="button"
                            aria-label="Cancel edit"
                            onClick={resetComposer}
                            className="text-muted-foreground"
                        >
                            <X className="size-4" />
                        </button>
                    </div>
                )}
                {replyingTo && (
                    <div className="bg-muted mb-2 flex items-start gap-2 rounded-xl border-l-4 border-l-primary px-3 py-2 text-xs">
                        <div className="min-w-0 flex-1">
                            <p className="font-semibold">
                                Replying to{' '}
                                {replyingTo.mine
                                    ? 'yourself'
                                    : replyingTo.author.name}
                            </p>
                            <p className="text-muted-foreground line-clamp-2 break-words">
                                {replyingTo.preview}
                            </p>
                        </div>
                        <button
                            type="button"
                            aria-label="Cancel reply"
                            onClick={() => setReplyingTo(null)}
                            className="text-muted-foreground"
                        >
                            <X className="size-4" />
                        </button>
                    </div>
                )}
                <Form
                    {...(editing
                        ? GroupChatController.update.form({
                              group: group.slug,
                              message: editing.id,
                          })
                        : GroupChatController.store.form({
                              group: group.slug,
                          }))}
                    resetOnSuccess
                    options={{ preserveScroll: true }}
                    onSuccess={() => {
                        followNewMessages.current = true;
                        resetComposer();
                    }}
                    className="flex items-end gap-2"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid flex-1 gap-1">
                                <input
                                    type="hidden"
                                    name="body"
                                    value={serializeMentions(draft, tagged)}
                                />
                                {!editing && (
                                    <input
                                        type="hidden"
                                        name="reply_to_id"
                                        value={replyingTo?.id ?? ''}
                                    />
                                )}
                                <MentionTextarea
                                    value={draft}
                                    onValueChange={setDraft}
                                    members={members}
                                    onTag={(member) =>
                                        setTagged((current) => [
                                            ...current.filter(
                                                (mention) =>
                                                    mention.id !== member.id,
                                            ),
                                            {
                                                id: member.id,
                                                name: member.name,
                                            },
                                        ])
                                    }
                                    rows={1}
                                    required
                                    maxLength={1000}
                                    aria-label="Message"
                                    placeholder="Write a message"
                                    onKeyDown={(event) => {
                                        if (
                                            event.key === 'Enter' &&
                                            !event.shiftKey
                                        ) {
                                            event.preventDefault();
                                            event.currentTarget.form?.requestSubmit();
                                        }
                                    }}
                                    className="bg-muted max-h-32 min-h-11 rounded-2xl border-transparent px-4 py-3"
                                />
                                <InputError message={errors.body} />
                            </div>
                            <Button
                                type="submit"
                                disabled={processing}
                                size="icon"
                                aria-label="Send message"
                                className="size-11 shrink-0 rounded-full"
                            >
                                {processing ? <Spinner /> : <Send />}
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

function MessageBubble({
    message,
    onReply,
    onEdit,
    deleteUrl,
}: {
    message: ChatMessage;
    onReply: () => void;
    onEdit: () => void;
    deleteUrl: string;
}) {
    return (
        <div
            id={`message-${message.id}`}
            className={cn(
                'scroll-mt-20',
                'flex items-end gap-2',
                message.mine && 'flex-row-reverse',
            )}
        >
            <UserAvatar
                name={message.author.name}
                src={message.author.avatar}
                className="size-8 shrink-0"
            />

            <div
                className={cn(
                    'flex max-w-[80%] min-w-0 flex-col gap-1',
                    message.mine && 'items-end',
                )}
            >
                <div className="text-muted-foreground flex items-center gap-2 text-xs">
                    <span className="truncate font-semibold">
                        {message.mine ? 'You' : message.author.name}
                    </span>
                    {message.author.is_manager && (
                        <span className="bg-primary-tint text-primary-tint-foreground rounded-full px-2 py-0.5 text-[10px] font-semibold">
                            Accountability partner
                        </span>
                    )}
                    <span>
                        {formatRelativeTime(message.created_at)}
                        {message.edited && ' · edited'}
                    </span>
                    <button
                        type="button"
                        aria-label="Reply to message"
                        onClick={onReply}
                        className="text-muted-foreground"
                    >
                        <Reply className="size-3.5" />
                    </button>
                    {message.can_edit && (
                        <button
                            type="button"
                            aria-label="Edit message"
                            onClick={onEdit}
                            className="text-muted-foreground"
                        >
                            <Pencil className="size-3.5" />
                        </button>
                    )}
                    {message.can_delete && (
                        <DeleteMessage deleteUrl={deleteUrl} />
                    )}
                </div>

                <div
                    className={cn(
                        'rounded-2xl px-4 py-2 text-sm break-words whitespace-pre-line',
                        message.mine
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-foreground',
                    )}
                >
                    {message.reply_to && (
                        <button
                            type="button"
                            onClick={() =>
                                document
                                    .getElementById(
                                        `message-${message.reply_to?.id}`,
                                    )
                                    ?.scrollIntoView({
                                        block: 'center',
                                        behavior: 'smooth',
                                    })
                            }
                            className="bg-background/20 mb-1 block w-full rounded-lg border-l-4 border-l-current px-2 py-1 text-left text-xs"
                        >
                            <span className="block font-semibold">
                                {message.reply_to.author_name}
                            </span>
                            <span className="line-clamp-2 break-words opacity-80">
                                {message.reply_to.preview}
                            </span>
                        </button>
                    )}
                    <MessageBody
                        body={message.body}
                        mentions={message.mentions}
                    />
                </div>
            </div>
        </div>
    );
}

function DeleteMessage({ deleteUrl }: { deleteUrl: string }) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <button
                    type="button"
                    aria-label="Delete message"
                    className="text-muted-foreground"
                >
                    <Trash2 className="size-3.5" />
                </button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Delete this message?</DialogTitle>
                <DialogDescription>
                    It will be removed for everyone in the group.
                </DialogDescription>

                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>

                    <Form
                        action={deleteUrl}
                        method="delete"
                        options={{ preserveScroll: true }}
                    >
                        <Button type="submit" variant="destructive">
                            Delete message
                        </Button>
                    </Form>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

import { Form, Head, router, usePoll } from '@inertiajs/react';
import { Send, Trash2 } from 'lucide-react';
import { useEffect, useRef } from 'react';
import GroupChatController from '@/actions/App/Http/Controllers/GroupChatController';
import InputError from '@/components/input-error';
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
import { Textarea } from '@/components/ui/textarea';
import { UserAvatar } from '@/components/user-avatar';
import { formatRelativeTime } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { ChatMessage, Group } from '@/types/models';

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
    hasMore,
    limit,
}: {
    group: Group;
    messages: ChatMessage[];
    hasMore: boolean;
    limit: number;
}) {
    const bottomRef = useRef<HTMLDivElement>(null);
    const followNewMessages = useRef(true);
    const lastMessageId = messages.at(-1)?.id;

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
                        deleteUrl={GroupChatController.destroy.url({
                            group: group.slug,
                            message: message.id,
                        })}
                    />
                ))}

                <div ref={bottomRef} className="scroll-mb-40" />
            </div>

            <div className="bg-background border-border sticky bottom-[3.75rem] z-10 mt-4 border-t px-4 py-3">
                <Form
                    {...GroupChatController.store.form({ group: group.slug })}
                    resetOnSuccess
                    options={{ preserveScroll: true }}
                    onSuccess={() => {
                        followNewMessages.current = true;
                    }}
                    className="flex items-end gap-2"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid flex-1 gap-1">
                                <Textarea
                                    name="body"
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
    deleteUrl,
}: {
    message: ChatMessage;
    deleteUrl: string;
}) {
    return (
        <div
            className={cn(
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
                    <span>{formatRelativeTime(message.created_at)}</span>
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
                    {message.body}
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

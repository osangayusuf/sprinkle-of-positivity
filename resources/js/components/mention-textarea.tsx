import { Users } from 'lucide-react';
import { useRef, useState } from 'react';
import { Textarea } from '@/components/ui/textarea';
import { UserAvatar } from '@/components/user-avatar';
import { cn } from '@/lib/utils';
import type { ChatMember } from '@/types/models';

type Suggestion = Omit<ChatMember, 'id'> & { id: number | null };

const MENTION_QUERY = /(?:^|\s)@([^\s@]*)$/;
const MAX_SUGGESTIONS = 20;
const EVERYONE = 'everyone';

/**
 * A textarea that suggests group members when the user types "@", plus an
 * "everyone" option that tags the whole group. Picking a member inserts
 * "@Name" and reports it through `onTag`, so the caller can turn the visible
 * names into mention tokens on submit; "@everyone" is plain text. While the suggestions are open
 * Enter picks one instead of submitting.
 */
export function MentionTextarea({
    value,
    onValueChange,
    members,
    onTag,
    className,
    ...props
}: Omit<React.ComponentProps<typeof Textarea>, 'value' | 'onChange'> & {
    value: string;
    onValueChange: (value: string) => void;
    members: ChatMember[];
    onTag: (member: ChatMember) => void;
}) {
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const [caret, setCaret] = useState(0);
    const [activeIndex, setActiveIndex] = useState(0);
    const [dismissed, setDismissed] = useState(false);

    const query = value.slice(0, caret).match(MENTION_QUERY)?.[1];
    const suggestions: Suggestion[] =
        query === undefined || dismissed
            ? []
            : [
                  ...(EVERYONE.startsWith(query.toLowerCase())
                      ? [{ id: null, name: EVERYONE, avatar: null }]
                      : []),
                  ...members.filter((member) =>
                      member.name.toLowerCase().includes(query.toLowerCase()),
                  ),
              ].slice(0, MAX_SUGGESTIONS);
    const isOpen = suggestions.length > 0;

    function pick(member: Suggestion) {
        if (query === undefined) {
            return;
        }

        const queryStart = caret - query.length - 1;
        const inserted = `@${member.name} `;

        onValueChange(
            value.slice(0, queryStart) + inserted + value.slice(caret),
        );
        if (member.id !== null) {
            onTag({
                id: member.id,
                name: member.name,
                avatar: member.avatar,
            });
        }

        setActiveIndex(0);

        const nextCaret = queryStart + inserted.length;

        setCaret(nextCaret);
        requestAnimationFrame(() => {
            textareaRef.current?.focus();
            textareaRef.current?.setSelectionRange(nextCaret, nextCaret);
        });
    }

    function onKeyDown(event: React.KeyboardEvent<HTMLTextAreaElement>) {
        if (isOpen) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                setActiveIndex(
                    (activeIndex +
                        (event.key === 'ArrowDown' ? 1 : -1) +
                        suggestions.length) %
                        suggestions.length,
                );

                return;
            }

            if (event.key === 'Enter' || event.key === 'Tab') {
                event.preventDefault();
                pick(
                    suggestions[Math.min(activeIndex, suggestions.length - 1)],
                );

                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                setDismissed(true);

                return;
            }
        }

        props.onKeyDown?.(event);
    }

    return (
        <div className="relative">
            {isOpen && (
                <ul
                    role="listbox"
                    aria-label="Mention a member"
                    className="bg-background border-border absolute bottom-full left-0 mb-2 max-h-48 w-full overflow-y-auto rounded-xl border p-1 shadow-lg"
                >
                    {suggestions.map((member, index) => (
                        <li
                            key={member.id ?? EVERYONE}
                            role="option"
                            aria-selected={index === activeIndex}
                            onMouseDown={(event) => {
                                event.preventDefault();
                                pick(member);
                            }}
                            className={cn(
                                'flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm',
                                index === activeIndex && 'bg-muted',
                            )}
                        >
                            {member.id === null ? (
                                <>
                                    <span className="bg-primary-tint text-primary-tint-foreground flex size-6 shrink-0 items-center justify-center rounded-full">
                                        <Users className="size-3.5" />
                                    </span>
                                    <span className="truncate">
                                        <span className="font-semibold">
                                            everyone
                                        </span>{' '}
                                        <span className="text-muted-foreground">
                                            Notify the whole group
                                        </span>
                                    </span>
                                </>
                            ) : (
                                <>
                                    <UserAvatar
                                        name={member.name}
                                        src={member.avatar}
                                        className="size-6 text-[10px]"
                                    />
                                    <span className="truncate">
                                        {member.name}
                                    </span>
                                </>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            <Textarea
                {...props}
                ref={textareaRef}
                value={value}
                className={className}
                onKeyDown={onKeyDown}
                onChange={(event) => {
                    onValueChange(event.target.value);
                    setCaret(event.target.selectionStart);
                    setActiveIndex(0);
                    setDismissed(false);
                }}
                onSelect={(event) =>
                    setCaret(event.currentTarget.selectionStart)
                }
            />
        </div>
    );
}

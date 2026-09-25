import { Link, router } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import type { ReactionSummary } from '@/types/models';

const EMOJIS = ['🔥', '😊', '😲', '❤️', '🙏'];

export function ReactionBar({
    reactUrl,
    summary,
    myReaction,
    commentsCount,
    commentsHref,
}: {
    reactUrl: string;
    summary: ReactionSummary;
    myReaction: string | null;
    commentsCount?: number;
    commentsHref?: string;
}) {
    function react(emoji: string) {
        router.post(
            reactUrl,
            { emoji },
            { preserveScroll: true, preserveState: false },
        );
    }

    return (
        <div className="flex items-center gap-2">
            {typeof commentsCount === 'number' &&
                (commentsHref ? (
                    <Link
                        href={commentsHref}
                        className="bg-muted text-muted-foreground flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium"
                    >
                        <MessageCircle className="size-3.5" />
                        {commentsCount}
                    </Link>
                ) : (
                    <span className="bg-muted text-muted-foreground flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium">
                        <MessageCircle className="size-3.5" />
                        {commentsCount}
                    </span>
                ))}

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <button
                        type="button"
                        className={cn(
                            'flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium',
                            myReaction
                                ? 'bg-primary-tint text-primary-tint-foreground'
                                : 'bg-muted text-muted-foreground',
                        )}
                    >
                        {summary.emojis.length > 0
                            ? summary.emojis.join('')
                            : '🤍'}
                        {summary.count > 0 && <span>{summary.count}</span>}
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    align="start"
                    className="flex min-w-0 items-center gap-1 p-1"
                >
                    {EMOJIS.map((emoji) => (
                        <DropdownMenuItem
                            key={emoji}
                            onSelect={() => react(emoji)}
                            className={cn(
                                'w-auto justify-center rounded-full text-base',
                                myReaction === emoji && 'bg-primary-tint',
                            )}
                        >
                            {emoji}
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}

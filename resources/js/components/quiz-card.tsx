import { Form, Link, router } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { UserAvatar } from '@/components/user-avatar';
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
import { useGuestRedirect } from '@/hooks/use-guest-redirect';
import { formatRelativeTime } from '@/lib/format';
import { progressWidthClass } from '@/lib/progress-width';
import { cn } from '@/lib/utils';
import type { Quiz } from '@/types/models';

export function QuizCard({
    quiz,
    respondUrl,
    canManage = false,
    editUrl,
    deleteUrl,
    closeUrl,
    reopenUrl,
}: {
    quiz: Quiz;
    respondUrl: string;
    canManage?: boolean;
    editUrl?: string;
    deleteUrl?: string;
    closeUrl?: string;
    reopenUrl?: string;
}) {
    const isPoll = quiz.type === 'poll';
    const noun = isPoll ? 'poll' : 'question';
    const answered = quiz.my_response_option_id !== null;
    const hasResponses = quiz.responses_count > 0;
    // A quiz is answered once; a poll vote can change until it is closed.
    const locked = isPoll ? quiz.closed : answered;
    const showResults = isPoll && quiz.options.some((o) => o.votes != null);
    const totalVotes = quiz.options.reduce((sum, o) => sum + (o.votes ?? 0), 0);

    const redirectGuest = useGuestRedirect();

    function respond(optionId: number) {
        if (locked || quiz.my_response_option_id === optionId) {
            return;
        }

        if (redirectGuest()) {
            return;
        }

        router.post(
            respondUrl,
            { quiz_option_id: optionId },
            { preserveScroll: true },
        );
    }

    return (
        <div className="flex flex-col gap-3 py-4">
            <div className="flex items-center gap-3">
                <UserAvatar
                    name={quiz.created_by_name}
                    src={quiz.created_by_avatar}
                    className="size-9"
                />
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-semibold">
                        {quiz.created_by_name}
                    </p>
                    <p className="text-muted-foreground text-xs">
                        {formatRelativeTime(quiz.created_at)}
                    </p>
                </div>

                {canManage && (
                    <div className="flex items-center gap-3">
                        {!hasResponses && editUrl && (
                            <Link
                                href={editUrl}
                                aria-label={`Edit ${noun}`}
                                className="text-muted-foreground"
                            >
                                <Pencil className="size-4" />
                            </Link>
                        )}

                        {deleteUrl && (
                            <Dialog>
                                <DialogTrigger asChild>
                                    <button
                                        type="button"
                                        aria-label={`Delete ${noun}`}
                                        className="text-muted-foreground"
                                    >
                                        <Trash2 className="size-4" />
                                    </button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>
                                        Delete this {noun}?
                                    </DialogTitle>
                                    <DialogDescription>
                                        {hasResponses
                                            ? isPoll
                                                ? 'Members have already voted in this poll — deleting it removes their votes. This cannot be undone.'
                                                : 'Members have already answered this question — deleting it will also reverse the points they earned from it. This cannot be undone.'
                                            : 'This cannot be undone.'}
                                    </DialogDescription>

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button variant="secondary">
                                                Cancel
                                            </Button>
                                        </DialogClose>

                                        <Form
                                            action={deleteUrl}
                                            method="delete"
                                        >
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                            >
                                                Delete {noun}
                                            </Button>
                                        </Form>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                )}
            </div>

            <p className="text-foreground text-sm font-medium">
                {quiz.question}
            </p>

            <div className="flex flex-col gap-2">
                {quiz.options.map((option) => {
                    const selected = quiz.my_response_option_id === option.id;
                    const votes = option.votes ?? 0;
                    const share = totalVotes > 0 ? votes / totalVotes : 0;

                    if (isPoll) {
                        return (
                            <button
                                key={option.id}
                                type="button"
                                disabled={locked}
                                aria-pressed={selected}
                                onClick={() => respond(option.id)}
                                className={cn(
                                    'border-border relative overflow-hidden rounded-xl border px-4 py-3 text-left text-sm font-medium',
                                    selected
                                        ? 'border-primary text-primary-tint-foreground'
                                        : 'text-foreground',
                                    locked && !selected && 'opacity-80',
                                )}
                            >
                                {showResults && (
                                    <span
                                        aria-hidden="true"
                                        className={cn(
                                            'absolute inset-y-0 left-0',
                                            selected
                                                ? 'bg-primary-tint'
                                                : 'bg-muted',
                                            progressWidthClass(share),
                                        )}
                                    />
                                )}
                                <span className="relative flex items-center justify-between gap-3">
                                    <span>{option.label}</span>
                                    {showResults && (
                                        <span className="text-muted-foreground shrink-0 text-xs">
                                            {Math.round(share * 100)}% · {votes}
                                        </span>
                                    )}
                                </span>
                            </button>
                        );
                    }

                    const isCorrect = quiz.correct_quiz_option_id === option.id;
                    const isWrongPick = answered && selected && !isCorrect;

                    return (
                        <button
                            key={option.id}
                            type="button"
                            disabled={answered}
                            onClick={() => respond(option.id)}
                            className={cn(
                                'border-border rounded-xl border px-4 py-3 text-left text-sm font-medium',
                                answered && isCorrect
                                    ? 'border-success bg-success/10 text-foreground'
                                    : isWrongPick
                                      ? 'border-destructive bg-destructive/10 text-foreground'
                                      : selected
                                        ? 'border-primary bg-primary-tint text-primary-tint-foreground'
                                        : 'text-foreground',
                                answered &&
                                    !selected &&
                                    !isCorrect &&
                                    'opacity-60',
                            )}
                        >
                            {option.label}
                        </button>
                    );
                })}
            </div>

            {isPoll && (
                <div className="text-muted-foreground flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span>
                        {quiz.responses_count} vote
                        {quiz.responses_count === 1 ? '' : 's'}
                        {quiz.closed
                            ? ' · Closed'
                            : answered
                              ? ' · Tap another option to change your vote'
                              : !showResults
                                ? ' · Results show after you vote'
                                : ''}
                    </span>

                    {canManage && (quiz.closed ? reopenUrl : closeUrl) && (
                        <button
                            type="button"
                            onClick={() =>
                                router.patch(
                                    (quiz.closed
                                        ? reopenUrl
                                        : closeUrl) as string,
                                    {},
                                    { preserveScroll: true },
                                )
                            }
                            className="text-primary font-semibold"
                        >
                            {quiz.closed ? 'Reopen poll' : 'Close poll'}
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}

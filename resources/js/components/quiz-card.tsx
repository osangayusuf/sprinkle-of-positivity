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
import { cn } from '@/lib/utils';
import type { Quiz } from '@/types/models';

export function QuizCard({
    quiz,
    respondUrl,
    canManage = false,
    editUrl,
    deleteUrl,
}: {
    quiz: Quiz;
    respondUrl: string;
    canManage?: boolean;
    editUrl?: string;
    deleteUrl?: string;
}) {
    const answered = quiz.my_response_option_id !== null;
    const hasResponses = quiz.responses_count > 0;

    const redirectGuest = useGuestRedirect();

    function respond(optionId: number) {
        if (answered || redirectGuest()) {
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
                                aria-label="Edit question"
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
                                        aria-label="Delete question"
                                        className="text-muted-foreground"
                                    >
                                        <Trash2 className="size-4" />
                                    </button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>
                                        Delete this question?
                                    </DialogTitle>
                                    <DialogDescription>
                                        {hasResponses
                                            ? 'Members have already answered this question — deleting it will also reverse the points they earned from it. This cannot be undone.'
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
                                                Delete question
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
        </div>
    );
}

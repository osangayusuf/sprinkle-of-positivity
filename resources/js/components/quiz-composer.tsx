import { Form } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';
import QuizController from '@/actions/App/Http/Controllers/QuizController';
import { QuizQuestionFields } from '@/components/quiz-question-fields';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

export function QuizComposer({ groupSlug }: { groupSlug: string }) {
    const [open, setOpen] = useState(false);
    const [type, setType] = useState<'quiz' | 'poll'>('quiz');

    if (!open) {
        return (
            <button
                type="button"
                onClick={() => setOpen(true)}
                className="border-border text-primary mt-4 flex items-center justify-center gap-2 rounded-xl border border-dashed py-3 text-sm font-semibold"
            >
                <Plus className="size-4" />
                Add a question or poll
            </button>
        );
    }

    return (
        <div className="bg-muted mt-4 rounded-2xl p-4">
            <Form
                {...QuizController.store.form({ group: groupSlug })}
                resetOnSuccess
                onSuccess={() => setOpen(false)}
                className="flex flex-col gap-4"
            >
                {({ processing, errors }) => (
                    <>
                        <input type="hidden" name="type" value={type} />

                        <div className="bg-background flex rounded-full p-1">
                            {(
                                [
                                    ['quiz', 'Question'],
                                    ['poll', 'Poll'],
                                ] as const
                            ).map(([value, label]) => (
                                <button
                                    key={value}
                                    type="button"
                                    aria-pressed={type === value}
                                    onClick={() => setType(value)}
                                    className={cn(
                                        'h-9 flex-1 rounded-full text-sm font-semibold',
                                        type === value
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-muted-foreground',
                                    )}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>

                        <QuizQuestionFields
                            key={type}
                            type={type}
                            errors={errors}
                        />

                        <div className="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                className="h-11 flex-1 rounded-full"
                                onClick={() => setOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="h-11 flex-1 rounded-full"
                            >
                                {processing && <Spinner />}
                                {type === 'poll'
                                    ? 'Post poll'
                                    : 'Post question'}
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}

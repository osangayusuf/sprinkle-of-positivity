import { X } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const MIN_OPTIONS = 2;
const MAX_OPTIONS = 6;

type OptionDefault = { id: number; label: string };

/**
 * The question/options/correct-answer fields shared by the quiz composer
 * (create) and quiz edit form. Expects to be mounted fresh per use (a new
 * `key`, or a parent that conditionally renders it) since its row state
 * isn't controlled from outside.
 */
export function QuizQuestionFields({
    defaultQuestion = '',
    defaultOptions,
    defaultCorrectIndex,
    errors,
    inputClassName = 'bg-background rounded-xl border-transparent px-4',
}: {
    defaultQuestion?: string;
    defaultOptions?: OptionDefault[];
    defaultCorrectIndex?: number;
    errors: Partial<Record<string, string>>;
    inputClassName?: string;
}) {
    const [optionIds, setOptionIds] = useState(
        defaultOptions?.length ? defaultOptions.map((_, i) => i) : [0, 1],
    );

    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="question">Question</Label>
                <Textarea
                    id="question"
                    name="question"
                    defaultValue={defaultQuestion}
                    required
                    rows={2}
                    className={inputClassName}
                />
                <InputError message={errors.question} />
            </div>

            <div className="grid gap-2">
                <Label>
                    Answer options{' '}
                    <span className="text-muted-foreground font-normal">
                        (select the correct one)
                    </span>
                </Label>
                {optionIds.map((id, index) => (
                    <div key={id} className="flex items-center gap-2">
                        <input
                            type="radio"
                            name="correct_index"
                            value={index}
                            required
                            defaultChecked={defaultCorrectIndex === index}
                            aria-label={`Option ${index + 1} is correct`}
                            className="accent-primary size-4 shrink-0"
                        />
                        <Input
                            name="options[]"
                            defaultValue={defaultOptions?.[index]?.label}
                            required
                            placeholder={`Option ${index + 1}`}
                            className={inputClassName}
                        />
                        {optionIds.length > MIN_OPTIONS && (
                            <button
                                type="button"
                                aria-label="Remove option"
                                onClick={() =>
                                    setOptionIds((ids) =>
                                        ids.filter((i) => i !== id),
                                    )
                                }
                                className="text-muted-foreground"
                            >
                                <X className="size-4" />
                            </button>
                        )}
                    </div>
                ))}
                <InputError message={errors.options} />

                {optionIds.length < MAX_OPTIONS && (
                    <button
                        type="button"
                        onClick={() =>
                            setOptionIds((ids) => [
                                ...ids,
                                (ids.at(-1) ?? 0) + 1,
                            ])
                        }
                        className="text-primary self-start text-sm font-semibold"
                    >
                        + Add option
                    </button>
                )}
            </div>
        </>
    );
}

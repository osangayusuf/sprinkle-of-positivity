import { Form, Head } from '@inertiajs/react';
import QuizController from '@/actions/App/Http/Controllers/QuizController';
import { PageHeader } from '@/components/page-header';
import { QuizQuestionFields } from '@/components/quiz-question-fields';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { Group } from '@/types/models';

type QuizOption = { id: number; label: string; position: number };

type EditableQuiz = {
    id: number;
    question: string;
    options: QuizOption[];
    correct_quiz_option_id: number | null;
};

export default function QuizEdit({
    group,
    quiz,
}: {
    group: Group;
    quiz: EditableQuiz;
}) {
    const correctIndex = quiz.options.findIndex(
        (option) => option.id === quiz.correct_quiz_option_id,
    );

    return (
        <>
            <Head title={`Edit question — ${group.name}`} />
            <PageHeader
                title="Edit question"
                backHref={`/groups/${group.slug}/verse`}
            />

            <div className="px-4 py-6">
                <Form
                    {...QuizController.update.form({
                        group: group.slug,
                        quiz: quiz.id,
                    })}
                    className="flex flex-col gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <QuizQuestionFields
                                defaultQuestion={quiz.question}
                                defaultOptions={quiz.options}
                                defaultCorrectIndex={
                                    correctIndex >= 0 ? correctIndex : undefined
                                }
                                errors={errors}
                                inputClassName="bg-muted rounded-xl border-transparent px-4"
                            />

                            <Button
                                type="submit"
                                disabled={processing}
                                className="h-14 w-full rounded-full text-base"
                            >
                                {processing && <Spinner />}
                                Save changes
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

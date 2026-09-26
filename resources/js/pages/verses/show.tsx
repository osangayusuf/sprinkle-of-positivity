import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import { InsightCard } from '@/components/insight-card';
import { PageHeader } from '@/components/page-header';
import { QuizComposer } from '@/components/quiz-composer';
import { QuizCard } from '@/components/quiz-card';
import { StreakTracker } from '@/components/streak-tracker';
import { VerseCard } from '@/components/verse-card';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type {
    ChallengeProgress,
    Group,
    Insight,
    Quiz,
    Verse,
} from '@/types/models';

type Props = {
    group: Group;
    verse: Verse | null;
    canManage: boolean;
    canParticipate: boolean;
    insights: Insight[];
    quizzes: Quiz[];
    progress: ChallengeProgress | null;
};

type Tab = 'insights' | 'qa';

export default function VerseShow({
    group,
    verse,
    canManage,
    canParticipate,
    insights,
    quizzes,
    progress,
}: Props) {
    const [tab, setTab] = useState<Tab>('insights');

    const hasChallenge =
        group.current_day !== null &&
        group.duration_days !== null &&
        group.starts_on !== null;

    return (
        <>
            <Head title={`${group.name} — Today's verse`} />
            <PageHeader title={group.name} backHref={`/groups/${group.slug}`} />

            <div className="flex flex-col gap-6 px-4 py-6">
                {hasChallenge && (
                    <StreakTracker
                        currentDay={group.current_day as number}
                        totalDays={group.duration_days as number}
                        startsOn={group.starts_on as string}
                        progress={progress}
                    />
                )}

                {verse ? (
                    <VerseCard verse={verse} label="Today's verse" />
                ) : (
                    <div className="bg-muted text-muted-foreground rounded-2xl p-6 text-center text-sm">
                        No verse has been set for today yet.
                    </div>
                )}

                {canManage && (
                    <Button
                        asChild
                        variant="outline"
                        className="h-12 w-full rounded-full"
                    >
                        <Link href={`/groups/${group.slug}/verse/edit`}>
                            {verse ? "Edit today's verse" : "Set today's verse"}
                        </Link>
                    </Button>
                )}
            </div>

            {verse && (
                <div className="px-4">
                    <div className="border-border flex border-b">
                        {(
                            [
                                ['insights', 'Insights'],
                                ['qa', 'Q&A'],
                            ] as [Tab, string][]
                        ).map(([value, label]) => (
                            <button
                                key={value}
                                type="button"
                                onClick={() => setTab(value)}
                                className={cn(
                                    'flex-1 border-b-2 pb-3 text-sm font-semibold',
                                    tab === value
                                        ? 'border-primary text-primary'
                                        : 'text-muted-foreground border-transparent',
                                )}
                            >
                                {label}
                            </button>
                        ))}
                    </div>

                    {tab === 'insights' && (
                        <div className="flex flex-col">
                            {canParticipate && (
                                <Link
                                    href={`/groups/${group.slug}/insights/create`}
                                    className="border-border text-primary mt-4 flex items-center justify-center gap-2 rounded-xl border border-dashed py-3 text-sm font-semibold"
                                >
                                    <Plus className="size-4" />
                                    Share your reflection
                                </Link>
                            )}

                            {insights.length === 0 ? (
                                <p className="text-muted-foreground py-10 text-center text-sm">
                                    No insights have been shared yet.
                                </p>
                            ) : (
                                <div className="divide-border divide-y">
                                    {insights.map((insight) => (
                                        <InsightCard
                                            key={insight.id}
                                            insight={insight}
                                            groupSlug={group.slug}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    {tab === 'qa' && (
                        <div className="flex flex-col">
                            {canManage && (
                                <QuizComposer groupSlug={group.slug} />
                            )}

                            {quizzes.length === 0 ? (
                                <p className="text-muted-foreground py-10 text-center text-sm">
                                    No questions have been posted yet.
                                </p>
                            ) : (
                                <div className="divide-border divide-y">
                                    {quizzes.map((quiz) => (
                                        <QuizCard
                                            key={quiz.id}
                                            quiz={quiz}
                                            respondUrl={`/groups/${group.slug}/quizzes/${quiz.id}/responses`}
                                            canManage={canManage}
                                            editUrl={`/groups/${group.slug}/quizzes/${quiz.id}/edit`}
                                            deleteUrl={`/groups/${group.slug}/quizzes/${quiz.id}`}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            )}
        </>
    );
}

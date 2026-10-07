import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import { InsightCard } from '@/components/insight-card';
import { PageHeader } from '@/components/page-header';
import { QuizComposer } from '@/components/quiz-composer';
import { QuizCard } from '@/components/quiz-card';
import { SignInPrompt } from '@/components/sign-in-prompt';
import { StreakTracker } from '@/components/streak-tracker';
import { VerseCard } from '@/components/verse-card';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { Auth } from '@/types/auth';
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
    viewingDate: string;
    isToday: boolean;
    pastDays: { date: string; reference: string; insights_count: number }[];
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
    viewingDate,
    isToday,
    pastDays,
}: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const [tab, setTab] = useState<Tab>('insights');

    const dateLabel = new Date(`${viewingDate}T00:00:00`).toLocaleDateString(
        undefined,
        { weekday: 'long', day: 'numeric', month: 'long' },
    );

    const hasChallenge =
        group.current_day !== null &&
        group.duration_days !== null &&
        group.starts_on !== null;

    return (
        <>
            <Head
                title={`${group.name} — ${isToday ? "Today's verse" : dateLabel}`}
            />
            <PageHeader title={group.name} backHref={`/groups/${group.slug}`} />

            <div className="flex flex-col gap-6 px-4 py-6">
                {hasChallenge && (
                    <StreakTracker
                        currentDay={group.current_day as number}
                        totalDays={group.duration_days as number}
                        startsOn={group.starts_on as string}
                        progress={progress}
                        groupSlug={group.slug}
                        selectedDate={viewingDate}
                    />
                )}

                {!isToday && (
                    <div className="bg-muted flex items-center justify-between gap-3 rounded-xl px-4 py-3 text-sm">
                        <span>Viewing {dateLabel}</span>
                        <Link
                            href={`/groups/${group.slug}/verse`}
                            className="text-primary font-semibold"
                        >
                            Back to today
                        </Link>
                    </div>
                )}

                {verse ? (
                    <VerseCard
                        verse={verse}
                        label={isToday ? "Today's verse" : 'Verse of the day'}
                    />
                ) : (
                    <div className="bg-muted text-muted-foreground rounded-2xl p-6 text-center text-sm">
                        {isToday
                            ? 'No verse has been set for today yet.'
                            : 'No verse was set for this day.'}
                    </div>
                )}

                {canManage && isToday && (
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
                                ['insights', 'Bible Study Insights'],
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

                            {!auth.user && (
                                <SignInPrompt
                                    className="mt-4"
                                    message="Log in or create an account to share your reflection."
                                />
                            )}

                            {insights.length === 0 ? (
                                <p className="text-muted-foreground py-10 text-center text-sm">
                                    No Bible Study Insights have been shared
                                    yet.
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
                            {canManage && isToday && (
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

            {pastDays.length > 0 && (
                <div className="mt-8 px-4 pb-8">
                    <h2 className="mb-3 text-base font-semibold">Past days</h2>
                    <div className="divide-border border-border divide-y rounded-xl border">
                        {pastDays.map((day) => (
                            <Link
                                key={day.date}
                                href={`/groups/${group.slug}/verse?date=${day.date}`}
                                className={cn(
                                    'flex items-center justify-between gap-3 px-4 py-3 text-sm',
                                    day.date === viewingDate &&
                                        'bg-primary/10 text-primary',
                                )}
                            >
                                <span className="min-w-0">
                                    <span className="block font-semibold">
                                        {new Date(
                                            `${day.date}T00:00:00`,
                                        ).toLocaleDateString(undefined, {
                                            day: 'numeric',
                                            month: 'short',
                                        })}
                                    </span>
                                    <span className="text-muted-foreground block truncate text-xs">
                                        {day.reference}
                                    </span>
                                </span>
                                <span className="text-muted-foreground shrink-0 text-xs">
                                    {day.insights_count} insight
                                    {day.insights_count === 1 ? '' : 's'}
                                </span>
                            </Link>
                        ))}
                    </div>
                </div>
            )}
        </>
    );
}

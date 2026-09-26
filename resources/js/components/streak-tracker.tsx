import { Check } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { ChallengeProgress } from '@/types/models';

const DAY_MS = 24 * 60 * 60 * 1000;

export function StreakTracker({
    currentDay,
    totalDays,
    startsOn,
    progress,
}: {
    currentDay: number;
    totalDays: number;
    startsOn: string;
    progress?: ChallengeProgress | null;
}) {
    const windowSize = Math.min(6, totalDays);
    const start = Math.max(
        1,
        Math.min(currentDay - 3, totalDays - windowSize + 1),
    );
    const days = Array.from({ length: windowSize }, (_, i) => start + i);
    const startDate = new Date(startsOn);

    return (
        <div>
            <div className="flex items-baseline justify-between gap-3">
                <p className="text-sm font-semibold">
                    Day {currentDay}/{totalDays}
                </p>
                {progress && (
                    <p className="text-muted-foreground text-xs">
                        <span className="text-foreground font-semibold">
                            {progress.current_streak}
                        </span>{' '}
                        day streak · best{' '}
                        <span className="text-foreground font-semibold">
                            {progress.longest_streak}
                        </span>
                    </p>
                )}
            </div>

            <div className="mt-3 flex gap-2">
                {days.map((day) => {
                    const date = new Date(
                        startDate.getTime() + (day - 1) * DAY_MS,
                    );
                    const isCurrent = day === currentDay;
                    const isDone = progress?.completed_days.includes(day);

                    return (
                        <div
                            key={day}
                            className={cn(
                                'flex flex-1 flex-col items-center rounded-xl border px-2 py-2 text-xs',
                                isCurrent
                                    ? 'border-primary text-primary'
                                    : 'border-border text-muted-foreground',
                            )}
                        >
                            <span className="flex items-center gap-1 font-semibold">
                                {day}
                                {isDone && (
                                    <Check
                                        className="text-success size-3"
                                        aria-label="Completed"
                                    />
                                )}
                            </span>
                            <span>
                                {date.toLocaleDateString(undefined, {
                                    day: '2-digit',
                                    month: 'short',
                                })}
                            </span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

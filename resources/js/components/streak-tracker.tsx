import { cn } from '@/lib/utils';

const DAY_MS = 24 * 60 * 60 * 1000;

export function StreakTracker({
    currentDay,
    totalDays,
    startsOn,
}: {
    currentDay: number;
    totalDays: number;
    startsOn: string;
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
            <p className="text-sm font-semibold">
                Day {currentDay}/{totalDays}
            </p>

            <div className="mt-3 flex gap-2">
                {days.map((day) => {
                    const date = new Date(
                        startDate.getTime() + (day - 1) * DAY_MS,
                    );
                    const isCurrent = day === currentDay;

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
                            <span className="font-semibold">{day}</span>
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

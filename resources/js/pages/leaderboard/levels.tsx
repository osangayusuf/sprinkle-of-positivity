import { Head } from '@inertiajs/react';
import { Check } from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import { cn } from '@/lib/utils';
import type { Level } from '@/types/models';

export default function LeaderboardLevels({
    levels,
    myPoints,
}: {
    levels: Level[];
    myPoints: number;
}) {
    return (
        <>
            <Head title="Levels" />
            <PageHeader title="Levels" backHref="/leaderboard" />

            <div className="flex flex-col gap-3 px-4 py-4">
                {levels.map((level) => {
                    const achieved = myPoints >= level.threshold;

                    return (
                        <div
                            key={level.key}
                            className="bg-muted flex items-center justify-between rounded-2xl px-4 py-4"
                        >
                            <div className="flex items-center gap-3">
                                <span
                                    className={cn(
                                        'flex size-6 items-center justify-center rounded-full',
                                        achieved
                                            ? 'bg-primary text-primary-foreground'
                                            : 'border-border border',
                                    )}
                                >
                                    {achieved && <Check className="size-4" />}
                                </span>
                                <span className="text-sm font-semibold">
                                    {level.label}
                                </span>
                            </div>

                            <span className="bg-primary-tint text-primary-tint-foreground flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold">
                                🏅 {level.threshold.toLocaleString()} Points
                            </span>
                        </div>
                    );
                })}
            </div>
        </>
    );
}

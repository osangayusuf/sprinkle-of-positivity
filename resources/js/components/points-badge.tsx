import { Bell } from 'lucide-react';

export function PointsBadge({
    points,
    level,
}: {
    points: number;
    level: string | null;
}) {
    return (
        <div className="bg-card border-border rounded-2xl border p-4">
            <div className="flex items-center gap-3">
                <span className="text-3xl">🏅</span>
                <div>
                    <p className="text-base font-bold">
                        My points: {points.toLocaleString()}
                    </p>
                    <p className="text-muted-foreground text-sm">
                        Level: {level ?? 'Newcomer'}
                    </p>
                </div>
            </div>

            <div className="bg-muted mt-4 flex items-center gap-3 rounded-xl p-3">
                <span className="bg-primary-tint text-primary-tint-foreground flex size-9 shrink-0 items-center justify-center rounded-full">
                    <Bell className="size-4" />
                </span>
                <p className="text-muted-foreground text-sm">
                    Engage in more insights, quiz and conversations to get more
                    points
                </p>
            </div>
        </div>
    );
}

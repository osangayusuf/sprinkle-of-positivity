import { Head, Link } from "@inertiajs/react";
import { useState } from "react";
import { PageHeader } from "@/components/page-header";
import { PointsBadge } from "@/components/points-badge";
import { SearchInput } from "@/components/search-input";
import { UserAvatar } from "@/components/user-avatar";
import { progressWidthClass } from "@/lib/progress-width";
import { matchesSearch } from "@/lib/search";
import { cn } from "@/lib/utils";
import type { LeaderboardEntry } from "@/types/models";

const MEDALS: Record<number, string> = { 1: "🥇", 2: "🥈", 3: "🥉" };

export default function LeaderboardIndex({
    entries,
    myPoints,
    myLevel,
}: {
    entries: LeaderboardEntry[];
    myPoints: number;
    myLevel: string | null;
}) {
    const [search, setSearch] = useState("");
    const filteredEntries = entries.filter((entry) =>
        matchesSearch(search, entry.name),
    );

    return (
        <>
            <Head title="Leaderboard" />
            <PageHeader title="Leaderboard" backHref="/home" />

            <div className="flex flex-col gap-4 px-4 py-4">
                <PointsBadge points={myPoints} level={myLevel} />

                <div className="flex items-center justify-between">
                    <Link
                        href="/leaderboard/consistency"
                        className="text-primary text-sm font-semibold"
                    >
                        Consistency
                    </Link>
                    <Link
                        href="/leaderboard/levels"
                        className="text-primary text-sm font-semibold"
                    >
                        View levels
                    </Link>
                </div>

                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search members"
                />

                <div className="flex flex-col">
                    {filteredEntries.map((entry) => (
                        <div
                            key={entry.id}
                            className={cn(
                                "flex items-center gap-3 rounded-xl px-2 py-3",
                                entry.is_me &&
                                    "bg-primary text-primary-foreground",
                            )}
                        >
                            <span className="w-6 shrink-0 text-center text-sm font-semibold">
                                {MEDALS[entry.rank] ?? entry.rank}
                            </span>

                            <UserAvatar
                                name={entry.name}
                                src={entry.avatar}
                                className="size-9"
                            />

                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold">
                                    {entry.is_me
                                        ? `You {${entry.name}}`
                                        : entry.name}
                                </p>
                                <div className="bg-muted/30 mt-1.5 h-1.5 w-full max-w-32 overflow-hidden rounded-full">
                                    <div
                                        className={cn(
                                            "h-full rounded-full",
                                            progressWidthClass(entry.progress),
                                            entry.is_me
                                                ? "bg-primary-foreground"
                                                : "bg-primary",
                                        )}
                                    />
                                </div>
                            </div>

                            <span className="shrink-0 text-sm font-semibold">
                                {entry.points.toLocaleString()}
                            </span>
                        </div>
                    ))}

                    {filteredEntries.length === 0 && (
                        <p className="text-muted-foreground py-10 text-center text-sm">
                            {entries.length === 0
                                ? "No one has earned points yet."
                                : "No one matches your search."}
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

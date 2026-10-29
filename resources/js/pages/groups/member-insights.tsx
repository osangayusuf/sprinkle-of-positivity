import { Head, Link } from "@inertiajs/react";
import { useState } from "react";
import { PageHeader } from "@/components/page-header";
import { SearchInput } from "@/components/search-input";
import { UserAvatar } from "@/components/user-avatar";
import { matchesSearch } from "@/lib/search";

type MemberInsight = {
    id: number;
    body: string;
    image_url: string | null;
    created_at: string;
    comments_count: number;
    verse: { date: string; reference: string };
};

export default function MemberInsights({
    group,
    member,
    insights,
}: {
    group: { id: number; name: string; slug: string };
    member: { id: number; name: string; avatar: string | null };
    insights: MemberInsight[];
}) {
    const [search, setSearch] = useState("");
    const filteredInsights = insights.filter((insight) =>
        matchesSearch(search, insight.body, insight.verse.reference),
    );

    return (
        <>
            <Head title={`${member.name} — insights`} />
            <PageHeader
                title="Insights"
                backHref={`/groups/${group.slug}/manage`}
            />

            <div className="flex flex-col gap-4 px-4 py-4">
                <div className="flex items-center gap-3">
                    <UserAvatar
                        name={member.name}
                        src={member.avatar}
                        className="size-10"
                    />
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold">
                            {member.name}
                        </p>
                        <p className="text-muted-foreground text-xs">
                            {group.name} · {insights.length} insight
                            {insights.length === 1 ? "" : "s"}
                        </p>
                    </div>
                </div>

                {insights.length > 0 && (
                    <SearchInput
                        value={search}
                        onChange={setSearch}
                        placeholder="Search insights"
                    />
                )}

                {insights.length === 0 && (
                    <p className="text-muted-foreground py-10 text-center text-sm">
                        {member.name} hasn&apos;t shared any insights yet.
                    </p>
                )}

                {insights.length > 0 && filteredInsights.length === 0 && (
                    <p className="text-muted-foreground py-10 text-center text-sm">
                        No insights match your search.
                    </p>
                )}

                <div className="divide-border divide-y">
                    {filteredInsights.map((insight) => (
                        <Link
                            key={insight.id}
                            href={`/groups/${group.slug}/insights/${insight.id}`}
                            className="flex flex-col gap-1 py-4"
                        >
                            <p className="text-muted-foreground text-xs">
                                {new Date(
                                    `${insight.verse.date}T00:00:00`,
                                ).toLocaleDateString(undefined, {
                                    weekday: "short",
                                    day: "numeric",
                                    month: "short",
                                })}{" "}
                                · {insight.verse.reference}
                            </p>
                            <p className="line-clamp-3 text-sm">
                                {insight.body}
                            </p>
                            <p className="text-muted-foreground text-xs">
                                {insight.comments_count} comment
                                {insight.comments_count === 1 ? "" : "s"}
                            </p>
                        </Link>
                    ))}
                </div>
            </div>
        </>
    );
}

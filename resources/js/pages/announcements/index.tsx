import { Head } from "@inertiajs/react";
import { Megaphone } from "lucide-react";
import { useState } from "react";
import { PageHeader } from "@/components/page-header";
import { SearchInput } from "@/components/search-input";
import { matchesSearch } from "@/lib/search";
import type { Announcement } from "@/types/models";

function formatDate(isoDate: string): string {
    return new Date(isoDate).toLocaleDateString(undefined, {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}

export default function AnnouncementsIndex({
    announcements,
}: {
    announcements: Announcement[];
}) {
    const [search, setSearch] = useState("");
    const filteredAnnouncements = announcements.filter((announcement) =>
        matchesSearch(search, announcement.title, announcement.body),
    );

    return (
        <>
            <Head title="Announcements" />
            <PageHeader title="Announcements" backHref="/notifications" />

            <div className="flex flex-col gap-4 px-4 py-4">
                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search announcements"
                />

                {filteredAnnouncements.length === 0 ? (
                    <div className="text-muted-foreground flex flex-col items-center gap-3 py-20 text-center text-sm">
                        <Megaphone className="size-8" />
                        {announcements.length === 0
                            ? "No announcements yet."
                            : "No announcements match your search."}
                    </div>
                ) : (
                    filteredAnnouncements.map((announcement) => (
                        <article
                            key={announcement.id}
                            className="bg-muted rounded-2xl p-4"
                        >
                            <h2 className="text-sm font-semibold">
                                {announcement.title}
                            </h2>
                            <p className="text-muted-foreground mt-0.5 text-xs">
                                {formatDate(
                                    announcement.last_sent_at ??
                                        announcement.created_at,
                                )}
                                {announcement.is_edited && " • Updated"}
                            </p>
                            <p className="text-foreground mt-3 text-sm leading-relaxed whitespace-pre-line">
                                {announcement.body}
                            </p>
                        </article>
                    ))
                )}
            </div>
        </>
    );
}

import { Head, Link } from "@inertiajs/react";
import { useState } from "react";
import Heading from "@/components/heading";
import { SearchInput } from "@/components/search-input";
import { Button } from "@/components/ui/button";
import { matchesSearch } from "@/lib/search";
import type { Announcement } from "@/types/models";

function formatDateTime(isoDate: string): string {
    return new Date(isoDate).toLocaleString(undefined, {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

export default function AdminAnnouncementIndex({
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

            <div className="max-w-3xl space-y-6">
                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Announcements"
                        description="Everything sent to the community. Edit a message to correct it, and send it again if members need to be told."
                    />
                    <Button asChild>
                        <Link href="/admin/announcements/create">
                            New announcement
                        </Link>
                    </Button>
                </div>

                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search announcements"
                    className="max-w-sm"
                />

                {filteredAnnouncements.length === 0 ? (
                    <p className="text-muted-foreground rounded-lg border px-4 py-6 text-center text-sm">
                        {announcements.length === 0
                            ? "No announcements have been sent yet."
                            : "No announcements match your search."}
                    </p>
                ) : (
                    <ul className="divide-y rounded-lg border">
                        {filteredAnnouncements.map((announcement) => (
                            <li
                                key={announcement.id}
                                className="flex items-start justify-between gap-4 px-4 py-4"
                            >
                                <div className="min-w-0 space-y-1">
                                    <p className="font-medium">
                                        {announcement.title}
                                    </p>
                                    <p className="text-muted-foreground line-clamp-2 text-sm whitespace-pre-line">
                                        {announcement.body}
                                    </p>
                                    <p className="text-muted-foreground text-xs">
                                        {announcement.last_sent_at
                                            ? `Last sent ${formatDateTime(announcement.last_sent_at)}`
                                            : `Created ${formatDateTime(announcement.created_at)}`}
                                        {announcement.is_edited &&
                                            ` • Edited ${formatDateTime(announcement.updated_at)}`}
                                    </p>
                                </div>
                                <Button
                                    asChild
                                    variant="outline"
                                    size="sm"
                                    className="shrink-0"
                                >
                                    <Link
                                        href={`/admin/announcements/${announcement.id}/edit`}
                                    >
                                        Edit
                                    </Link>
                                </Button>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

AdminAnnouncementIndex.layout = {
    breadcrumbs: [{ title: "Announcements", href: "/admin/announcements" }],
};

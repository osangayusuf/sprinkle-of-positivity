import { Head, Link } from "@inertiajs/react";
import { useState } from "react";
import Heading from "@/components/heading";
import { SearchInput } from "@/components/search-input";
import { matchesSearch } from "@/lib/search";
import type { Verse } from "@/types/models";

function todayDateString(): string {
    return new Date().toLocaleDateString("en-CA");
}

export default function AdminDailyVerseHistory({
    verses,
}: {
    verses: Verse[];
}) {
    const today = todayDateString();
    const [search, setSearch] = useState("");
    const filteredVerses = verses.filter((verse) =>
        matchesSearch(search, verse.reference, verse.date),
    );

    return (
        <>
            <Head title="Verse history" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Verse history"
                    description="Every global daily verse that's been set, most recent first"
                />

                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search by reference or date"
                    className="max-w-sm"
                />

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full min-w-[640px] text-sm">
                        <thead className="bg-muted text-muted-foreground text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">Date</th>
                                <th className="px-4 py-2 font-medium">
                                    Reference
                                </th>
                                <th className="px-4 py-2 font-medium">Image</th>
                                <th className="px-4 py-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredVerses.map((verse) => (
                                <tr key={verse.id} className="border-t">
                                    <td className="px-4 py-2 whitespace-nowrap">
                                        {verse.date}
                                    </td>
                                    <td className="px-4 py-2">
                                        {verse.reference}
                                    </td>
                                    <td className="px-4 py-2">
                                        {verse.image_url ? (
                                            <img
                                                src={verse.image_url}
                                                alt=""
                                                className="h-10 w-16 rounded object-cover"
                                            />
                                        ) : (
                                            <span className="text-muted-foreground">
                                                &mdash;
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2 text-right whitespace-nowrap">
                                        {verse.date >= today && (
                                            <Link
                                                href={`/admin/daily-verse/${verse.date}`}
                                                className="text-primary font-semibold"
                                            >
                                                Edit
                                            </Link>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {filteredVerses.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        {verses.length === 0
                                            ? "No verses have been set yet."
                                            : "No verses match your search."}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

AdminDailyVerseHistory.layout = {
    breadcrumbs: [
        { title: "Today's verse", href: "/admin/daily-verse" },
        { title: "History", href: "/admin/daily-verse/history" },
    ],
};

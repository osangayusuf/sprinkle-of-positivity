import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import type { Verse } from '@/types/models';

function todayDateString(): string {
    return new Date().toLocaleDateString('en-CA');
}

export default function AdminDailyVerseHistory({
    verses,
}: {
    verses: Verse[];
}) {
    const today = todayDateString();

    return (
        <>
            <Head title="Verse history" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Verse history"
                    description="Every global daily verse that's been set, most recent first"
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
                            {verses.map((verse) => (
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
                            {verses.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No verses have been set yet.
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
        { title: "Today's verse", href: '/admin/daily-verse' },
        { title: 'History', href: '/admin/daily-verse/history' },
    ],
};

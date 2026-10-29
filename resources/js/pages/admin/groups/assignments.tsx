import { Head, router } from "@inertiajs/react";
import { useState } from "react";
import Heading from "@/components/heading";
import { SearchInput } from "@/components/search-input";
import { matchesSearch } from "@/lib/search";

type Props = {
    group: { id: number; name: string };
    participants: { id: number; name: string; partner_id: number | null }[];
    partners: { id: number; name: string }[];
};

export default function AdminGroupAssignments({
    group,
    participants,
    partners,
}: Props) {
    const [search, setSearch] = useState("");
    const filteredParticipants = participants.filter((participant) =>
        matchesSearch(search, participant.name),
    );

    function assign(membershipId: number, partnerId: string) {
        router.put(
            `/admin/groups/${group.id}/assignments/${membershipId}`,
            { partner_id: partnerId },
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title={`Assignments — ${group.name}`} />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={`${group.name} assignments`}
                    description="Choose which accountability partner looks after each participant"
                />

                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search participants"
                    className="max-w-sm"
                />

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full min-w-[480px] text-sm">
                        <thead className="bg-muted text-muted-foreground text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">
                                    Participant
                                </th>
                                <th className="px-4 py-2 font-medium">
                                    Partner
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredParticipants.map((participant) => (
                                <tr key={participant.id} className="border-t">
                                    <td className="px-4 py-2">
                                        {participant.name}
                                    </td>
                                    <td className="px-4 py-2">
                                        <select
                                            value={participant.partner_id ?? ""}
                                            onChange={(e) =>
                                                assign(
                                                    participant.id,
                                                    e.target.value,
                                                )
                                            }
                                            className="bg-background rounded-md border px-2 py-1 text-sm"
                                        >
                                            <option value="" disabled>
                                                Unassigned
                                            </option>
                                            {partners.map((partner) => (
                                                <option
                                                    key={partner.id}
                                                    value={partner.id}
                                                >
                                                    {partner.name}
                                                </option>
                                            ))}
                                        </select>
                                    </td>
                                </tr>
                            ))}
                            {filteredParticipants.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={2}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        {participants.length === 0
                                            ? "No approved participants yet."
                                            : "No participants match your search."}
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

AdminGroupAssignments.layout = {
    breadcrumbs: [
        { title: "Groups", href: "/admin/groups" },
        { title: "Assignments", href: "#" },
    ],
};

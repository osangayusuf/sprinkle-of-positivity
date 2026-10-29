import { Head } from "@inertiajs/react";
import { useState } from "react";
import { GroupCard } from "@/components/group-card";
import { PageHeader } from "@/components/page-header";
import { SearchInput } from "@/components/search-input";
import { matchesSearch } from "@/lib/search";
import type { Group } from "@/types/models";

type Props = {
    myGroups: Group[];
    discoverGroups: Group[];
};

export default function GroupsIndex({ myGroups, discoverGroups }: Props) {
    const [search, setSearch] = useState("");
    const matches = (group: Group): boolean =>
        matchesSearch(search, group.name, group.purpose);
    const filteredMyGroups = myGroups.filter(matches);
    const filteredDiscoverGroups = discoverGroups.filter(matches);

    return (
        <>
            <Head title="Groups" />
            <PageHeader title="Groups" />

            <div className="flex flex-col gap-6 px-4 py-6">
                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search groups"
                />

                {filteredMyGroups.length > 0 && (
                    <section className="flex flex-col gap-3">
                        <h2 className="text-muted-foreground text-sm font-semibold">
                            My Groups
                        </h2>
                        {filteredMyGroups.map((group) => (
                            <GroupCard
                                key={group.id}
                                group={group}
                                membershipState="approved"
                            />
                        ))}
                    </section>
                )}

                <section className="flex flex-col gap-3">
                    <h2 className="text-muted-foreground text-sm font-semibold">
                        Discover
                    </h2>
                    {filteredDiscoverGroups.length === 0 ? (
                        <p className="text-muted-foreground text-sm">
                            {search.trim() !== ""
                                ? "No groups match your search."
                                : "No new groups to discover right now."}
                        </p>
                    ) : (
                        filteredDiscoverGroups.map((group) => (
                            <GroupCard key={group.id} group={group} />
                        ))
                    )}
                </section>
            </div>
        </>
    );
}

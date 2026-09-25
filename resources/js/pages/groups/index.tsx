import { Head } from '@inertiajs/react';
import { GroupCard } from '@/components/group-card';
import { PageHeader } from '@/components/page-header';
import type { Group } from '@/types/models';

type Props = {
    myGroups: Group[];
    discoverGroups: Group[];
};

export default function GroupsIndex({ myGroups, discoverGroups }: Props) {
    return (
        <>
            <Head title="Groups" />
            <PageHeader title="Groups" />

            <div className="flex flex-col gap-6 px-4 py-6">
                {myGroups.length > 0 && (
                    <section className="flex flex-col gap-3">
                        <h2 className="text-muted-foreground text-sm font-semibold">
                            My Groups
                        </h2>
                        {myGroups.map((group) => (
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
                    {discoverGroups.length === 0 ? (
                        <p className="text-muted-foreground text-sm">
                            No new groups to discover right now.
                        </p>
                    ) : (
                        discoverGroups.map((group) => (
                            <GroupCard key={group.id} group={group} />
                        ))
                    )}
                </section>
            </div>
        </>
    );
}

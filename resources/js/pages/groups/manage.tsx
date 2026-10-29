import { Form, Head, Link } from "@inertiajs/react";
import { useState } from "react";
import GroupApplicationController from "@/actions/App/Http/Controllers/GroupApplicationController";
import { PageHeader } from "@/components/page-header";
import { SearchInput } from "@/components/search-input";
import { Button } from "@/components/ui/button";
import { UserAvatar } from "@/components/user-avatar";
import { matchesSearch } from "@/lib/search";
import type { Group, GroupMember } from "@/types/models";

type Props = {
    group: Group;
    pendingApplications: GroupMember[];
    members: GroupMember[];
};

export default function GroupManage({
    group,
    pendingApplications,
    members,
}: Props) {
    const [search, setSearch] = useState("");
    const isSearching = search.trim() !== "";
    const filteredPendingApplications = pendingApplications.filter(
        (application) => matchesSearch(search, application.name),
    );
    const filteredMembers = members.filter((member) =>
        matchesSearch(search, member.name),
    );

    return (
        <>
            <Head title={`Manage ${group.name}`} />
            <PageHeader
                title="Manage group"
                backHref={`/groups/${group.slug}`}
            />

            <div className="flex flex-col gap-6 px-4 py-6">
                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search members and applications"
                />

                <section className="flex flex-col gap-3">
                    <h2 className="text-muted-foreground text-sm font-semibold">
                        Pending applications
                    </h2>

                    {filteredPendingApplications.length === 0 ? (
                        <p className="text-muted-foreground text-sm">
                            {isSearching && pendingApplications.length > 0
                                ? "No applications match your search."
                                : "No pending applications."}
                        </p>
                    ) : (
                        filteredPendingApplications.map((application) => (
                            <div
                                key={application.membership_id}
                                className="bg-muted flex items-center justify-between rounded-2xl p-3"
                            >
                                <div className="flex min-w-0 items-center gap-3">
                                    <UserAvatar
                                        name={application.name}
                                        src={application.avatar}
                                        className="size-9"
                                    />
                                    <span className="truncate font-semibold">
                                        {application.name}
                                    </span>
                                </div>
                                <div className="flex gap-2">
                                    <Form
                                        {...GroupApplicationController.update.form(
                                            {
                                                group: group.slug,
                                                membership:
                                                    application.membership_id,
                                            },
                                        )}
                                    >
                                        <input
                                            type="hidden"
                                            name="decision"
                                            value="rejected"
                                        />
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            size="sm"
                                            className="rounded-full"
                                        >
                                            Reject
                                        </Button>
                                    </Form>
                                    <Form
                                        {...GroupApplicationController.update.form(
                                            {
                                                group: group.slug,
                                                membership:
                                                    application.membership_id,
                                            },
                                        )}
                                    >
                                        <input
                                            type="hidden"
                                            name="decision"
                                            value="approved"
                                        />
                                        <Button
                                            type="submit"
                                            size="sm"
                                            className="rounded-full"
                                        >
                                            Approve
                                        </Button>
                                    </Form>
                                </div>
                            </div>
                        ))
                    )}
                </section>

                <section className="flex flex-col gap-3">
                    <h2 className="text-muted-foreground text-sm font-semibold">
                        Members
                    </h2>

                    {isSearching && filteredMembers.length === 0 && (
                        <p className="text-muted-foreground text-sm">
                            No members match your search.
                        </p>
                    )}

                    {filteredMembers.map((member) => (
                        <div
                            key={member.membership_id}
                            className="bg-muted flex items-center justify-between rounded-2xl p-3"
                        >
                            <div className="flex min-w-0 items-center gap-3">
                                <UserAvatar
                                    name={member.name}
                                    src={member.avatar}
                                    className="size-9"
                                />
                                <div className="min-w-0">
                                    <p className="truncate font-semibold">
                                        {member.name}
                                    </p>
                                    {member.role === "manager" && (
                                        <p className="text-muted-foreground text-xs">
                                            Accountability partner
                                        </p>
                                    )}
                                </div>
                            </div>
                            {member.role !== "manager" && (
                                <div className="flex shrink-0 items-center gap-2">
                                    <Button
                                        asChild
                                        variant="outline"
                                        size="sm"
                                        className="rounded-full"
                                    >
                                        <Link
                                            href={`/groups/${group.slug}/members/${member.user_id}/insights`}
                                        >
                                            Insights
                                        </Link>
                                    </Button>
                                    <Form
                                        {...GroupApplicationController.destroy.form(
                                            {
                                                group: group.slug,
                                                membership:
                                                    member.membership_id,
                                            },
                                        )}
                                    >
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            size="sm"
                                            className="rounded-full"
                                        >
                                            Remove
                                        </Button>
                                    </Form>
                                </div>
                            )}
                        </div>
                    ))}
                </section>
            </div>
        </>
    );
}

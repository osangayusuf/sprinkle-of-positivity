import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, MoreVertical } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { Group, GroupMembershipInfo } from '@/types/models';

type Props = {
    group: Group;
    membership: GroupMembershipInfo | null;
    canManage: boolean;
};

export default function GroupShow({ group, membership, canManage }: Props) {
    return (
        <>
            <Head title={group.name} />

            <div className="relative flex h-56 flex-col justify-end p-4 text-white">
                {group.cover_image_url ? (
                    <img
                        src={group.cover_image_url}
                        alt=""
                        className="absolute inset-0 size-full object-cover"
                    />
                ) : (
                    <div className="bg-heading absolute inset-0" />
                )}
                <div className="absolute inset-0 bg-black/50" />
                <div className="relative z-10 flex items-start justify-between">
                    <Link href="/groups" aria-label="Go back">
                        <ChevronLeft className="size-6" />
                    </Link>
                    {canManage && (
                        <Link
                            href={`/groups/${group.slug}/manage`}
                            aria-label="Manage group"
                        >
                            <MoreVertical className="size-6" />
                        </Link>
                    )}
                </div>
                <h1 className="relative z-10 text-2xl font-bold">
                    {group.name}
                </h1>
            </div>

            <div className="flex flex-col gap-6 px-4 py-6">
                <div>
                    <h2 className="text-muted-foreground mb-2 text-sm font-semibold">
                        Group purpose
                    </h2>
                    <p className="text-foreground text-sm leading-relaxed">
                        {group.purpose}
                    </p>
                </div>

                {typeof group.members_count === 'number' && (
                    <p className="text-muted-foreground text-sm">
                        {group.members_count} members
                    </p>
                )}

                <div className="flex flex-col gap-3">
                    {membership === null && (
                        <Button
                            asChild
                            className="h-14 w-full rounded-full text-base"
                        >
                            <Link href={`/groups/${group.slug}/join`}>
                                Join Group
                            </Link>
                        </Button>
                    )}

                    {membership?.status === 'pending' && (
                        <div className="bg-muted text-muted-foreground rounded-full px-5 py-4 text-center text-sm font-semibold">
                            Application pending approval
                        </div>
                    )}

                    <Button
                        asChild
                        variant="outline"
                        className="border-primary text-primary h-14 w-full rounded-full text-base"
                    >
                        <Link href={`/groups/${group.slug}/verse`}>
                            Read Insights
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

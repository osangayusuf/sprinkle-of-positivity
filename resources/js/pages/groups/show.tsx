import { Head, Link, usePage } from '@inertiajs/react';
import { ChevronLeft, Lock, MoreVertical } from 'lucide-react';
import { SignInPrompt } from '@/components/sign-in-prompt';
import { Button } from '@/components/ui/button';
import type { Auth } from '@/types/auth';
import type { Group, GroupMembershipInfo } from '@/types/models';

type Props = {
    group: Group;
    membership: GroupMembershipInfo | null;
    canManage: boolean;
    canViewContent: boolean;
};

export default function GroupShow({
    group,
    membership,
    canManage,
    canViewContent,
}: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;

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
                {group.is_private && (
                    <p className="relative z-10 mt-1 inline-flex items-center gap-1 text-sm font-medium text-white/80">
                        <Lock className="size-3.5" />
                        Private group
                    </p>
                )}
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
                    {!auth.user && (
                        <SignInPrompt message="Log in or create an account to join this group." />
                    )}

                    {auth.user && membership === null && (
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

                    {canViewContent ? (
                        <Button
                            asChild
                            variant="outline"
                            className="border-primary text-primary h-14 w-full rounded-full text-base"
                        >
                            <Link href={`/groups/${group.slug}/verse`}>
                                Read Bible Study Insights
                            </Link>
                        </Button>
                    ) : (
                        <div className="bg-muted text-muted-foreground flex items-center justify-center gap-2 rounded-full px-5 py-4 text-center text-sm">
                            <Lock className="size-4 shrink-0" />
                            Bible Study Insights are visible to members only.
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

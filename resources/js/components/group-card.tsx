import { Link } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { Group, GroupMembershipStatus } from '@/types/models';

type MembershipState = 'none' | GroupMembershipStatus | 'manager';

const ACTION_LABEL: Record<MembershipState, string> = {
    none: 'Join',
    pending: 'Pending',
    approved: 'View',
    rejected: 'View',
    removed: 'View',
    manager: 'Manage',
};

export function GroupCard({
    group,
    membershipState = 'none',
}: {
    group: Group;
    membershipState?: MembershipState;
}) {
    return (
        <Link
            href={`/groups/${group.slug}`}
            prefetch
            className="bg-muted flex items-center gap-3 rounded-2xl p-3"
        >
            <span className="bg-secondary size-14 shrink-0 overflow-hidden rounded-xl">
                {group.cover_image_url && (
                    <img
                        src={group.cover_image_url}
                        alt=""
                        className="size-full object-cover"
                    />
                )}
            </span>

            <span className="min-w-0 flex-1">
                <span className="flex items-center gap-1.5 font-semibold">
                    <span className="truncate">{group.name}</span>
                    {group.is_private && (
                        <Lock
                            className="text-muted-foreground size-3.5 shrink-0"
                            aria-label="Private group"
                        />
                    )}
                </span>
                <span className="text-muted-foreground block truncate text-sm">
                    {group.purpose}
                </span>
                {typeof group.members_count === 'number' && (
                    <span className="text-muted-foreground block text-xs">
                        {group.members_count} members
                    </span>
                )}
            </span>

            <span
                className={cn(
                    'shrink-0 rounded-full px-4 py-2 text-sm font-semibold',
                    membershipState === 'none'
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-primary-tint text-primary-tint-foreground',
                )}
            >
                {ACTION_LABEL[membershipState]}
            </span>
        </Link>
    );
}

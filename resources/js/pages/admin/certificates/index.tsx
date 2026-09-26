import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type ChallengeGroup = {
    id: number;
    name: string;
    duration_days: number;
    starts_on: string;
    ends_on: string | null;
    has_ended: boolean;
    members_count: number;
    certificates_count: number;
};

export default function AdminCertificatesIndex({
    groups,
}: {
    groups: ChallengeGroup[];
}) {
    return (
        <>
            <Head title="Certificates" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Certificates"
                    description="Certificates are issued automatically after a challenge ends. Open a group to review, issue or revoke them."
                />

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full min-w-[640px] text-sm">
                        <thead className="bg-muted text-muted-foreground text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">Group</th>
                                <th className="px-4 py-2 font-medium">
                                    Challenge
                                </th>
                                <th className="px-4 py-2 font-medium">
                                    Members
                                </th>
                                <th className="px-4 py-2 font-medium">
                                    Certificates
                                </th>
                                <th className="px-4 py-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {groups.map((group) => (
                                <tr key={group.id} className="border-t">
                                    <td className="px-4 py-2">{group.name}</td>
                                    <td className="px-4 py-2">
                                        {group.duration_days} days,{' '}
                                        {group.has_ended
                                            ? `ended ${group.ends_on}`
                                            : `ends ${group.ends_on}`}
                                    </td>
                                    <td className="px-4 py-2">
                                        {group.members_count}
                                    </td>
                                    <td className="px-4 py-2">
                                        {group.certificates_count}
                                    </td>
                                    <td className="px-4 py-2 text-right">
                                        <Button
                                            asChild
                                            variant="outline"
                                            size="sm"
                                        >
                                            <Link
                                                href={`/admin/certificates/groups/${group.id}`}
                                            >
                                                Manage
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                            {groups.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No groups run a challenge yet.
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

AdminCertificatesIndex.layout = {
    breadcrumbs: [{ title: 'Certificates', href: '/admin/certificates' }],
};

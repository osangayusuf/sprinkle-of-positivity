import { Head, router } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import Heading from '@/components/heading';

type AdminUser = {
    id: number;
    name: string;
    email: string;
    points: number;
    role: 'admin' | 'member' | 'partner';
    partner_status: 'pending' | 'approved' | 'rejected' | null;
};

export default function AdminUsersIndex({ users }: { users: AdminUser[] }) {
    function changeRole(user: AdminUser, role: string) {
        router.put(
            UserController.updateRole.url({ user: user.id }),
            { role },
            { preserveScroll: true },
        );
    }

    function decidePartner(user: AdminUser, decision: string) {
        router.put(
            `/admin/users/${user.id}/partner`,
            { decision },
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title="Users" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Users"
                    description="View members, manage roles and approve accountability partners"
                />

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full min-w-[640px] text-sm">
                        <thead className="bg-muted text-muted-foreground text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">Name</th>
                                <th className="px-4 py-2 font-medium">Email</th>
                                <th className="px-4 py-2 font-medium">
                                    Points
                                </th>
                                <th className="px-4 py-2 font-medium">Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr key={user.id} className="border-t">
                                    <td className="px-4 py-2">{user.name}</td>
                                    <td className="px-4 py-2">{user.email}</td>
                                    <td className="px-4 py-2">{user.points}</td>
                                    <td className="px-4 py-2">
                                        <select
                                            value={user.role}
                                            onChange={(e) =>
                                                changeRole(user, e.target.value)
                                            }
                                            className="bg-background rounded-md border px-2 py-1 text-sm"
                                        >
                                            <option value="member">
                                                Member
                                            </option>
                                            <option value="partner">
                                                Accountability partner
                                            </option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        {user.partner_status === 'pending' && (
                                            <span className="ml-3 inline-flex items-center gap-2">
                                                <span className="text-xs text-amber-600">
                                                    Awaiting approval
                                                </span>
                                                <button
                                                    type="button"
                                                    className="text-primary text-xs font-semibold"
                                                    onClick={() =>
                                                        decidePartner(
                                                            user,
                                                            'approved',
                                                        )
                                                    }
                                                >
                                                    Approve
                                                </button>
                                                <button
                                                    type="button"
                                                    className="text-destructive text-xs font-semibold"
                                                    onClick={() =>
                                                        decidePartner(
                                                            user,
                                                            'rejected',
                                                        )
                                                    }
                                                >
                                                    Reject
                                                </button>
                                            </span>
                                        )}
                                        {user.partner_status === 'rejected' && (
                                            <span className="text-destructive ml-3 text-xs">
                                                Rejected
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {users.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No users yet.
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

AdminUsersIndex.layout = {
    breadcrumbs: [{ title: 'Users', href: '/admin/users' }],
};

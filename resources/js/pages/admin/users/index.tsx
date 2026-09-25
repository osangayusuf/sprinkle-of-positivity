import { Head, router } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import Heading from '@/components/heading';

type AdminUser = {
    id: number;
    name: string;
    email: string;
    points: number;
    role: 'admin' | 'member';
};

export default function AdminUsersIndex({ users }: { users: AdminUser[] }) {
    function changeRole(user: AdminUser, role: string) {
        router.put(
            UserController.updateRole.url({ user: user.id }),
            { role },
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
                    description="View members and manage admin access"
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
                                            <option value="admin">Admin</option>
                                        </select>
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

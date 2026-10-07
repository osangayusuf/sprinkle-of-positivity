import { Form, Head, Link } from '@inertiajs/react';
import GroupController from '@/actions/App/Http/Controllers/Admin/GroupController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Group } from '@/types/models';

export default function AdminGroupsIndex({ groups }: { groups: Group[] }) {
    return (
        <>
            <Head title="Groups" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Groups"
                        description="Create and manage Bible study groups"
                    />
                    <Button asChild>
                        <Link href="/admin/groups/create">New group</Link>
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full min-w-[640px] text-sm">
                        <thead className="bg-muted text-muted-foreground text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">Name</th>
                                <th className="px-4 py-2 font-medium">
                                    Members
                                </th>
                                <th className="px-4 py-2 font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {groups.map((group) => (
                                <tr key={group.id} className="border-t">
                                    <td className="px-4 py-2">{group.name}</td>
                                    <td className="px-4 py-2">
                                        {group.members_count ?? 0}
                                    </td>
                                    <td className="px-4 py-2 capitalize">
                                        {group.status}
                                    </td>
                                    <td className="px-4 py-2 text-right whitespace-nowrap">
                                        <div className="flex justify-end gap-2">
                                            <Button
                                                asChild
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link
                                                    href={`/admin/groups/${group.id}/edit`}
                                                >
                                                    Edit
                                                </Link>
                                            </Button>

                                            <Button
                                                asChild
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link
                                                    href={`/admin/groups/${group.id}/assignments`}
                                                >
                                                    Partners
                                                </Link>
                                            </Button>

                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                    >
                                                        Delete
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogTitle>
                                                        Delete &ldquo;
                                                        {group.name}&rdquo;?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        This permanently deletes
                                                        the group along with its
                                                        members, daily verses,
                                                        insights, and quizzes.
                                                        This cannot be undone.
                                                    </DialogDescription>

                                                    <DialogFooter className="gap-2">
                                                        <DialogClose asChild>
                                                            <Button variant="secondary">
                                                                Cancel
                                                            </Button>
                                                        </DialogClose>

                                                        <Form
                                                            {...GroupController.destroy.form(
                                                                {
                                                                    group: group.id,
                                                                },
                                                            )}
                                                        >
                                                            <Button
                                                                type="submit"
                                                                variant="destructive"
                                                            >
                                                                Delete group
                                                            </Button>
                                                        </Form>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {groups.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No groups yet.
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

AdminGroupsIndex.layout = {
    breadcrumbs: [{ title: 'Groups', href: '/admin/groups' }],
};

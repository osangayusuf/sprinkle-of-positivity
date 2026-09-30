import { Form, Head } from '@inertiajs/react';
import GroupController from '@/actions/App/Http/Controllers/Admin/GroupController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import type { Group } from '@/types/models';

type Props = {
    group: Group;
    users: { id: number; name: string; email: string }[];
    managerId: number | null;
};

export default function AdminGroupsEdit({ group, users, managerId }: Props) {
    return (
        <>
            <Head title={`Edit ${group.name}`} />

            <div className="max-w-xl space-y-6">
                <Heading
                    variant="small"
                    title="Edit group"
                    description="Update this group's details"
                />

                <Form
                    {...GroupController.update.form({ group: group.id })}
                    encType="multipart/form-data"
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={group.name}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="purpose">Purpose</Label>
                                <Textarea
                                    id="purpose"
                                    name="purpose"
                                    defaultValue={group.purpose}
                                    required
                                    rows={4}
                                />
                                <InputError message={errors.purpose} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="duration_days">
                                        Duration (days)
                                    </Label>
                                    <Input
                                        id="duration_days"
                                        name="duration_days"
                                        type="number"
                                        min={1}
                                        defaultValue={
                                            group.duration_days ?? undefined
                                        }
                                    />
                                    <InputError
                                        message={errors.duration_days}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="starts_on">
                                        Start date
                                    </Label>
                                    <Input
                                        id="starts_on"
                                        name="starts_on"
                                        type="date"
                                        defaultValue={
                                            group.starts_on ?? undefined
                                        }
                                    />
                                    <InputError message={errors.starts_on} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="manager_id">Manager</Label>
                                <Select
                                    name="manager_id"
                                    defaultValue={
                                        managerId
                                            ? String(managerId)
                                            : undefined
                                    }
                                >
                                    <SelectTrigger id="manager_id">
                                        <SelectValue placeholder="Select a manager" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {users.map((user) => (
                                            <SelectItem
                                                key={user.id}
                                                value={String(user.id)}
                                            >
                                                {user.name} ({user.email})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.manager_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <Select
                                    name="status"
                                    defaultValue={group.status}
                                >
                                    <SelectTrigger id="status">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">
                                            Active
                                        </SelectItem>
                                        <SelectItem value="archived">
                                            Archived
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.status} />
                            </div>

                            <div className="flex items-start gap-3">
                                <Checkbox
                                    id="is_private"
                                    name="is_private"
                                    value="1"
                                    defaultChecked={group.is_private}
                                />
                                <div className="grid gap-1">
                                    <Label htmlFor="is_private">
                                        Private group
                                    </Label>
                                    <p className="text-muted-foreground text-sm">
                                        Anyone can see the group and apply to
                                        join, but only members can read its
                                        verses and insights.
                                    </p>
                                    <InputError message={errors.is_private} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="cover_image">
                                    Cover image
                                    {' (leave blank to keep the current one)'}
                                </Label>
                                {group.cover_image_url && (
                                    <img
                                        src={group.cover_image_url}
                                        alt=""
                                        className="h-32 w-full rounded-lg object-cover"
                                    />
                                )}
                                <Input
                                    id="cover_image"
                                    name="cover_image"
                                    type="file"
                                    accept="image/*"
                                />
                                <InputError message={errors.cover_image} />
                            </div>

                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner />}
                                Save changes
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

AdminGroupsEdit.layout = {
    breadcrumbs: [
        { title: 'Groups', href: '/admin/groups' },
        { title: 'Edit group', href: '#' },
    ],
};

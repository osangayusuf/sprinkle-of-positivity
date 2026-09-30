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

type Props = {
    users: { id: number; name: string; email: string }[];
};

export default function AdminGroupsCreate({ users }: Props) {
    return (
        <>
            <Head title="New group" />

            <div className="max-w-xl space-y-6">
                <Heading
                    variant="small"
                    title="New group"
                    description="Create a group and appoint its manager"
                />

                <Form
                    {...GroupController.store.form()}
                    encType="multipart/form-data"
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input id="name" name="name" required />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="purpose">Purpose</Label>
                                <Textarea
                                    id="purpose"
                                    name="purpose"
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
                                    />
                                    <InputError message={errors.starts_on} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="manager_id">Manager</Label>
                                <Select name="manager_id">
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

                            <div className="flex items-start gap-3">
                                <Checkbox
                                    id="is_private"
                                    name="is_private"
                                    value="1"
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
                                <Label htmlFor="cover_image">Cover image</Label>
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
                                Create group
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

AdminGroupsCreate.layout = {
    breadcrumbs: [
        { title: 'Groups', href: '/admin/groups' },
        { title: 'New group', href: '/admin/groups/create' },
    ],
};

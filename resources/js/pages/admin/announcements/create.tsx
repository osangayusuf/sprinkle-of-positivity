import { Form, Head } from '@inertiajs/react';
import AnnouncementController from '@/actions/App/Http/Controllers/Admin/AnnouncementController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

export default function AdminAnnouncementCreate() {
    return (
        <>
            <Head title="Send an announcement" />

            <div className="max-w-xl space-y-6">
                <Heading
                    variant="small"
                    title="Send an announcement"
                    description="Delivered to every user's notifications"
                />

                <Form
                    {...AnnouncementController.store.form()}
                    resetOnSuccess
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    placeholder="Weekly Fast Alert! 🎉"
                                    required
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="body">Message</Label>
                                <Textarea
                                    id="body"
                                    name="body"
                                    required
                                    rows={6}
                                />
                                <InputError message={errors.body} />
                            </div>

                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner />}
                                Send
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

AdminAnnouncementCreate.layout = {
    breadcrumbs: [
        { title: 'Send announcement', href: '/admin/announcements/create' },
    ],
};

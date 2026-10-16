import { Form, Head } from "@inertiajs/react";
import AnnouncementController from "@/actions/App/Http/Controllers/Admin/AnnouncementController";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import { Textarea } from "@/components/ui/textarea";
import type { Announcement } from "@/types/models";

export default function AdminAnnouncementEdit({
    announcement,
}: {
    announcement: Announcement;
}) {
    return (
        <>
            <Head title="Edit announcement" />

            <div className="max-w-xl space-y-6">
                <Heading
                    variant="small"
                    title="Edit announcement"
                    description="Corrections update the notification every member already received"
                />

                <Form
                    {...AnnouncementController.update.form({
                        announcement: announcement.id,
                    })}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    defaultValue={announcement.title}
                                    required
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="body">Message</Label>
                                <Textarea
                                    id="body"
                                    name="body"
                                    defaultValue={announcement.body}
                                    required
                                    rows={6}
                                />
                                <InputError message={errors.body} />
                            </div>

                            <div className="flex items-start gap-3">
                                <Checkbox id="resend" name="resend" value="1" />
                                <div className="grid gap-1">
                                    <Label htmlFor="resend">Send again</Label>
                                    <p className="text-muted-foreground text-sm">
                                        Notify every member again, as a new
                                        unread notification and push alert.
                                    </p>
                                </div>
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

AdminAnnouncementEdit.layout = {
    breadcrumbs: [
        { title: "Announcements", href: "/admin/announcements" },
        { title: "Edit", href: "#" },
    ],
};

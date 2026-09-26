import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import CertificateController from '@/actions/App/Http/Controllers/Admin/CertificateController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { Certificate } from '@/types/models';

type MemberRow = {
    user: { id: number; name: string };
    completed_count: number;
    required_count: number;
    current_streak: number;
    longest_streak: number;
    eligible: boolean;
    certificate: Certificate | null;
};

type Props = {
    group: {
        id: number;
        name: string;
        duration_days: number;
        has_ended: boolean;
    };
    members: MemberRow[];
};

function FormDialog({
    trigger,
    title,
    description,
    submitLabel,
    destructive = false,
    form,
    children,
}: {
    trigger: string;
    title: string;
    description: string;
    submitLabel: string;
    destructive?: boolean;
    form: { action: string; method: 'post' | 'get' };
    children: (errors: Record<string, string>) => React.ReactNode;
}) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button type="button" variant="outline" size="sm">
                    {trigger}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>{title}</DialogTitle>
                <DialogDescription>{description}</DialogDescription>

                <Form {...form} onSuccess={() => setOpen(false)}>
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            {children(errors)}
                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant={
                                        destructive ? 'destructive' : 'default'
                                    }
                                    disabled={processing}
                                >
                                    {submitLabel}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function ReasonField({
    errors,
    label,
}: {
    errors: Record<string, string>;
    label: string;
}) {
    return (
        <div className="grid gap-2">
            <Label htmlFor="reason">{label}</Label>
            <Textarea id="reason" name="reason" required maxLength={500} />
            <InputError message={errors.reason} />
        </div>
    );
}

function status(row: MemberRow): string {
    if (row.certificate?.revoked) {
        return 'Revoked';
    }

    if (row.certificate) {
        return row.certificate.override_reason ? 'Issued (override)' : 'Issued';
    }

    return row.eligible ? 'Eligible' : 'Not eligible';
}

export default function AdminCertificatesShow({ group, members }: Props) {
    return (
        <>
            <Head title={`Certificates: ${group.name}`} />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={group.name}
                    description={
                        group.has_ended
                            ? 'The challenge has ended. Certificates are issued automatically to members who completed every day.'
                            : 'The challenge is still running. Certificates are issued after it ends.'
                    }
                />

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full min-w-[760px] text-sm">
                        <thead className="bg-muted text-muted-foreground text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">
                                    Member
                                </th>
                                <th className="px-4 py-2 font-medium">Days</th>
                                <th className="px-4 py-2 font-medium">
                                    Streak (best)
                                </th>
                                <th className="px-4 py-2 font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {members.map((row) => {
                                const certificate = row.certificate;

                                return (
                                    <tr key={row.user.id} className="border-t">
                                        <td className="px-4 py-2">
                                            {certificate?.recipient_name ??
                                                row.user.name}
                                        </td>
                                        <td className="px-4 py-2 tabular-nums">
                                            {row.completed_count}/
                                            {row.required_count}
                                        </td>
                                        <td className="px-4 py-2 tabular-nums">
                                            {row.current_streak} (
                                            {row.longest_streak})
                                        </td>
                                        <td className="px-4 py-2">
                                            {status(row)}
                                            {certificate?.revoke_reason && (
                                                <span className="text-muted-foreground block text-xs">
                                                    {certificate.revoke_reason}
                                                </span>
                                            )}
                                            {certificate?.override_reason && (
                                                <span className="text-muted-foreground block text-xs">
                                                    {
                                                        certificate.override_reason
                                                    }
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-4 py-2">
                                            <div className="flex flex-wrap justify-end gap-2">
                                                {!certificate && (
                                                    <FormDialog
                                                        trigger="Issue"
                                                        title={`Issue a certificate to ${row.user.name}?`}
                                                        description="Use this when the system judged the member ineligible but they did complete the challenge. The reason is recorded."
                                                        submitLabel="Issue certificate"
                                                        form={CertificateController.issue.form(
                                                            {
                                                                group: group.id,
                                                                user: row.user
                                                                    .id,
                                                            },
                                                        )}
                                                    >
                                                        {(errors) => (
                                                            <ReasonField
                                                                errors={errors}
                                                                label="Reason for the override"
                                                            />
                                                        )}
                                                    </FormDialog>
                                                )}

                                                {certificate &&
                                                    !certificate.revoked && (
                                                        <>
                                                            <Button
                                                                asChild
                                                                variant="outline"
                                                                size="sm"
                                                            >
                                                                <Link
                                                                    href={`/certificates/${certificate.code}`}
                                                                >
                                                                    View
                                                                </Link>
                                                            </Button>
                                                            <FormDialog
                                                                trigger="Edit name"
                                                                title="Edit the name on this certificate"
                                                                description="Use this to fix a misspelling. The certificate keeps the same link."
                                                                submitLabel="Save name"
                                                                form={CertificateController.updateName.form(
                                                                    {
                                                                        certificate:
                                                                            certificate.id,
                                                                    },
                                                                )}
                                                            >
                                                                {(errors) => (
                                                                    <div className="grid gap-2">
                                                                        <Label htmlFor="recipient_name">
                                                                            Name
                                                                        </Label>
                                                                        <Input
                                                                            id="recipient_name"
                                                                            name="recipient_name"
                                                                            defaultValue={
                                                                                certificate.recipient_name
                                                                            }
                                                                            required
                                                                            maxLength={
                                                                                120
                                                                            }
                                                                        />
                                                                        <InputError
                                                                            message={
                                                                                errors.recipient_name
                                                                            }
                                                                        />
                                                                    </div>
                                                                )}
                                                            </FormDialog>
                                                            <FormDialog
                                                                trigger="Revoke"
                                                                title={`Revoke ${certificate.recipient_name}'s certificate?`}
                                                                description="The certificate stops being valid and will not be issued again automatically. You can restore it later."
                                                                submitLabel="Revoke certificate"
                                                                destructive
                                                                form={CertificateController.revoke.form(
                                                                    {
                                                                        certificate:
                                                                            certificate.id,
                                                                    },
                                                                )}
                                                            >
                                                                {(errors) => (
                                                                    <ReasonField
                                                                        errors={
                                                                            errors
                                                                        }
                                                                        label="Reason for revoking"
                                                                    />
                                                                )}
                                                            </FormDialog>
                                                        </>
                                                    )}

                                                {certificate?.revoked && (
                                                    <Form
                                                        {...CertificateController.restore.form(
                                                            {
                                                                certificate:
                                                                    certificate.id,
                                                            },
                                                        )}
                                                    >
                                                        <Button
                                                            type="submit"
                                                            variant="outline"
                                                            size="sm"
                                                        >
                                                            Restore
                                                        </Button>
                                                    </Form>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                            {members.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        This group has no approved members.
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

AdminCertificatesShow.layout = {
    breadcrumbs: [
        { title: 'Certificates', href: '/admin/certificates' },
        { title: 'Group certificates', href: '#' },
    ],
};

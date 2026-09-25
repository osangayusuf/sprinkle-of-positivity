import { Form, Head, Link, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import { SettingsHeader } from '@/components/settings-tabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

const fieldClassName = 'bg-muted h-12 rounded-xl border-transparent px-4';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <>
            <Head title="Profile settings" />
            <SettingsHeader />

            <div className="flex flex-col gap-6 px-4 py-6">
                <Form
                    {...ProfileController.update.form()}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    className={fieldClassName}
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="Full name"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    className={fieldClassName}
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="Email address"
                                />
                                <InputError message={errors.email} />
                            </div>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div className="bg-muted rounded-xl p-3 text-sm">
                                        <p className="text-muted-foreground">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-primary font-semibold underline-offset-4 hover:underline"
                                            >
                                                Resend the verification email.
                                            </Link>
                                        </p>

                                        {status ===
                                            'verification-link-sent' && (
                                            <p className="text-success mt-2 font-medium">
                                                A new verification link has been
                                                sent to your email address.
                                            </p>
                                        )}
                                    </div>
                                )}

                            <Button
                                type="submit"
                                disabled={processing}
                                data-test="update-profile-button"
                                className="h-12 w-full rounded-full"
                            >
                                {processing && <Spinner />}
                                Save
                            </Button>
                        </>
                    )}
                </Form>

                <DeleteUser />
            </div>
        </>
    );
}

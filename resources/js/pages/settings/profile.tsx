import type { FormComponentRef } from '@inertiajs/core';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Check } from 'lucide-react';
import { useRef, useState } from 'react';
import AvatarController from '@/actions/App/Http/Controllers/Settings/AvatarController';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import { SettingsHeader } from '@/components/settings-tabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { UserAvatar } from '@/components/user-avatar';
import { GOAL_OPTIONS, MONTHS } from '@/lib/profile-options';
import { cn } from '@/lib/utils';
import { send } from '@/routes/verification';
import type { AuthenticatedAuth } from '@/types';

const fieldClassName = 'bg-muted h-12 rounded-xl border-transparent px-4';
const selectClassName =
    'bg-muted h-12 w-full rounded-xl border-transparent px-4 text-sm outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<{ auth: AuthenticatedAuth }>().props;
    const [goals, setGoals] = useState<string[]>(auth.user.goals ?? []);

    // Keep at least one goal selected: an empty selection isn't sent, so
    // letting it be cleared would look saved when nothing changed.
    function toggleGoal(goal: string) {
        setGoals((current) =>
            current.includes(goal)
                ? current.length > 1
                    ? current.filter((g) => g !== goal)
                    : current
                : [...current, goal],
        );
    }

    return (
        <>
            <Head title="Profile settings" />
            <SettingsHeader />

            <div className="flex flex-col gap-6 px-4 py-6">
                <ProfilePicture />

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

                            <div className="grid gap-2">
                                <Label htmlFor="whatsapp_number">
                                    WhatsApp number
                                </Label>
                                <Input
                                    id="whatsapp_number"
                                    type="tel"
                                    className={fieldClassName}
                                    defaultValue={
                                        auth.user.whatsapp_number ?? ''
                                    }
                                    name="whatsapp_number"
                                    autoComplete="tel"
                                    placeholder="Phone number"
                                />
                                <InputError message={errors.whatsapp_number} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Birthday</Label>
                                <div className="flex gap-3">
                                    <select
                                        name="birthday_day"
                                        aria-label="Birthday day"
                                        defaultValue={
                                            auth.user.birthday_day ?? ''
                                        }
                                        className={selectClassName}
                                    >
                                        <option value="">Day</option>
                                        {Array.from(
                                            { length: 31 },
                                            (_, i) => i + 1,
                                        ).map((day) => (
                                            <option key={day} value={day}>
                                                {day}
                                            </option>
                                        ))}
                                    </select>
                                    <select
                                        name="birthday_month"
                                        aria-label="Birthday month"
                                        defaultValue={
                                            auth.user.birthday_month ?? ''
                                        }
                                        className={selectClassName}
                                    >
                                        <option value="">Month</option>
                                        {MONTHS.map((month, i) => (
                                            <option key={month} value={i + 1}>
                                                {month}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <InputError message={errors.birthday_day} />
                                <InputError message={errors.birthday_month} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Goals</Label>
                                <div className="flex flex-col gap-2">
                                    {GOAL_OPTIONS.map((goal) => {
                                        const selected = goals.includes(goal);

                                        return (
                                            <button
                                                key={goal}
                                                type="button"
                                                aria-pressed={selected}
                                                onClick={() => toggleGoal(goal)}
                                                className={cn(
                                                    'flex h-12 items-center justify-between rounded-full px-5 text-left text-sm font-semibold',
                                                    selected
                                                        ? 'bg-primary-tint text-primary-tint-foreground'
                                                        : 'bg-muted text-foreground',
                                                )}
                                            >
                                                {goal}
                                                {selected && (
                                                    <span className="bg-primary text-primary-foreground flex size-5 items-center justify-center rounded-full">
                                                        <Check className="size-3.5" />
                                                    </span>
                                                )}
                                            </button>
                                        );
                                    })}
                                </div>
                                {goals.map((goal) => (
                                    <input
                                        key={goal}
                                        type="hidden"
                                        name="goals[]"
                                        value={goal}
                                    />
                                ))}
                                <InputError message={errors.goals} />
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

function ProfilePicture() {
    const { auth } = usePage<{ auth: AuthenticatedAuth }>().props;
    const formRef = useRef<FormComponentRef>(null);

    return (
        <div className="flex items-center gap-4">
            <UserAvatar
                name={auth.user.name}
                src={auth.user.avatar}
                className="size-20 text-xl"
            />

            <div className="flex flex-col gap-2">
                <Form
                    ref={formRef}
                    {...AvatarController.update.form()}
                    options={{ preserveScroll: true }}
                    resetOnSuccess
                >
                    {({ processing, errors }) => (
                        <>
                            <Label
                                htmlFor="avatar"
                                className="bg-muted hover:bg-muted/80 inline-flex h-10 cursor-pointer items-center gap-2 rounded-full px-4 text-sm font-semibold"
                            >
                                {processing && <Spinner />}
                                {auth.user.avatar
                                    ? 'Change photo'
                                    : 'Upload photo'}
                            </Label>
                            <input
                                id="avatar"
                                name="avatar"
                                type="file"
                                accept="image/*"
                                className="sr-only"
                                disabled={processing}
                                onChange={(event) =>
                                    event.currentTarget.files?.length &&
                                    formRef.current?.submit()
                                }
                            />
                            <InputError message={errors.avatar} />
                        </>
                    )}
                </Form>

                {auth.user.avatar && (
                    <Form
                        {...AvatarController.destroy.form()}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <button
                                type="submit"
                                disabled={processing}
                                className="text-muted-foreground hover:text-destructive text-sm font-medium"
                            >
                                Remove photo
                            </button>
                        )}
                    </Form>
                )}
            </div>
        </div>
    );
}

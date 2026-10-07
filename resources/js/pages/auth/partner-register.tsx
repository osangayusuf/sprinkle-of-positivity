import { Form, Head } from '@inertiajs/react';
import PartnerRegistrationController from '@/actions/App/Http/Controllers/Partner/PartnerRegistrationController';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login, register } from '@/routes';

export default function PartnerRegister({
    passwordRules,
}: {
    passwordRules: string;
}) {
    return (
        <>
            <Head title="Accountability partner sign up" />
            <Form
                {...PartnerRegistrationController.store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Full name"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Password"
                                    passwordrules={passwordRules}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirm password
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={5}
                                data-test="register-partner-button"
                            >
                                {processing && <Spinner />}
                                Request partner account
                            </Button>
                        </div>

                        <div className="text-muted-foreground space-y-1 text-center text-sm">
                            <p>
                                Already have an account?{' '}
                                <TextLink href={login()} tabIndex={6}>
                                    Log in
                                </TextLink>
                            </p>
                            <p>
                                Joining as a participant?{' '}
                                <TextLink href={register()} tabIndex={7}>
                                    Sign up here
                                </TextLink>
                            </p>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

PartnerRegister.layout = {
    title: 'Become an accountability partner',
    description:
        'Create your account. An admin will approve it before you can start.',
};

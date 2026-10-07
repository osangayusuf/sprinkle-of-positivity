import { Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { logout } from '@/routes';

export default function PartnerPending({ rejected }: { rejected: boolean }) {
    return (
        <>
            <Head title="Awaiting approval" />

            <div className="space-y-6 text-center">
                <p className="text-muted-foreground text-sm">
                    {rejected
                        ? 'An admin did not approve your accountability partner sign-up. Please contact the programme team if you think this is a mistake.'
                        : 'Your accountability partner account is waiting for an admin to approve it. You will be notified here as soon as it is approved.'}
                </p>

                <TextLink href={logout()} className="mx-auto block text-sm">
                    Log out
                </TextLink>
            </div>
        </>
    );
}

PartnerPending.layout = {
    title: 'Awaiting approval',
    description: 'Thanks for signing up as an accountability partner.',
};

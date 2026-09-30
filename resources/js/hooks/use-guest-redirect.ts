import { router, usePage } from '@inertiajs/react';
import { login } from '@/routes';
import type { Auth } from '@/types/auth';

/**
 * For write actions triggered outside a form (reacting, answering a quiz):
 * returns a function that sends guests to log in, then back to this page,
 * and reports whether it did so.
 */
export function useGuestRedirect(): () => boolean {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { url } = usePage();

    return () => {
        if (auth.user) {
            return false;
        }

        router.visit(login({ query: { intended: url } }));

        return true;
    };
}

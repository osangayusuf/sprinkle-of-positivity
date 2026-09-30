import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { login, register } from '@/routes';

/**
 * Shown to guests in place of a write action (commenting, sharing an
 * insight, joining a group). Logging in returns them to this page.
 */
export function SignInPrompt({
    message,
    className,
}: {
    message: string;
    className?: string;
}) {
    const { url } = usePage();
    const options = { query: { intended: url } };

    return (
        <div
            className={cn(
                'bg-muted flex flex-col items-center gap-3 rounded-2xl p-4 text-center',
                className,
            )}
        >
            <p className="text-muted-foreground text-sm">{message}</p>
            <div className="flex gap-2">
                <Button asChild variant="outline" className="rounded-full">
                    <Link href={login(options)}>Log in</Link>
                </Button>
                <Button asChild className="rounded-full">
                    <Link href={register(options)}>Join</Link>
                </Button>
            </div>
        </div>
    );
}

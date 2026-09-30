import { Link, usePage } from '@inertiajs/react';
import { guestNavItems, primaryNavItems } from '@/lib/navigation';
import { cn } from '@/lib/utils';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { Auth } from '@/types/auth';

export function BottomNav() {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const { auth } = usePage<{ auth: Auth }>().props;
    const navItems = auth.user ? primaryNavItems : guestNavItems;

    return (
        <nav
            className={cn(
                'border-border bg-background fixed inset-x-0 bottom-0 z-50 border-t',
                'pb-[env(safe-area-inset-bottom)]',
            )}
        >
            <ul className="mx-auto flex max-w-md items-stretch justify-between">
                {navItems.map((item) => {
                    const active = isCurrentOrParentUrl(item.href);

                    return (
                        <li key={item.title} className="flex-1">
                            <Link
                                href={item.href}
                                prefetch
                                className={cn(
                                    'flex flex-col items-center gap-1 py-2 text-xs font-medium',
                                    active
                                        ? 'text-primary'
                                        : 'text-muted-foreground',
                                )}
                            >
                                {item.icon && (
                                    <item.icon
                                        className="size-5"
                                        strokeWidth={active ? 2.5 : 2}
                                    />
                                )}
                                <span>{item.title}</span>
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}

import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/page-header';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';

const TABS = [
    { href: '/settings/profile', label: 'Profile' },
    { href: '/settings/security', label: 'Security' },
    { href: '/settings/appearance', label: 'Appearance' },
];

/**
 * Shared header for every /settings/* page: back to Profile, plus a tab
 * strip between the three settings sections — same underlined-tab pattern
 * as the Insights/Q&A tabs on the group verse page.
 */
export function SettingsHeader() {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <>
            <PageHeader title="Settings" backHref="/profile" />
            <div className="border-border flex border-b px-4">
                {TABS.map((tab) => {
                    const active = isCurrentUrl(tab.href);

                    return (
                        <Link
                            key={tab.href}
                            href={tab.href}
                            className={cn(
                                'flex-1 border-b-2 pb-3 text-center text-sm font-semibold',
                                active
                                    ? 'border-primary text-primary'
                                    : 'text-muted-foreground border-transparent',
                            )}
                        >
                            {tab.label}
                        </Link>
                    );
                })}
            </div>
        </>
    );
}

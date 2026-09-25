import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import type { InertiaLinkProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type PageHeaderProps = {
    title: string;
    backHref?: NonNullable<InertiaLinkProps['href']>;
    actions?: ReactNode;
    className?: string;
};

/**
 * The back-chevron + centered-title header used by every screen except
 * Home, which has its own bespoke greeting header.
 */
export function PageHeader({
    title,
    backHref,
    actions,
    className,
}: PageHeaderProps) {
    return (
        <header
            className={cn(
                'border-border flex h-14 items-center justify-between border-b px-4',
                className,
            )}
        >
            <div className="flex w-10 items-center justify-start">
                {backHref && (
                    <Link
                        href={backHref}
                        className="text-foreground"
                        aria-label="Go back"
                    >
                        <ChevronLeft className="size-6" />
                    </Link>
                )}
            </div>

            <h1 className="flex-1 truncate text-center text-base font-semibold">
                {title}
            </h1>

            <div className="flex w-10 items-center justify-end">{actions}</div>
        </header>
    );
}

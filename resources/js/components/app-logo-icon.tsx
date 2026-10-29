import type { ImgHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

type Props = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'> & {
    /** Force the variant for surfaces that stay dark/light in every theme. */
    tone?: 'auto' | 'on-dark' | 'on-light';
};

/**
 * The brand mark — an open book with rays of light. The black variant is used
 * on light surfaces and the pink/cream one on dark surfaces; `auto` follows
 * the app's light/dark theme.
 */
export default function AppLogoIcon({
    tone = 'auto',
    className,
    ...props
}: Props) {
    return (
        <>
            <img
                {...props}
                src="/images/logo/mark-light.png"
                alt=""
                className={cn(
                    'h-auto',
                    tone === 'on-dark' && 'hidden',
                    tone === 'auto' && 'dark:hidden',
                    className,
                )}
            />
            <img
                {...props}
                src="/images/logo/mark-dark.png"
                alt=""
                className={cn(
                    'h-auto',
                    tone === 'on-light' && 'hidden',
                    tone === 'auto' && 'hidden dark:block',
                    className,
                )}
            />
        </>
    );
}

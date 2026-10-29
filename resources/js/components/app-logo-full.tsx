import type { ImgHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

type Props = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'> & {
    /** Force the variant for surfaces that stay dark/light in every theme. */
    tone?: 'auto' | 'on-dark' | 'on-light';
};

/**
 * The stacked "Light Shining in the Dark." logo (mark + wordmark), with a
 * black variant for light surfaces and a cream/pink one for dark surfaces.
 */
export default function AppLogoFull({
    tone = 'auto',
    className,
    ...props
}: Props) {
    return (
        <>
            <img
                {...props}
                src="/images/logo/full-light.png"
                alt="Light Shining in the Dark."
                className={cn(
                    'h-auto',
                    tone === 'on-dark' && 'hidden',
                    tone === 'auto' && 'dark:hidden',
                    className,
                )}
            />
            <img
                {...props}
                src="/images/logo/full-dark.png"
                alt="Light Shining in the Dark."
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

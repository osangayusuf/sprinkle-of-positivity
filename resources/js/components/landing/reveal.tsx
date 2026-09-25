import { useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

const DELAYS = ['', 'delay-100', 'delay-200', 'delay-300'] as const;

/**
 * Fades and lifts its children in once, when they scroll into view.
 * Motion classes are all `motion-safe:`, so reduced-motion users see the
 * content immediately.
 */
export default function Reveal({
    children,
    className,
    delay = 0,
}: {
    children: ReactNode;
    className?: string;
    delay?: 0 | 1 | 2 | 3;
}) {
    const ref = useRef<HTMLDivElement>(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const node = ref.current;

        if (!node || typeof IntersectionObserver === 'undefined') {
            setVisible(true);

            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setVisible(true);
                    observer.disconnect();
                }
            },
            { rootMargin: '0px 0px -10% 0px' },
        );

        observer.observe(node);

        return () => observer.disconnect();
    }, []);

    return (
        <div
            ref={ref}
            className={cn(
                'motion-safe:transition-[opacity,translate] motion-safe:duration-700 motion-safe:ease-out',
                DELAYS[delay],
                !visible && 'motion-safe:translate-y-6 motion-safe:opacity-0',
                className,
            )}
        >
            {children}
        </div>
    );
}

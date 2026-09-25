import type { SVGAttributes } from 'react';

/**
 * The brand mark — three pink petals around a navy center, matching
 * design-reference/splash-screen/home.png. Always rendered in the brand's
 * own colors (not `currentColor`), since it's inherently two-tone.
 */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <circle cx="8.2" cy="14.2" r="3.6" fill="var(--primary)" />
            <circle cx="15.8" cy="14.2" r="3.6" fill="var(--primary)" />
            <circle cx="12" cy="17.6" r="3.6" fill="var(--primary)" />
            <circle cx="12" cy="6.6" r="2.8" fill="var(--heading)" />
        </svg>
    );
}

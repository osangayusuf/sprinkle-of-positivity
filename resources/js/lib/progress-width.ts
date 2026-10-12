// Tailwind can't statically discover a class built from an interpolated
// number (e.g. `w-[${n}%]`), so progress snaps to the nearest of these
// fixed, fully-literal width utilities instead.
const WIDTH_STEPS = [
    'w-0',
    'w-1/12',
    'w-2/12',
    'w-3/12',
    'w-4/12',
    'w-5/12',
    'w-6/12',
    'w-7/12',
    'w-8/12',
    'w-9/12',
    'w-10/12',
    'w-11/12',
    'w-full',
];

/**
 * The width utility closest to a 0–1 fraction.
 */
export function progressWidthClass(progress: number): string {
    const step = Math.round(
        Math.min(1, Math.max(0, progress)) * (WIDTH_STEPS.length - 1),
    );

    return WIDTH_STEPS[step];
}

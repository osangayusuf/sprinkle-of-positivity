import { cn } from '@/lib/utils';

type Tone = 'pink' | 'ink' | 'gold' | 'blush' | 'paper';

const TONES: Record<Tone, string> = {
    pink: 'bg-primary',
    ink: 'bg-ink',
    gold: 'bg-gold',
    blush: 'bg-blush',
    paper: 'bg-paper',
};

/**
 * One hand-cut sprinkle: a small rounded capsule. Rotation and position come
 * from the caller as literal Tailwind classes.
 */
export function Sprinkle({
    tone = 'pink',
    className,
}: {
    tone?: Tone;
    className?: string;
}) {
    return (
        <span
            aria-hidden="true"
            className={cn(
                'block h-5 w-1.5 rounded-full',
                TONES[tone],
                className,
            )}
        />
    );
}

const RULE: { tone: Tone; className: string }[] = [
    { tone: 'pink', className: 'rotate-12' },
    { tone: 'ink', className: '-rotate-45 h-4' },
    { tone: 'gold', className: 'rotate-[70deg]' },
    { tone: 'blush', className: '-rotate-12 h-6' },
    { tone: 'pink', className: 'rotate-45 h-4' },
    { tone: 'ink', className: '-rotate-[80deg]' },
    { tone: 'gold', className: 'rotate-[20deg] h-6' },
    { tone: 'pink', className: '-rotate-[30deg]' },
    { tone: 'blush', className: 'rotate-[60deg] h-4' },
    { tone: 'ink', className: 'rotate-[5deg]' },
];

/** A section divider: sprinkles scattered along a line, never evenly. */
export function SprinkleRule({ className }: { className?: string }) {
    return (
        <div
            aria-hidden="true"
            className={cn(
                'flex items-center justify-between gap-4 overflow-hidden',
                className,
            )}
        >
            {RULE.map((item, index) => (
                <Sprinkle
                    key={index}
                    tone={item.tone}
                    className={cn(item.className, index % 3 === 1 && 'ml-6')}
                />
            ))}
        </div>
    );
}

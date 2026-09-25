import { Check, Copy } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { cn } from '@/lib/utils';

async function writeToClipboard(text: string): Promise<boolean> {
    try {
        await navigator.clipboard.writeText(text);

        return true;
    } catch {
        const field = document.createElement('textarea');

        field.value = text;
        field.setAttribute('readonly', '');
        field.className = 'fixed -top-full opacity-0';
        document.body.appendChild(field);
        field.select();

        const copied = document.execCommand('copy');

        document.body.removeChild(field);

        return copied;
    }
}

/**
 * Copies `value` and confirms it twice: the button changes, and a polite
 * live region announces it to screen readers.
 */
export default function CopyButton({
    value,
    label,
    announcement,
    variant = 'solid',
    className,
}: {
    value: string;
    label: string;
    announcement: string;
    variant?: 'solid' | 'quiet';
    className?: string;
}) {
    const [status, setStatus] = useState<'idle' | 'copied' | 'failed'>('idle');
    const timer = useRef<ReturnType<typeof setTimeout>>(undefined);

    useEffect(() => () => clearTimeout(timer.current), []);

    async function copy() {
        const copied = await writeToClipboard(value);

        setStatus(copied ? 'copied' : 'failed');
        clearTimeout(timer.current);
        timer.current = setTimeout(() => setStatus('idle'), 2400);
    }

    const copied = status === 'copied';

    return (
        <>
            <button
                type="button"
                onClick={copy}
                className={cn(
                    'focus-visible:ring-primary focus-visible:ring-offset-paper inline-flex min-h-11 items-center justify-center gap-2 text-sm font-semibold transition-[background-color,color,transform] duration-200 outline-none focus-visible:ring-[3px] focus-visible:ring-offset-2 motion-safe:active:scale-[0.97]',
                    variant === 'solid' &&
                        'rounded-full px-5 ' +
                            (copied
                                ? 'bg-gold text-ink'
                                : 'bg-ink text-paper hover:bg-primary'),
                    variant === 'quiet' &&
                        'text-ink decoration-primary hover:text-primary rounded-sm px-1 underline underline-offset-4',
                    className,
                )}
            >
                {copied ? (
                    <Check className="size-4" aria-hidden="true" />
                ) : (
                    <Copy className="size-4" aria-hidden="true" />
                )}
                <span>
                    {copied
                        ? 'Copied'
                        : status === 'failed'
                          ? 'Copy failed'
                          : label}
                </span>
            </button>
            <span role="status" aria-live="polite" className="sr-only">
                {copied
                    ? announcement
                    : status === 'failed'
                      ? 'Copy failed. Select the text and copy it by hand.'
                      : ''}
            </span>
        </>
    );
}

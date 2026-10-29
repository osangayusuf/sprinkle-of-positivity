import { Link } from '@inertiajs/react';
import { HandHeart, X } from 'lucide-react';
import { useState } from 'react';
import { give } from '@/routes';

export function GiveBanner() {
    const [dismissed, setDismissed] = useState(false);

    if (dismissed) {
        return null;
    }

    return (
        <section
            aria-label="Give"
            className="bg-primary-tint mx-4 mb-4 flex items-center gap-3 rounded-2xl px-4 py-3"
        >
            <HandHeart
                className="text-primary size-5 shrink-0"
                aria-hidden="true"
            />
            <p className="flex-1 text-sm font-semibold">
                Give to support the work
            </p>
            <Link
                href={give()}
                className="text-primary shrink-0 text-sm font-semibold"
            >
                Give
            </Link>
            <button
                type="button"
                onClick={() => setDismissed(true)}
                aria-label="Dismiss"
                className="text-muted-foreground -m-1 p-1"
            >
                <X className="size-4" />
            </button>
        </section>
    );
}

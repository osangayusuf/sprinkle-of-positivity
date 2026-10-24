import { Link } from '@inertiajs/react';
import { Check, Copy, HandHeart, X } from 'lucide-react';
import { useState } from 'react';
import { useClipboard } from '@/hooks/use-clipboard';
import { support } from '@/lib/donation-accounts';
import { give } from '@/routes';

export function GiveBanner() {
    const [dismissed, setDismissed] = useState(false);
    const [copiedText, copy] = useClipboard();

    if (dismissed) {
        return null;
    }

    return (
        <section
            aria-label="Give"
            className="bg-primary-tint mx-4 mb-4 rounded-2xl p-4"
        >
            <div className="flex items-start gap-3">
                <HandHeart
                    className="text-primary mt-0.5 size-5 shrink-0"
                    aria-hidden="true"
                />
                <div className="flex-1">
                    <h2 className="text-sm font-semibold">
                        Help keep the study going
                    </h2>
                    <p className="text-muted-foreground mt-0.5 text-xs">
                        {support.intro}
                    </p>
                </div>
                <button
                    type="button"
                    onClick={() => setDismissed(true)}
                    aria-label="Dismiss"
                    className="text-muted-foreground -m-1 p-1"
                >
                    <X className="size-4" />
                </button>
            </div>

            <ul className="mt-3 flex flex-col gap-2">
                {support.accounts.map((account) => (
                    <li
                        key={account.accountNumber}
                        className="bg-background flex items-center justify-between gap-3 rounded-xl px-3 py-2"
                    >
                        <div className="min-w-0">
                            <p className="text-muted-foreground text-xs">
                                {account.label} · {account.bank}
                            </p>
                            <p className="font-semibold tracking-wide tabular-nums">
                                {account.accountNumber}
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={() => copy(account.accountNumber)}
                            className="text-primary inline-flex shrink-0 items-center gap-1 text-xs font-semibold"
                        >
                            {copiedText === account.accountNumber ? (
                                <Check className="size-3.5" />
                            ) : (
                                <Copy className="size-3.5" />
                            )}
                            {copiedText === account.accountNumber
                                ? 'Copied'
                                : 'Copy'}
                        </button>
                    </li>
                ))}
            </ul>

            <p className="text-muted-foreground mt-3 text-xs">
                Account name: {support.accounts[0].accountName}.{' '}
                <Link href={give()} className="text-primary font-semibold">
                    See all giving details
                </Link>
            </p>
        </section>
    );
}

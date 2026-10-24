import { Head } from '@inertiajs/react';
import { Check, Copy, HandHeart } from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import { useClipboard } from '@/hooks/use-clipboard';
import { support } from '@/lib/donation-accounts';
import type { DonationAccount } from '@/lib/donation-accounts';

export default function GiveIndex() {
    return (
        <>
            <Head title="Give" />
            <PageHeader title="Give" backHref="/profile" />

            <div className="flex flex-col gap-4 px-4 py-4">
                <div className="bg-muted flex flex-col items-center gap-2 rounded-2xl p-6 text-center">
                    <span className="bg-primary-tint text-primary flex size-12 items-center justify-center rounded-full">
                        <HandHeart className="size-6" aria-hidden="true" />
                    </span>
                    <h2 className="text-heading text-lg font-bold">
                        Help keep the study going
                    </h2>
                    <p className="text-muted-foreground text-sm">
                        {support.intro}
                    </p>
                </div>

                {support.accounts.map((account) => (
                    <AccountCard
                        key={account.accountNumber}
                        account={account}
                    />
                ))}
            </div>
        </>
    );
}

function AccountCard({ account }: { account: DonationAccount }) {
    const [copiedText, copy] = useClipboard();
    const allDetails = `${account.bank}\n${account.accountName}\n${account.accountNumber} (${account.currency})`;

    return (
        <article
            aria-label={account.label}
            className="bg-muted flex flex-col gap-3 rounded-2xl p-4"
        >
            <header className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-semibold">{account.label}</p>
                    <p className="text-muted-foreground text-xs">
                        {account.bank}
                    </p>
                </div>
                <span className="bg-primary text-primary-foreground rounded-full px-3 py-1 text-xs font-semibold">
                    {account.currency}
                </span>
            </header>

            <div>
                <p className="text-muted-foreground text-xs">Account number</p>
                <p className="text-2xl font-bold tracking-wide tabular-nums">
                    {account.accountNumber}
                </p>
                <p className="text-muted-foreground mt-2 text-xs">
                    Account name
                </p>
                <p className="text-sm font-medium">{account.accountName}</p>
            </div>

            <div className="flex gap-2">
                <CopyAction
                    label="Copy number"
                    copied={copiedText === account.accountNumber}
                    onCopy={() => copy(account.accountNumber)}
                    primary
                />
                <CopyAction
                    label="Copy all details"
                    copied={copiedText === allDetails}
                    onCopy={() => copy(allDetails)}
                />
            </div>
        </article>
    );
}

function CopyAction({
    label,
    copied,
    onCopy,
    primary = false,
}: {
    label: string;
    copied: boolean;
    onCopy: () => void;
    primary?: boolean;
}) {
    return (
        <button
            type="button"
            onClick={onCopy}
            className={`inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-full text-sm font-semibold ${primary ? 'bg-primary text-primary-foreground' : 'bg-background text-foreground'}`}
        >
            {copied ? (
                <Check className="size-4" />
            ) : (
                <Copy className="size-4" />
            )}
            {copied ? 'Copied' : label}
        </button>
    );
}

import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import CopyButton from '@/components/landing/copy-button';
import Reveal from '@/components/landing/reveal';

type DonationAccount = {
    label: string;
    currency: 'NGN' | 'USD';
    bank: string;
    accountName: string;
    accountNumber: string;
};

const support = {
    intro: 'Gifts help keep the study going and the community growing. Give what you can, in the currency that suits you.',
    accounts: [
        {
            label: 'Naira account',
            currency: 'NGN',
            bank: 'Providus Bank',
            accountName: 'The Service Excellence Mentor Enterprise',
            accountNumber: '5401542407',
        },
        {
            label: 'US dollar domiciliary account',
            currency: 'USD',
            bank: 'Providus Bank',
            accountName: 'The Service Excellence Mentor Enterprise',
            accountNumber: '5401558781',
        },
        {
            label: 'US dollar cash domiciliary account',
            currency: 'USD',
            bank: 'Providus Bank',
            accountName: 'The Service Excellence Mentor Enterprise',
            accountNumber: '8501558778',
        },
    ] satisfies DonationAccount[],
};

export default function Support() {
    return (
        <>
            <Head title="Give / Support" />
            <div className="landing bg-paper text-ink min-h-screen">
                <div className="mx-auto max-w-7xl px-5 py-10 md:px-8 lg:py-16">
                    <Link
                        href="/"
                        className="text-ink hover:text-primary decoration-primary focus-visible:ring-primary inline-flex items-center gap-2 rounded-sm font-semibold underline underline-offset-4 outline-none focus-visible:ring-[3px]"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        Back to home
                    </Link>
                    <Reveal className="mt-8 grid gap-6 lg:grid-cols-12">
                        <div className="lg:col-span-6">
                            <p className="text-primary text-sm font-semibold tracking-[0.18em] uppercase">
                                Support the program
                            </p>
                            <h1 className="font-display text-ink mt-4 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl">
                                Help keep the study going.
                            </h1>
                        </div>
                        <p className="text-ink-soft max-w-md text-lg leading-relaxed lg:col-span-5 lg:col-start-8 lg:self-end">
                            {support.intro}
                        </p>
                    </Reveal>

                    <ul className="mt-12 grid gap-5 lg:grid-cols-[1.25fr_1fr_1fr]">
                        {support.accounts.map((account, index) => (
                            <li key={account.accountNumber}>
                                <Reveal
                                    delay={Math.min(index, 3) as 0 | 1 | 2 | 3}
                                    className="h-full"
                                >
                                    <AccountCard account={account} />
                                </Reveal>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </>
    );
}

function AccountCard({ account }: { account: DonationAccount }) {
    const allDetails = `${account.bank}\n${account.accountName}\n${account.accountNumber} (${account.currency})`;

    return (
        <article
            aria-label={account.label}
            className="bg-paper-deep text-ink border-ink/15 flex h-full flex-col justify-between rounded-tl-[2.5rem] rounded-tr-md rounded-br-[2.5rem] rounded-bl-md border p-6 sm:p-7"
        >
            <header className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-primary text-xs font-semibold tracking-[0.14em] uppercase">
                        {account.label}
                    </p>
                    <p className="text-ink-soft mt-1 text-sm">{account.bank}</p>
                </div>
                <span className="bg-ink text-paper rounded-full px-3 py-1 text-xs font-semibold tracking-wider">
                    {account.currency}
                </span>
            </header>

            <div className="border-ink/25 my-6 border-t-2 border-dashed pt-6">
                <p className="text-ink-soft text-sm">Account number</p>
                <p className="font-display mt-1 text-3xl font-semibold tracking-[0.04em] tabular-nums sm:text-4xl">
                    {account.accountNumber}
                </p>
                <p className="text-ink-soft mt-4 text-sm">Account name</p>
                <p className="leading-snug font-semibold">
                    {account.accountName}
                </p>
            </div>

            <footer className="flex flex-wrap items-center gap-x-5 gap-y-2">
                <CopyButton
                    value={account.accountNumber}
                    label="Copy number"
                    announcement={`${account.currency} account number ${account.accountNumber} copied`}
                />
                <CopyButton
                    value={allDetails}
                    label="Copy all details"
                    announcement={`${account.currency} account details copied`}
                    variant="quiet"
                />
            </footer>
        </article>
    );
}

import { Head, Link } from '@inertiajs/react';
import { ArrowDown, ArrowUpRight, Mail, MessageCircle } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import CopyButton from '@/components/landing/copy-button';
import Reveal from '@/components/landing/reveal';
import { Sprinkle, SprinkleRule } from '@/components/landing/sprinkle';
import { login, register } from '@/routes';

/* -------------------------------------------------------------------------
 * Edit content here. Nothing below this block needs to change for copy,
 * people, photos, links or donation accounts.
 * ---------------------------------------------------------------------- */

type DonationAccount = {
    label: string;
    currency: 'NGN' | 'USD';
    bank: string;
    accountName: string;
    accountNumber: string;
};

type Testimonial = {
    quote: string;
    name: string;
    detail?: string;
    photo?: string;
};

const program = {
    name: 'Sprinkle of Positivity',
    intro: 'A Bible study program for people who want to know the word better and stay accountable while they do it. A verse each day. A small group each week. Someone to walk with.',
    schedule:
        'A new verse every day in the app. Study groups set their own meeting times.',
    venue: 'In the app, and wherever your group meets.',
    audience:
        'Anyone who wants to grow in the word and in community. No Bible school needed.',
    reasons: [
        'Know the Bible better',
        'Get closer to God',
        'Grow in community',
        'Build a daily habit',
        'Deepen my faith',
    ],
};

const organizer = {
    name: 'Nnenna Sam-Obioha',
    role: 'Founder and organizer',
    photo: '/images/organizer.webp',
    photoAlt: 'Portrait of Nnenna Sam-Obioha in a blue blazer',
    intro: 'Nnenna is a mentor, trainer and consultant with more than sixteen years in sales and customer experience, across financial services, telecoms and supply chain. She holds a BSc and an MSc in Psychology.',
    why: 'What she cares about most is people: helping them grow, and helping them build healthy relationships. Sprinkle of Positivity is where that care meets her faith. She created it as a place to open the Bible daily, and to do it beside others.',
    quote: '',
    facts: [
        {
            term: 'Studied',
            detail: 'Psychology, University of Ibadan (BSc) and Liverpool John Moores University (MSc)',
        },
        {
            term: 'Author of',
            detail: 'Wedding Night Chronicles, My Refreshing Thanksgiving Experience, and The Digital Connection',
        },
        {
            term: 'Recognised',
            detail: 'For social impact at the 2024 Global Entrepreneurship Festival',
        },
        {
            term: 'Also leads',
            detail: 'Lights Shining in the Dark Bible Study Accountability Program, The Service Excellence Mentor Consulting, Nut-Just-Salad',
        },
    ],
    closing: 'She lives in Nigeria with her husband and three children.',
};

const steps = [
    {
        title: 'Join',
        body: 'Create your account and tell us what you hope to gain from the Bible.',
    },
    {
        title: 'Read the verse',
        body: 'Each day there is a verse to sit with. Keep the streak going.',
    },
    {
        title: 'Find your group',
        body: 'Ask to join a study group and meet the people you will grow with.',
    },
    {
        title: 'Share what you learn',
        body: 'Post your reflections, encourage others, and watch one another grow.',
    },
];

const testimonials: Testimonial[] = [
    // {
    //     quote: 'What this group meant to me…',
    //     name: 'Member name',
    //     detail: 'Joined 2025',
    //     photo: '/images/members/name.webp',
    // },
];

const give = {
    intro: 'Gifts keep the program running and the community growing. Send what you can, in the currency that suits you. Thank you for sowing into this.',
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

const contact = {
    email: 'theserviceexcellencementor@gmail.com',
    phoneDisplay: '0808 111 4235',
    whatsapp: 'https://wa.me/2348081114235',
    socials: [
        {
            label: 'Instagram · @lightshininginthedark',
            href: 'https://www.instagram.com/lightshininginthedark',
        },
        {
            label: 'Instagram · @theserviceexcellencementor',
            href: 'https://www.instagram.com/theserviceexcellencementor',
        },
    ],
};

/* ---------------------------------------------------------------------- */

const buttonBase =
    'inline-flex min-h-12 items-center justify-center gap-2 rounded-full px-7 text-base font-semibold transition-[background-color,color,transform] duration-200 outline-none focus-visible:ring-[3px] focus-visible:ring-offset-2 motion-safe:active:scale-[0.98]';

function SectionLabel({
    number,
    children,
    tone = 'ink',
}: {
    number: string;
    children: string;
    tone?: 'ink' | 'paper';
}) {
    return (
        <p
            className={
                tone === 'paper'
                    ? 'text-blush flex items-baseline gap-3 text-sm font-semibold tracking-[0.18em] uppercase'
                    : 'text-primary flex items-baseline gap-3 text-sm font-semibold tracking-[0.18em] uppercase'
            }
        >
            <span className="font-display text-base tracking-normal italic">
                {number}
            </span>
            {children}
        </p>
    );
}

function Nav() {
    return (
        <header className="relative z-20 mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-5 md:px-8">
            <Link
                href="/"
                className="focus-visible:ring-primary flex items-center gap-2.5 rounded-sm outline-none focus-visible:ring-[3px] focus-visible:ring-offset-2"
            >
                <AppLogoIcon className="size-9" />
                <span className="font-display text-ink text-lg leading-none font-semibold tracking-tight">
                    Sprinkle of
                    <br />
                    Positivity
                </span>
            </Link>
            <nav
                aria-label="Main"
                className="flex items-center gap-1 text-sm font-semibold sm:gap-3"
            >
                <a
                    href="#give"
                    className="text-ink hover:text-primary focus-visible:ring-primary hidden rounded-full px-3 py-3 outline-none focus-visible:ring-[3px] sm:inline-block"
                >
                    Give
                </a>
                <Link
                    href={login()}
                    className="text-ink hover:text-primary focus-visible:ring-primary rounded-full px-3 py-3 outline-none focus-visible:ring-[3px]"
                >
                    Log in
                </Link>
                <Link
                    href={register()}
                    className="bg-ink text-paper hover:bg-primary focus-visible:ring-primary focus-visible:ring-offset-paper min-h-11 rounded-full px-5 py-3 outline-none focus-visible:ring-[3px] focus-visible:ring-offset-2"
                >
                    Join
                </Link>
            </nav>
        </header>
    );
}

function Hero() {
    return (
        <div className="grain bg-paper relative overflow-hidden">
            <Nav />

            <Sprinkle className="absolute top-40 left-[46%] hidden rotate-[35deg] lg:block" />
            <Sprinkle
                tone="gold"
                className="absolute top-[58%] left-[52%] hidden -rotate-12 lg:block"
            />
            <Sprinkle
                tone="ink"
                className="absolute top-24 right-[14%] hidden h-6 rotate-[70deg] lg:block"
            />

            <div className="relative mx-auto grid max-w-7xl gap-10 px-5 pt-10 pb-20 md:px-8 lg:grid-cols-12 lg:pt-20 lg:pb-36">
                <div className="relative z-10 lg:col-span-8">
                    <p className="text-primary mb-6 text-sm font-semibold tracking-[0.18em] uppercase">
                        A Bible study community
                    </p>
                    <h1 className="font-display text-ink text-[clamp(3.4rem,11.5vw,8.5rem)] leading-[0.9] font-semibold tracking-[-0.04em]">
                        A little light,{' '}
                        <em className="text-primary font-normal">sprinkled</em>{' '}
                        into every day.
                    </h1>
                    <p className="text-ink-soft mt-8 max-w-xl text-lg leading-relaxed md:text-xl">
                        {program.intro}
                    </p>
                    <div className="mt-10 flex flex-wrap items-center gap-x-6 gap-y-4">
                        <Link
                            href={register()}
                            className={`${buttonBase} bg-primary text-primary-foreground hover:bg-ink focus-visible:ring-primary focus-visible:ring-offset-paper`}
                        >
                            Join the program
                        </Link>
                        <a
                            href="#give"
                            className={`${buttonBase} border-ink text-ink hover:bg-ink hover:text-paper focus-visible:ring-primary focus-visible:ring-offset-paper border-2`}
                        >
                            Give
                            <ArrowDown className="size-4" aria-hidden="true" />
                        </a>
                        <Link
                            href={login()}
                            className="text-ink hover:text-primary decoration-primary focus-visible:ring-primary rounded-sm text-base font-semibold underline underline-offset-4 outline-none focus-visible:ring-[3px]"
                        >
                            Already a member? Log in
                        </Link>
                    </div>
                </div>

                <div
                    aria-hidden="true"
                    className="relative mt-4 -mr-16 flex justify-end lg:absolute lg:top-10 lg:-right-32 lg:mt-0 lg:w-[46rem]"
                >
                    <AppLogoIcon className="size-64 rotate-12 sm:size-80 lg:size-[44rem]" />
                </div>
            </div>
        </div>
    );
}

function About() {
    return (
        <section
            id="about"
            aria-labelledby="about-title"
            className="bg-paper relative"
        >
            <div className="mx-auto max-w-7xl px-5 pb-24 md:px-8 lg:pb-36">
                <SprinkleRule className="mb-20 lg:mb-28" />
                <div className="grid gap-12 lg:grid-cols-12">
                    <Reveal className="lg:col-span-6">
                        <SectionLabel number="01">The program</SectionLabel>
                        <h2
                            id="about-title"
                            className="font-display text-ink mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl lg:text-7xl"
                        >
                            Bible study you can actually keep up with.
                        </h2>
                        <p className="text-ink-soft mt-8 max-w-lg text-lg leading-relaxed">
                            {program.name} is where the word meets ordinary
                            days. You read. You reflect. Then you meet with a
                            small group who will ask how it is going, and mean
                            it.
                        </p>
                        <p className="text-ink mt-10 mb-3 text-sm font-semibold tracking-[0.14em] uppercase">
                            People join to
                        </p>
                        <ul className="flex max-w-lg flex-wrap gap-2">
                            {program.reasons.map((reason) => (
                                <li
                                    key={reason}
                                    className="border-ink/25 text-ink rounded-full border px-4 py-1.5 text-sm"
                                >
                                    {reason}
                                </li>
                            ))}
                        </ul>
                    </Reveal>

                    <Reveal
                        className="lg:col-span-5 lg:col-start-8 lg:pt-32"
                        delay={1}
                    >
                        <dl className="divide-ink/15 border-ink/15 divide-y border-y">
                            {[
                                ['When', program.schedule],
                                ['Where', program.venue],
                                ['Who', program.audience],
                            ].map(([term, detail]) => (
                                <div
                                    key={term}
                                    className="grid gap-1 py-6 sm:grid-cols-[6rem_1fr] sm:gap-6"
                                >
                                    <dt className="font-display text-primary text-2xl italic">
                                        {term}
                                    </dt>
                                    <dd className="text-ink text-lg leading-snug">
                                        {detail}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </Reveal>
                </div>
            </div>
        </section>
    );
}

function Organizer() {
    return (
        <section
            id="organizer"
            aria-labelledby="organizer-title"
            className="grain grain-light bg-ink text-paper relative overflow-hidden"
        >
            <Sprinkle
                tone="gold"
                className="absolute top-20 right-[8%] hidden rotate-[40deg] lg:block"
            />
            <Sprinkle
                tone="blush"
                className="absolute bottom-24 left-[42%] hidden h-7 -rotate-[25deg] lg:block"
            />

            <div className="mx-auto grid max-w-7xl gap-14 px-5 py-24 md:px-8 lg:grid-cols-12 lg:gap-20 lg:py-40">
                <Reveal className="lg:col-span-5">
                    <figure className="relative mx-auto max-w-md pl-4 lg:mx-0 lg:max-w-none lg:pl-0">
                        <div
                            aria-hidden="true"
                            className="bg-primary absolute -bottom-5 left-0 h-[96%] w-[94%] rounded-t-full lg:-left-7"
                        />
                        <img
                            src={organizer.photo}
                            alt={organizer.photoAlt}
                            width={1066}
                            height={1280}
                            loading="lazy"
                            decoding="async"
                            className="relative aspect-[4/5] w-full rounded-t-full object-cover object-top"
                        />
                    </figure>
                </Reveal>

                <Reveal className="lg:col-span-7 lg:pt-20" delay={1}>
                    <SectionLabel number="02" tone="paper">
                        Meet the organizer
                    </SectionLabel>
                    <h2
                        id="organizer-title"
                        className="font-display mt-5 text-[clamp(3.2rem,9vw,7rem)] leading-[0.92] font-semibold tracking-[-0.04em]"
                    >
                        {organizer.name.split(' ')[0]}
                        <br />
                        <span className="text-blush font-normal italic">
                            {organizer.name.split(' ')[1]}
                        </span>
                    </h2>
                    <p className="text-paper/80 mt-3 text-lg">
                        {organizer.role}
                    </p>

                    {organizer.quote && (
                        <blockquote className="font-display border-blush mt-10 max-w-xl border-l-4 pl-6 text-2xl leading-snug italic md:text-3xl">
                            {organizer.quote}
                        </blockquote>
                    )}

                    <div className="text-paper/90 mt-10 max-w-xl space-y-5 text-lg leading-relaxed">
                        <p>{organizer.intro}</p>
                        <p>{organizer.why}</p>
                    </div>

                    <dl className="border-paper/20 divide-paper/15 mt-12 max-w-2xl divide-y border-t">
                        {organizer.facts.map((fact) => (
                            <div
                                key={fact.term}
                                className="grid gap-1 py-4 sm:grid-cols-[8rem_1fr] sm:gap-6"
                            >
                                <dt className="text-blush text-sm font-semibold tracking-[0.14em] uppercase">
                                    {fact.term}
                                </dt>
                                <dd className="text-paper/90">{fact.detail}</dd>
                            </div>
                        ))}
                    </dl>
                    <p className="text-paper/70 mt-8 text-base">
                        {organizer.closing}
                    </p>
                </Reveal>
            </div>
        </section>
    );
}

const STEP_OFFSETS = ['', 'md:mt-12', 'md:mt-24', 'md:mt-36'];

function Steps() {
    return (
        <section
            id="how-it-works"
            aria-labelledby="steps-title"
            className="bg-paper"
        >
            <div className="mx-auto max-w-7xl px-5 py-24 md:px-8 lg:py-36">
                <Reveal className="max-w-2xl">
                    <SectionLabel number="03">What to expect</SectionLabel>
                    <h2
                        id="steps-title"
                        className="font-display text-ink mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl"
                    >
                        Four small steps. Then you keep going.
                    </h2>
                </Reveal>

                <ol className="mt-16 grid gap-10 md:grid-cols-4 md:gap-8 lg:mt-20">
                    {steps.map((step, index) => (
                        <li key={step.title} className={STEP_OFFSETS[index]}>
                            <Reveal delay={Math.min(index, 3) as 0 | 1 | 2 | 3}>
                                <span
                                    aria-hidden="true"
                                    className="font-display text-primary block text-8xl leading-none italic"
                                >
                                    {index + 1}
                                </span>
                                <div className="bg-ink my-5 h-0.5 w-16" />
                                <h3 className="text-ink text-xl font-semibold">
                                    {step.title}
                                </h3>
                                <p className="text-ink-soft mt-2 max-w-xs text-lg leading-snug">
                                    {step.body}
                                </p>
                            </Reveal>
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}

function Voices() {
    return (
        <section
            id="voices"
            aria-labelledby="voices-title"
            className="bg-paper-deep"
        >
            <div className="mx-auto max-w-7xl px-5 py-24 md:px-8 lg:py-32">
                <Reveal className="max-w-2xl">
                    <SectionLabel number="04">In their words</SectionLabel>
                    <h2
                        id="voices-title"
                        className="font-display text-ink mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl"
                    >
                        Voices from the study.
                    </h2>
                </Reveal>

                {testimonials.length > 0 ? (
                    <div className="mt-14 grid gap-8 md:grid-cols-3">
                        {testimonials.map((item, index) => (
                            <Reveal
                                key={item.name}
                                delay={Math.min(index, 3) as 0 | 1 | 2 | 3}
                                className={index === 1 ? 'md:mt-16' : ''}
                            >
                                <figure>
                                    {item.photo && (
                                        <img
                                            src={item.photo}
                                            alt={`${item.name}`}
                                            loading="lazy"
                                            decoding="async"
                                            className="mb-5 aspect-[4/5] w-full rounded-t-full object-cover"
                                        />
                                    )}
                                    <blockquote className="font-display text-ink text-2xl leading-snug italic">
                                        {item.quote}
                                    </blockquote>
                                    <figcaption className="text-ink-soft mt-4 text-sm">
                                        <span className="text-ink font-semibold">
                                            {item.name}
                                        </span>
                                        {item.detail && `, ${item.detail}`}
                                    </figcaption>
                                </figure>
                            </Reveal>
                        ))}
                    </div>
                ) : (
                    <div className="mt-14 grid gap-6 md:grid-cols-12">
                        <Reveal className="md:col-span-5">
                            <div className="border-ink/30 flex aspect-[4/5] flex-col justify-end rounded-t-full border-2 border-dashed p-8">
                                <Sprinkle className="mb-4" />
                                <p className="text-ink-soft text-sm">
                                    A member photo will sit here.
                                </p>
                            </div>
                        </Reveal>
                        <Reveal className="md:col-span-7 md:pt-24" delay={1}>
                            <div className="border-ink/30 rounded-tl-[3rem] border-2 border-dashed p-8 lg:p-10">
                                <p className="font-display text-ink/70 text-3xl leading-snug italic md:text-4xl">
                                    Real words from real members will be written
                                    here.
                                </p>
                                <p className="text-ink-soft mt-6 text-sm">
                                    Name, and how long they have studied with
                                    us.
                                </p>
                            </div>
                            <div className="border-ink/30 mt-6 ml-0 rounded-br-[3rem] border-2 border-dashed p-8 md:ml-16">
                                <p className="font-display text-ink/70 text-2xl leading-snug italic">
                                    A second story, shorter.
                                </p>
                            </div>
                        </Reveal>
                    </div>
                )}
            </div>
        </section>
    );
}

function Give() {
    return (
        <section
            id="give"
            aria-labelledby="give-title"
            className="grain bg-primary text-primary-foreground relative overflow-hidden"
        >
            <div className="mx-auto max-w-7xl px-5 py-24 md:px-8 lg:py-36">
                <Reveal className="grid gap-8 lg:grid-cols-12">
                    <div className="lg:col-span-7">
                        <p className="flex items-baseline gap-3 text-sm font-semibold tracking-[0.18em] uppercase">
                            <span className="font-display text-base tracking-normal italic">
                                05
                            </span>
                            Give
                        </p>
                        <h2
                            id="give-title"
                            className="font-display mt-5 text-[clamp(3.2rem,9vw,7rem)] leading-[0.92] font-semibold tracking-[-0.04em]"
                        >
                            Sow into <em className="font-normal">the work.</em>
                        </h2>
                    </div>
                    <p className="max-w-md text-lg leading-relaxed lg:col-span-4 lg:col-start-9 lg:self-end">
                        {give.intro}
                    </p>
                </Reveal>

                <ul className="mt-14 grid gap-6 lg:mt-20 lg:grid-cols-2">
                    {give.accounts.map((account, index) => (
                        <li
                            key={account.accountNumber}
                            className={
                                index === 0 ? 'lg:col-span-2' : undefined
                            }
                        >
                            <Reveal
                                delay={Math.min(index, 3) as 0 | 1 | 2 | 3}
                                className="h-full"
                            >
                                <AccountCard
                                    account={account}
                                    featured={index === 0}
                                />
                            </Reveal>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}

function AccountCard({
    account,
    featured,
}: {
    account: DonationAccount;
    featured: boolean;
}) {
    const allDetails = `${account.bank}\n${account.accountName}\n${account.accountNumber} (${account.currency})`;

    return (
        <article
            aria-label={account.label}
            className={`bg-paper text-ink flex h-full flex-col justify-between rounded-tl-[3rem] rounded-tr-md rounded-br-[3rem] rounded-bl-md p-7 sm:p-9 ${featured ? 'lg:p-12' : ''}`}
        >
            <header className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-primary text-sm font-semibold tracking-[0.14em] uppercase">
                        {account.label}
                    </p>
                    <p className="text-ink-soft mt-1">{account.bank}</p>
                </div>
                <span className="bg-ink text-paper rounded-full px-3 py-1 text-sm font-semibold tracking-wider">
                    {account.currency}
                </span>
            </header>

            <div className="border-ink/25 my-8 border-t-2 border-dashed pt-8">
                <p className="text-ink-soft text-sm">Account number</p>
                <p
                    className={`font-display mt-1 font-semibold tracking-[0.04em] tabular-nums ${featured ? 'text-5xl sm:text-7xl' : 'text-4xl sm:text-5xl'}`}
                >
                    {account.accountNumber}
                </p>
                <p className="text-ink-soft mt-4 text-sm">Account name</p>
                <p className="text-lg leading-snug font-semibold">
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

function Closing() {
    return (
        <section aria-labelledby="closing-title" className="bg-paper">
            <div className="mx-auto max-w-7xl px-5 py-24 md:px-8 lg:py-36">
                <Reveal className="grid items-end gap-10 lg:grid-cols-12">
                    <h2
                        id="closing-title"
                        className="font-display text-ink text-[clamp(3.2rem,9vw,7.5rem)] leading-[0.92] font-semibold tracking-[-0.04em] lg:col-span-8"
                    >
                        Come and study{' '}
                        <em className="text-primary font-normal">with us.</em>
                    </h2>
                    <div className="flex flex-col items-start gap-4 lg:col-span-4">
                        <Link
                            href={register()}
                            className={`${buttonBase} bg-primary text-primary-foreground hover:bg-ink focus-visible:ring-primary focus-visible:ring-offset-paper`}
                        >
                            Join the program
                        </Link>
                        <Link
                            href={login()}
                            className="text-ink hover:text-primary decoration-primary focus-visible:ring-primary rounded-sm font-semibold underline underline-offset-4 outline-none focus-visible:ring-[3px]"
                        >
                            Log in to the portal
                        </Link>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}

function Footer() {
    const linkClass =
        'inline-flex items-center gap-2 rounded-sm underline decoration-paper/40 underline-offset-4 outline-none hover:decoration-current focus-visible:ring-[3px] focus-visible:ring-blush';

    return (
        <footer className="bg-ink text-paper">
            <div className="mx-auto grid max-w-7xl gap-10 px-5 py-14 md:grid-cols-12 md:px-8">
                <div className="md:col-span-5">
                    <div className="flex items-center gap-2.5">
                        <AppLogoIcon className="size-9" />
                        <span className="font-display text-xl font-semibold">
                            Sprinkle of Positivity
                        </span>
                    </div>
                    <p className="text-paper/70 mt-4 max-w-xs">
                        A Bible study community, led by {organizer.name}.
                    </p>
                </div>

                <nav aria-label="Portal" className="md:col-span-2">
                    <p className="text-blush mb-3 text-sm font-semibold tracking-[0.14em] uppercase">
                        Portal
                    </p>
                    <ul className="space-y-2">
                        <li>
                            <Link href={login()} className={linkClass}>
                                Log in
                            </Link>
                        </li>
                        <li>
                            <Link href={register()} className={linkClass}>
                                Join
                            </Link>
                        </li>
                        <li>
                            <a href="#give" className={linkClass}>
                                Give
                            </a>
                        </li>
                    </ul>
                </nav>

                <div className="md:col-span-5">
                    <p className="text-blush mb-3 text-sm font-semibold tracking-[0.14em] uppercase">
                        Get in touch
                    </p>
                    <ul className="space-y-2">
                        <li>
                            <a
                                href={`mailto:${contact.email}`}
                                className={linkClass}
                            >
                                <Mail className="size-4" aria-hidden="true" />
                                {contact.email}
                            </a>
                        </li>
                        <li>
                            <a
                                href={contact.whatsapp}
                                className={linkClass}
                                rel="noopener"
                            >
                                <MessageCircle
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                WhatsApp {contact.phoneDisplay}
                            </a>
                        </li>
                        {contact.socials.map((social) => (
                            <li key={social.href}>
                                <a
                                    href={social.href}
                                    className={linkClass}
                                    rel="noopener"
                                >
                                    <ArrowUpRight
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    {social.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
            <div className="border-paper/15 border-t">
                <p className="text-paper/60 mx-auto max-w-7xl px-5 py-6 text-sm md:px-8">
                    © {new Date().getFullYear()} Sprinkle of Positivity
                </p>
            </div>
        </footer>
    );
}

export default function Landing() {
    return (
        <>
            <Head title="Bible study, together">
                <meta
                    head-key="description"
                    name="description"
                    content={program.intro}
                />
                <meta
                    head-key="og:image"
                    property="og:image"
                    content={organizer.photo}
                />
            </Head>

            <a
                href="#about"
                className="bg-ink text-paper focus:ring-primary sr-only rounded-full px-5 py-3 focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:ring-[3px]"
            >
                Skip to content
            </a>

            <div className="landing bg-paper text-ink">
                <Hero />
                <main>
                    <About />
                    <Organizer />
                    <Steps />
                    <Voices />
                    <Give />
                    <Closing />
                </main>
                <Footer />
            </div>
        </>
    );
}

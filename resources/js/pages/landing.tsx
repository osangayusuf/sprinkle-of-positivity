import { Head, Link } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowUpRight,
    Mail,
    MessageCircle,
    Phone,
} from 'lucide-react';
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
    detail: string;
    /** Shown larger. Keep to three or four. */
    featured?: boolean;
};

const program = {
    name: 'Light Shining in the Dark',
    kind: 'A Bible study accountability community',
    intro: "To help people of all ages embrace their identity in God, that they may shine God's light across their spheres of influence without reservation.",
    about: 'You read a chapter of the Bible each day. You write down what you learn. A partner checks in and encourages you. Then, together, you keep going until the word is part of how you live.',
    schedule:
        'One chapter of the Bible every day. Study groups set their own meeting times.',
    venue: 'In the app, and wherever your group meets.',
    audience:
        'People of all ages who want to grow in the word and in community. No Bible school needed.',
    reasons: [
        'Know the Bible better',
        'Get closer to God',
        'Grow in community',
        'Build a daily habit',
        'Deepen my faith',
    ],
};

const benefits = [
    {
        title: 'Consistency in the word',
        body: 'A chapter a day, until reading the Bible is simply what you do.',
    },
    {
        title: 'Deeper study',
        body: 'Writing your insights pushes you past reading for knowledge and into real study.',
    },
    {
        title: 'Accountability',
        body: 'A partner who checks in and cheers you on, so you never carry it alone.',
    },
    {
        title: 'Discernment and discipline',
        body: 'Steadier habits, and clearer judgement in the decisions of daily life.',
    },
    {
        title: 'A growing prayer life',
        body: 'Learn to listen, pray with understanding, and trust where God is leading.',
    },
    {
        title: 'Who you are in God',
        body: 'Know your identity in Him, and let His light show in your home, work and community.',
    },
];

const visioneer = {
    name: 'Nnenna Sam-Obioha',
    photo: '/images/organizer.webp',
    photoAlt: 'Portrait of Nnenna Sam-Obioha in a blue blazer',
    vision: 'Nnenna carries the vision of Light Shining in the Dark: that people of every age would know who they are in God, and shine His light without reservation, in their homes, workplaces and communities.',
    why: 'She leads with the heart of a mentor. Her years of training and walking with people shape how the study is run: patient, practical, and rooted in the word.',
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
            term: 'Serves',
            detail: 'As a facilitator for the Africa Green Grant, and on the governing board of the Tope Olagbegi Circle of Influence',
        },
        {
            term: 'Recognised',
            detail: 'For social impact at the 2024 Global Entrepreneurship Festival',
        },
        {
            term: 'Also created',
            detail: 'The Sprinkle of Positivity app, The Service Excellence Mentor Consulting, and Nut-Just-Salad',
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
        title: 'Read a chapter',
        body: 'Each day there is a chapter to read. Keep the streak going.',
    },
    {
        title: 'Write your insight',
        body: 'Put down what stood out. It is how reading becomes study.',
    },
    {
        title: 'Walk with others',
        body: 'Join a group, meet your accountability partner, and grow together.',
    },
];

const testimonialsCohort = '9th cohort';

const testimonials: Testimonial[] = [
    {
        quote: 'Intimacy and consistency with God’s Word is achievable.',
        name: 'Abuoma Ogbuka',
        detail: 'Public servant, United Kingdom',
        featured: true,
    },
    {
        quote: 'My accountability partner was amazing. She was always checking in and encouraging me.',
        name: 'Success Emebu',
        detail: 'Legal practitioner, Nigeria',
        featured: true,
    },
    {
        quote: 'Trusting God even when we don’t understand what He is doing is key.',
        name: 'Omolara Thomas',
        detail: 'Children and education safeguarding business support, United Kingdom',
        featured: true,
    },
    {
        quote: 'Writing those insights forced me to study deeply and not just read for knowledge.',
        name: 'Eno Onen',
        detail: 'Medical doctor, Nigeria',
    },
    {
        quote: 'One thing that stood out was that God walked with me.',
        name: 'Onyekwere Maryclara',
        detail: 'Financial technology, Nigeria',
    },
    {
        quote: 'I became more discerning and disciplined.',
        name: 'Inegbenose Ibafidon',
        detail: 'Student, Nigeria',
    },
    {
        quote: 'I’ve learned to listen more, pray with greater understanding, and trust God’s leading.',
        name: 'Adebowale Isaac',
        detail: 'Banking, Nigeria',
    },
    {
        quote: 'This experience has made me more aware of the person of God and the essence of His judgement.',
        name: 'Deji Durotimi',
        detail: 'Project manager, United Kingdom',
    },
    {
        quote: 'I’ve grown significantly and feel more grounded in my faith.',
        name: 'Blessing Ogunsanwo',
        detail: 'Medical practitioner, Nigeria',
    },
    {
        quote: 'This experience helped me to be consistent with the word.',
        name: 'Omolara Adekunbi',
        detail: 'Pharmaceutical marketing, Nigeria',
    },
    {
        quote: 'I had clearer dreams, and became more discerning, prayed more.',
        name: 'Chidimma Ikemka',
        detail: 'Business, Nigeria',
    },
    {
        quote: 'It has taught me to be consistent with Bible reading and comprehension.',
        name: 'Sunkanmi Ogunsanwo',
        detail: 'Medical practitioner, Nigeria',
    },
    {
        quote: 'My study of the Word has grown deeply.',
        name: 'Mosimiloluwa Oduke',
        detail: 'Financial compliance analyst, Canada',
    },
];

const contact = {
    /** Placeholder. Replace with the real address. */
    email: 'hello@example.com',
    phoneDisplay: '0808 111 4235',
    phoneLink: 'tel:+2348081114235',
    whatsapp: 'https://wa.me/2348081114235',
    instagramHandle: '@thebiblestudycommunity',
    instagram: 'https://www.instagram.com/thebiblestudycommunity',
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

/* ---------------------------------------------------------------------- */

const buttonBase =
    'inline-flex min-h-12 items-center justify-center gap-2 rounded-full px-7 text-base font-semibold transition-[background-color,color,transform] duration-200 outline-none focus-visible:ring-[3px] focus-visible:ring-offset-2 motion-safe:active:scale-[0.98]';

const linkBase =
    'text-ink hover:text-primary decoration-primary focus-visible:ring-primary rounded-sm font-semibold underline underline-offset-4 outline-none focus-visible:ring-[3px]';

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
                    Light Shining
                    <br />
                    in the Dark
                </span>
            </Link>
            <nav
                aria-label="Main"
                className="flex items-center gap-1 text-sm font-semibold sm:gap-3"
            >
                <a
                    href="#contact"
                    className="text-ink hover:text-primary focus-visible:ring-primary hidden rounded-full px-3 py-3 outline-none focus-visible:ring-[3px] sm:inline-block"
                >
                    Contact
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
                        {program.kind}
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
                        <Link href={login()} className={linkBase}>
                            Already a member? Log in
                        </Link>
                        <a
                            href="#support"
                            className="text-ink-soft hover:text-primary focus-visible:ring-primary inline-flex items-center gap-1.5 rounded-sm text-base outline-none focus-visible:ring-[3px]"
                        >
                            Support the program
                            <ArrowDown className="size-4" aria-hidden="true" />
                        </a>
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
                            A chapter a day. Someone beside you.
                        </h2>
                        <p className="text-ink-soft mt-8 max-w-lg text-lg leading-relaxed">
                            {program.about}
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

function Benefits() {
    return (
        <section
            id="benefits"
            aria-labelledby="benefits-title"
            className="bg-paper-deep"
        >
            <div className="mx-auto grid max-w-7xl gap-14 px-5 py-24 md:px-8 lg:grid-cols-12 lg:py-32">
                <Reveal className="lg:col-span-4">
                    <div className="lg:sticky lg:top-12">
                        <SectionLabel number="02">The benefits</SectionLabel>
                        <h2
                            id="benefits-title"
                            className="font-display text-ink mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl"
                        >
                            What changes when you stay in the word.
                        </h2>
                    </div>
                </Reveal>

                <ul className="divide-ink/20 border-ink/20 divide-y border-y lg:col-span-7 lg:col-start-6">
                    {benefits.map((benefit, index) => (
                        <li key={benefit.title}>
                            <Reveal
                                className="grid gap-2 py-7 md:grid-cols-[1fr_1.1fr] md:gap-10"
                                delay={Math.min(index % 3, 3) as 0 | 1 | 2 | 3}
                            >
                                <h3 className="font-display text-ink text-2xl leading-tight font-semibold">
                                    {benefit.title}
                                </h3>
                                <p className="text-ink-soft text-lg leading-snug">
                                    {benefit.body}
                                </p>
                            </Reveal>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}

function Visioneer() {
    const [first, last] = visioneer.name.split(' ');

    return (
        <section
            id="visioneer"
            aria-labelledby="visioneer-title"
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
                            src={visioneer.photo}
                            alt={visioneer.photoAlt}
                            width={1066}
                            height={1280}
                            loading="lazy"
                            decoding="async"
                            className="relative aspect-[4/5] w-full rounded-t-full object-cover object-top"
                        />
                    </figure>
                </Reveal>

                <Reveal className="lg:col-span-7 lg:pt-20" delay={1}>
                    <SectionLabel number="03" tone="paper">
                        Meet the visioneer
                    </SectionLabel>
                    <h2
                        id="visioneer-title"
                        className="font-display mt-5 text-[clamp(3.2rem,9vw,7rem)] leading-[0.92] font-semibold tracking-[-0.04em]"
                    >
                        {first}
                        <br />
                        <span className="text-blush font-normal italic">
                            {last}
                        </span>
                    </h2>

                    {visioneer.quote && (
                        <blockquote className="font-display border-blush mt-10 max-w-xl border-l-4 pl-6 text-2xl leading-snug italic md:text-3xl">
                            {visioneer.quote}
                        </blockquote>
                    )}

                    <div className="text-paper/90 mt-10 max-w-xl space-y-5 text-lg leading-relaxed">
                        <p className="font-display text-paper text-2xl leading-snug md:text-3xl">
                            {visioneer.vision}
                        </p>
                        <p>{visioneer.why}</p>
                    </div>

                    <dl className="divide-paper/15 border-paper/20 mt-12 max-w-2xl divide-y border-t">
                        {visioneer.facts.map((fact) => (
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
                        {visioneer.closing}
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
                    <SectionLabel number="04">What to expect</SectionLabel>
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
            className="grain grain-light bg-ink text-paper relative overflow-hidden"
        >
            <div className="mx-auto max-w-7xl px-5 py-24 md:px-8 lg:py-36">
                <Reveal className="max-w-2xl">
                    <SectionLabel number="05" tone="paper">
                        {`In their words · ${testimonialsCohort}`}
                    </SectionLabel>
                    <h2
                        id="voices-title"
                        className="font-display mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl lg:text-7xl"
                    >
                        Walking with God,{' '}
                        <em className="text-blush font-normal">together.</em>
                    </h2>
                </Reveal>

                <div className="mt-16 gap-x-12 md:columns-2 lg:columns-3">
                    {testimonials.map((item, index) => (
                        <Reveal
                            key={item.name}
                            delay={Math.min(index % 3, 3) as 0 | 1 | 2 | 3}
                            className="mb-12 break-inside-avoid"
                        >
                            <figure className="border-paper/20 border-t pt-6">
                                <blockquote
                                    className={
                                        item.featured
                                            ? 'font-display text-3xl leading-snug italic'
                                            : 'text-paper/95 text-xl leading-snug'
                                    }
                                >
                                    {item.quote}
                                </blockquote>
                                <figcaption className="mt-4 text-sm">
                                    <span className="text-blush font-semibold">
                                        {item.name}
                                    </span>
                                    <span className="text-paper/70 block">
                                        {item.detail}
                                    </span>
                                </figcaption>
                            </figure>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}

function Contact() {
    const rowClass =
        'group inline-flex items-center gap-3 rounded-sm outline-none focus-visible:ring-[3px] focus-visible:ring-primary';

    return (
        <section
            id="contact"
            aria-labelledby="contact-title"
            className="bg-paper"
        >
            <div className="mx-auto grid max-w-7xl gap-12 px-5 py-24 md:px-8 lg:grid-cols-12 lg:py-36">
                <Reveal className="lg:col-span-6">
                    <SectionLabel number="06">Stay in touch</SectionLabel>
                    <h2
                        id="contact-title"
                        className="font-display text-ink mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl"
                    >
                        Questions? Come and say hello.
                    </h2>
                    <a
                        href={contact.instagram}
                        rel="noopener"
                        className="font-display text-primary decoration-primary/40 hover:decoration-primary focus-visible:ring-primary mt-10 inline-flex items-center gap-2 rounded-sm text-3xl leading-tight break-all underline underline-offset-8 outline-none focus-visible:ring-[3px] md:text-4xl"
                    >
                        {contact.instagramHandle}
                        <ArrowUpRight
                            className="size-7 shrink-0"
                            aria-hidden="true"
                        />
                    </a>
                    <p className="text-ink-soft mt-3">
                        Follow the community on Instagram.
                    </p>
                </Reveal>

                <Reveal
                    className="lg:col-span-5 lg:col-start-8 lg:pt-32"
                    delay={1}
                >
                    <ul className="divide-ink/15 border-ink/15 divide-y border-y text-lg">
                        <li className="py-5">
                            <a href={contact.phoneLink} className={rowClass}>
                                <Phone
                                    className="text-primary size-5"
                                    aria-hidden="true"
                                />
                                <span className="text-ink group-hover:text-primary font-semibold">
                                    Call {contact.phoneDisplay}
                                </span>
                            </a>
                        </li>
                        <li className="py-5">
                            <a
                                href={contact.whatsapp}
                                rel="noopener"
                                className={rowClass}
                            >
                                <MessageCircle
                                    className="text-primary size-5"
                                    aria-hidden="true"
                                />
                                <span className="text-ink group-hover:text-primary font-semibold">
                                    WhatsApp {contact.phoneDisplay}
                                </span>
                            </a>
                        </li>
                        <li className="py-5">
                            <a
                                href={`mailto:${contact.email}`}
                                className={rowClass}
                            >
                                <Mail
                                    className="text-primary size-5"
                                    aria-hidden="true"
                                />
                                <span className="text-ink group-hover:text-primary font-semibold">
                                    {contact.email}
                                </span>
                            </a>
                        </li>
                    </ul>
                </Reveal>
            </div>
        </section>
    );
}

function Support() {
    return (
        <section
            id="support"
            aria-labelledby="support-title"
            className="bg-paper-deep"
        >
            <div className="mx-auto max-w-7xl px-5 py-20 md:px-8 lg:py-28">
                <Reveal className="grid gap-6 lg:grid-cols-12">
                    <div className="lg:col-span-6">
                        <SectionLabel number="07">
                            Support the program
                        </SectionLabel>
                        <h2
                            id="support-title"
                            className="font-display text-ink mt-4 text-4xl leading-[1] font-semibold tracking-[-0.03em] md:text-5xl"
                        >
                            Help keep the study going.
                        </h2>
                    </div>
                    <p className="text-ink-soft max-w-md text-lg leading-relaxed lg:col-span-5 lg:col-start-8 lg:self-end">
                        {support.intro}
                    </p>
                </Reveal>

                <ul className="mt-10 grid gap-5 lg:grid-cols-[1.25fr_1fr_1fr]">
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
        </section>
    );
}

function AccountCard({ account }: { account: DonationAccount }) {
    const allDetails = `${account.bank}\n${account.accountName}\n${account.accountNumber} (${account.currency})`;

    return (
        <article
            aria-label={account.label}
            className="bg-paper text-ink border-ink/15 flex h-full flex-col justify-between rounded-tl-[2.5rem] rounded-tr-md rounded-br-[2.5rem] rounded-bl-md border p-6 sm:p-7"
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
                        <Link href={login()} className={linkBase}>
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
                            {program.name}
                        </span>
                    </div>
                    <p className="text-paper/70 mt-4 max-w-xs">
                        {program.kind}, led by {visioneer.name}.
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
                            <a href="#support" className={linkClass}>
                                Support the program
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
                                href={contact.instagram}
                                className={linkClass}
                                rel="noopener"
                            >
                                <ArrowUpRight
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                Instagram {contact.instagramHandle}
                            </a>
                        </li>
                        <li>
                            <a href={contact.phoneLink} className={linkClass}>
                                <Phone className="size-4" aria-hidden="true" />
                                {contact.phoneDisplay}
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
                                WhatsApp
                            </a>
                        </li>
                        <li>
                            <a
                                href={`mailto:${contact.email}`}
                                className={linkClass}
                            >
                                <Mail className="size-4" aria-hidden="true" />
                                {contact.email}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div className="border-paper/15 border-t">
                <p className="text-paper/60 mx-auto max-w-7xl px-5 py-6 text-sm md:px-8">
                    © {new Date().getFullYear()} {program.name}
                </p>
            </div>
        </footer>
    );
}

export default function Landing() {
    return (
        <>
            <Head title="Bible study accountability community">
                <meta
                    head-key="description"
                    name="description"
                    content={program.intro}
                />
                <meta
                    head-key="og:image"
                    property="og:image"
                    content={visioneer.photo}
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
                    <Benefits />
                    <Visioneer />
                    <Steps />
                    <Voices />
                    <Contact />
                    <Support />
                    <Closing />
                </main>
                <Footer />
            </div>
        </>
    );
}

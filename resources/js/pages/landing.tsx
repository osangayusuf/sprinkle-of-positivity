import { Head, Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    CalendarDays,
    Flame,
    HandHeart,
    Award,
    Coffee,
    GraduationCap,
    Mail,
    Menu,
    MessageCircle,
    Phone,
    Users,
} from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import Reveal from '@/components/landing/reveal';
import { Sprinkle } from '@/components/landing/sprinkle';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    accountabilityPartners,
    home,
    login,
    register,
    support as supportPage,
} from '@/routes';

/* -------------------------------------------------------------------------
 * Edit content here. Nothing below this block needs to change for copy,
 * people, photos or links.
 * ---------------------------------------------------------------------- */

const program = {
    name: 'Light Shining in the Dark',
    kind: 'A Bible study accountability community',
    headline: 'A Virtual Cave of Adullam',
    visionTitle: 'Our Vision',
    intro: '“To help people of all ages embrace their identity in God, that they may shine God’s light across their spheres of influence without reservation.”',
};

const anchor = {
    reference: 'Matthew 5:14–16',
    version: 'KJV',
    verses: [
        {
            number: 14,
            text: 'Ye are the light of the world. A city that is set on an hill cannot be hid.',
        },
        {
            number: 15,
            text: 'Neither do men light a candle, and put it under a bushel, but on a candlestick; and it giveth light unto all that are in the house.',
        },
        {
            number: 16,
            text: 'Let your light so shine before men, that they may see your good works, and glorify your Father which is in heaven.',
        },
    ],
};

const visioneer = {
    name: 'Nnenna Sam-Obioha',
    role: 'Business Leader / Visioneer',
    photo: '/images/organizer.webp',
    photoAlt: 'Portrait of Nnenna Sam-Obioha',
    bio: [
        'Nnenna Sam-Obioha is a business leader, customer experience consultant, and author with over a decade of experience in telecomms, Supply Chain and financial services.',
        'She holds a BSc and MSc in Psychology and specializes in service efficiency, operations, and people development.',
        'Driven by her faith in God and a passion for social impact and innovation, Nnenna is the founder of The Service Excellence Mentor Consulting and Nut Just Salad Enterprise. She is an author of three books and a recipient of the 2024 Global Entrepreneurship Festival Award for Social Impact.',
        "As the Visioneer of Light Shining in the Dark, she works alongside a dedicated team of Volunteer Accountability Partners to nurture and advance God's mandate to go into the world and make disciples of all nations.",
    ],
};

const partners = {
    title: 'Volunteer accountability partners',
    body: 'Every participant is paired with a volunteer accountability partner. They check in, encourage you on the slow days, pray with you, and hold your hand from the first chapter to the sixtieth day. The community runs on their generosity and faithfulness.',
    duties: ['Check in daily', 'Encourage and pray', 'Walk you to the finish'],
};

const activities = [
    {
        icon: CalendarDays,
        title: 'Monthly live Bible study',
        body: 'We open our doors to non-members of the community to join our sessions every third Thursday of the month.',
        tag: '3rd Thursday',
        wide: true,
    },
    {
        icon: Flame,
        title: 'Fasting and prayer watch hour',
        body: 'We host weekly fasting and prayer watches.',
        tag: 'Weekly',
        wide: true,
    },
    {
        icon: Award,
        title: '60-day certification',
        body: 'To encourage consistency, certificates are issued to participants after the first 60 days of daily bible study and submission of bible study learnings.',
        tag: '60 days',
    },
    {
        icon: GraduationCap,
        title: 'Alumni community',
        body: 'Join the Alumni Group to continue studying after 60 days of consistency.',
        tag: 'After 60 days',
    },
    {
        icon: Coffee,
        title: 'Community hangouts',
        body: 'Hangouts are held periodically.',
        tag: 'Periodic',
    },
];

const contact = {
    email: 'l.i.dbiblestudypartners@gmail.com',
    phoneDisplay: '0808 111 4235',
    phoneLink: 'tel:+2348081114235',
    whatsapp: 'https://wa.me/2348081114235',
    instagramHandle: '@thebiblestudycommunity',
    instagram: 'https://www.instagram.com/thebiblestudycommunity',
};

/* ---------------------------------------------------------------------- */

const buttonBase =
    'inline-flex min-h-12 items-center justify-center gap-2 rounded-full px-7 text-base font-semibold transition-[background-color,color,transform] duration-200 outline-none focus-visible:ring-[3px] focus-visible:ring-offset-2 motion-safe:active:scale-[0.98]';

const linkBase =
    'text-ink hover:text-primary decoration-primary focus-visible:ring-primary rounded-sm font-semibold underline underline-offset-4 outline-none focus-visible:ring-[3px]';

function SectionLabel({
    children,
    tone = 'ink',
}: {
    children: string;
    tone?: 'ink' | 'paper';
}) {
    return (
        <p
            className={
                tone === 'paper'
                    ? 'text-blush text-sm font-semibold tracking-[0.18em] uppercase'
                    : 'text-primary text-sm font-semibold tracking-[0.18em] uppercase'
            }
        >
            {children}
        </p>
    );
}

function Nav() {
    const mobileLinkClass =
        'hover:text-primary focus-visible:ring-primary rounded-md px-3 py-3 outline-none focus-visible:ring-[3px]';

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
                className="hidden items-center justify-end gap-3 text-sm font-semibold md:flex"
            >
                <a
                    href="#activities"
                    className="text-ink hover:text-primary focus-visible:ring-primary rounded-full px-3 py-3 outline-none focus-visible:ring-[3px]"
                >
                    Activities
                </a>
                <Link
                    href={accountabilityPartners()}
                    className="text-ink hover:text-primary focus-visible:ring-primary rounded-full px-3 py-3 outline-none focus-visible:ring-[3px]"
                >
                    Accountability Partners
                </Link>
                <Link
                    href={supportPage()}
                    className="text-ink hover:text-primary focus-visible:ring-primary rounded-full px-3 py-3 outline-none focus-visible:ring-[3px]"
                >
                    Give / Support
                </Link>
                <Link
                    href={home()}
                    className="text-ink hover:text-primary focus-visible:ring-primary rounded-full px-3 py-3 outline-none focus-visible:ring-[3px]"
                >
                    View as guest
                </Link>
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
            <Sheet>
                <SheetTrigger asChild>
                    <button
                        type="button"
                        aria-label="Open menu"
                        className="text-ink hover:text-primary focus-visible:ring-primary flex size-11 items-center justify-center rounded-full outline-none focus-visible:ring-[3px] md:hidden"
                    >
                        <Menu className="size-6" aria-hidden="true" />
                    </button>
                </SheetTrigger>
                <SheetContent
                    side="right"
                    className="bg-paper text-ink w-72 border-none"
                >
                    <SheetTitle className="sr-only">Menu</SheetTitle>
                    <SheetDescription className="sr-only">
                        Site navigation
                    </SheetDescription>
                    <nav
                        aria-label="Mobile"
                        className="font-display mt-14 flex flex-col gap-1 px-4 text-2xl font-semibold"
                    >
                        <SheetClose asChild>
                            <a href="#activities" className={mobileLinkClass}>
                                Activities
                            </a>
                        </SheetClose>
                        <Link
                            href={accountabilityPartners()}
                            className={mobileLinkClass}
                        >
                            Accountability Partners
                        </Link>
                        <Link href={supportPage()} className={mobileLinkClass}>
                            Give / Support
                        </Link>
                        <Link href={home()} className={mobileLinkClass}>
                            View as guest
                        </Link>
                        <Link href={login()} className={mobileLinkClass}>
                            Log in
                        </Link>
                        <Link
                            href={register()}
                            className="bg-ink text-paper hover:bg-primary focus-visible:ring-primary mt-4 rounded-full px-5 py-3 text-center text-base outline-none focus-visible:ring-[3px]"
                        >
                            Join
                        </Link>
                    </nav>
                </SheetContent>
            </Sheet>
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
            <div className="relative mx-auto grid max-w-7xl gap-10 px-5 pt-10 pb-20 md:px-8 lg:grid-cols-12 lg:pt-16 lg:pb-32">
                <div className="relative z-10 lg:col-span-8 lg:self-center">
                    <p className="text-primary mb-6 text-sm font-semibold tracking-[0.18em] uppercase">
                        {program.kind}
                    </p>
                    <h1 className="font-display text-ink text-[clamp(3.2rem,8vw,6.5rem)] leading-[0.92] font-semibold tracking-[-0.04em]">
                        {program.headline}
                    </h1>
                    <section
                        aria-labelledby="vision-title"
                        className="mt-8 max-w-xl"
                    >
                        <h2
                            id="vision-title"
                            className="text-primary text-sm font-semibold tracking-[0.18em] uppercase"
                        >
                            {program.visionTitle}
                        </h2>
                        <p className="text-ink-soft mt-3 text-lg leading-relaxed md:text-xl">
                            {program.intro}
                        </p>
                    </section>
                    <div className="mt-10 flex flex-wrap items-center gap-x-6 gap-y-4">
                        <Link
                            href={register()}
                            className={`${buttonBase} bg-primary text-primary-foreground hover:bg-ink focus-visible:ring-primary focus-visible:ring-offset-paper`}
                        >
                            Join the program
                        </Link>
                        <Link href={home()} className={linkBase}>
                            Explore the community
                        </Link>
                        <Link href={login()} className={linkBase}>
                            Already a member? Log in
                        </Link>
                    </div>
                </div>
                <figure className="relative mx-auto w-full max-w-md pl-4 lg:col-span-4 lg:mx-0 lg:max-w-none lg:self-center">
                    <div
                        aria-hidden="true"
                        className="bg-primary absolute -bottom-4 left-0 h-[96%] w-[94%] rounded-t-full"
                    />
                    <img
                        src="/images/hero.webp"
                        alt="A family studying the Bible together around a wooden table, smiling, with journals and a lit candle"
                        width={928}
                        height={1152}
                        fetchPriority="high"
                        decoding="async"
                        className="relative aspect-[4/5] w-full rounded-t-full object-cover object-center"
                    />
                </figure>
            </div>
        </div>
    );
}

function Anchor() {
    return (
        <section
            id="anchor"
            aria-labelledby="anchor-title"
            className="grain grain-light bg-ink text-paper relative overflow-hidden"
        >
            <Sprinkle
                tone="gold"
                className="absolute top-16 right-[8%] hidden rotate-[40deg] lg:block"
            />
            <div className="mx-auto grid max-w-7xl gap-14 px-5 py-20 md:px-8 lg:grid-cols-12 lg:gap-16 lg:py-28">
                <Reveal className="lg:col-span-12">
                    <SectionLabel tone="paper">
                        Our anchor scripture
                    </SectionLabel>
                    <h2
                        id="anchor-title"
                        className="font-display mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl"
                    >
                        {anchor.reference}
                    </h2>
                    <blockquote className="border-blush mt-8 space-y-3 border-l-4 pl-6">
                        {anchor.verses.map((verse) => (
                            <p
                                key={verse.number}
                                className="font-display text-xl leading-snug italic md:text-2xl"
                            >
                                <sup className="text-blush mr-2 text-xs not-italic">
                                    {verse.number}
                                </sup>
                                {verse.text}
                            </p>
                        ))}
                    </blockquote>
                    <p className="text-paper/60 mt-3 text-sm">
                        {anchor.reference} · {anchor.version}
                    </p>
                </Reveal>
            </div>
        </section>
    );
}

function Visioneer() {
    return (
        <section
            id="visioneer"
            aria-labelledby="visioneer-title"
            className="bg-paper-deep"
        >
            <div className="mx-auto grid max-w-7xl gap-10 px-5 py-20 md:px-8 lg:grid-cols-12 lg:gap-16 lg:py-28">
                <Reveal className="lg:col-span-4">
                    <img
                        src={visioneer.photo}
                        alt={visioneer.photoAlt}
                        width={480}
                        height={600}
                        loading="lazy"
                        decoding="async"
                        className="aspect-[4/5] w-full max-w-sm rounded-t-full object-cover object-top"
                    />
                </Reveal>
                <Reveal className="lg:col-span-8 lg:self-center" delay={1}>
                    <SectionLabel>Meet our Visioneer</SectionLabel>
                    <h2
                        id="visioneer-title"
                        className="font-display text-ink mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl"
                    >
                        {visioneer.name}
                    </h2>
                    <p className="text-primary mt-3 text-lg font-semibold">
                        {visioneer.role}
                    </p>
                    <div className="text-ink-soft mt-6 max-w-2xl space-y-4 text-lg leading-relaxed">
                        {visioneer.bio.map((paragraph) => (
                            <p key={paragraph}>{paragraph}</p>
                        ))}
                    </div>
                </Reveal>
            </div>
        </section>
    );
}

function Activities() {
    return (
        <section
            id="activities"
            aria-labelledby="activities-title"
            className="bg-paper"
        >
            <div className="mx-auto max-w-7xl px-5 py-20 md:px-8 lg:py-28">
                <Reveal className="max-w-2xl">
                    <SectionLabel>Life in the community</SectionLabel>
                    <h2
                        id="activities-title"
                        className="font-display text-ink mt-5 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl"
                    >
                        More than a reading plan.
                    </h2>
                </Reveal>

                <ul className="mt-12 grid gap-4 md:grid-cols-6">
                    {activities.map((item, index) => (
                        <li
                            key={item.title}
                            className={
                                item.wide ? 'md:col-span-3' : 'md:col-span-2'
                            }
                        >
                            <Reveal
                                delay={Math.min(index % 3, 3) as 0 | 1 | 2 | 3}
                                className="h-full"
                            >
                                <article className="bg-paper-deep border-ink/10 flex h-full flex-col rounded-tl-[2rem] rounded-tr-md rounded-br-[2rem] rounded-bl-md border p-6">
                                    <div className="flex items-center justify-between gap-3">
                                        <item.icon
                                            className="text-primary size-7"
                                            aria-hidden="true"
                                        />
                                        <span className="bg-ink text-paper rounded-full px-3 py-1 text-xs font-semibold tracking-wider">
                                            {item.tag}
                                        </span>
                                    </div>
                                    <h3 className="font-display text-ink mt-5 text-2xl leading-tight font-semibold">
                                        {item.title}
                                    </h3>
                                    <p className="text-ink-soft mt-2 text-lg leading-snug">
                                        {item.body}
                                    </p>
                                </article>
                            </Reveal>
                        </li>
                    ))}

                    <li className="md:col-span-6">
                        <Reveal>
                            <article
                                aria-labelledby="partners-title"
                                className="bg-primary text-primary-foreground grid gap-6 rounded-tl-md rounded-tr-[2.5rem] rounded-br-md rounded-bl-[2.5rem] p-7 md:grid-cols-12 md:items-center md:p-10"
                            >
                                <div className="md:col-span-7">
                                    <HandHeart
                                        className="size-8"
                                        aria-hidden="true"
                                    />
                                    <h3
                                        id="partners-title"
                                        className="font-display mt-4 text-3xl leading-tight font-semibold md:text-4xl"
                                    >
                                        {partners.title}
                                    </h3>
                                    <p className="mt-3 max-w-xl text-lg leading-relaxed opacity-95">
                                        {partners.body}
                                    </p>
                                </div>
                                <ul className="flex flex-wrap gap-2 md:col-span-5 md:justify-end">
                                    {partners.duties.map((duty) => (
                                        <li
                                            key={duty}
                                            className="border-primary-foreground/50 rounded-full border px-4 py-1.5 text-sm font-semibold"
                                        >
                                            {duty}
                                        </li>
                                    ))}
                                </ul>
                            </article>
                        </Reveal>
                    </li>
                </ul>
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
            <div className="mx-auto max-w-7xl px-5 py-20 md:px-8 lg:py-28">
                <Reveal className="flex flex-col items-start gap-6">
                    <SectionLabel>Testimonials</SectionLabel>
                    <h2 id="voices-title" className="sr-only">
                        Testimonials
                    </h2>
                    <Link
                        href="/testimonials"
                        className={`${buttonBase} bg-primary text-primary-foreground hover:bg-ink focus-visible:ring-primary focus-visible:ring-offset-paper`}
                    >
                        See What Past Participants Have to Say
                        <ArrowUpRight className="size-5" aria-hidden="true" />
                    </Link>
                </Reveal>
            </div>
        </section>
    );
}

function Footer() {
    const linkClass =
        'inline-flex items-center gap-2 rounded-sm underline decoration-paper/40 underline-offset-4 outline-none hover:decoration-current focus-visible:ring-[3px] focus-visible:ring-blush';

    return (
        <footer id="contact" className="bg-ink text-paper">
            <div className="mx-auto grid max-w-7xl gap-10 px-5 py-14 md:grid-cols-12 md:px-8">
                <div className="md:col-span-6">
                    <div className="flex items-center gap-2.5">
                        <AppLogoIcon className="size-9" />
                        <span className="font-display text-xl font-semibold">
                            {program.name}
                        </span>
                    </div>
                    <p className="text-paper/70 mt-4 max-w-xs">
                        {program.kind}.
                    </p>
                </div>

                <div className="md:col-span-6">
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
                        <li>
                            <Link
                                href={accountabilityPartners()}
                                className={linkClass}
                            >
                                <Users className="size-4" aria-hidden="true" />
                                Accountability Partners
                            </Link>
                        </li>
                        <li>
                            <Link href={supportPage()} className={linkClass}>
                                <HandHeart
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                Give / Support
                            </Link>
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
            </Head>

            <a
                href="#anchor"
                className="bg-ink text-paper focus:ring-primary sr-only rounded-full px-5 py-3 focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:ring-[3px]"
            >
                Skip to content
            </a>

            <div className="landing bg-paper text-ink">
                <Hero />
                <main>
                    <Anchor />
                    <Visioneer />
                    <Activities />
                    <Voices />
                </main>
                <Footer />
            </div>
        </>
    );
}

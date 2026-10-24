import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Reveal from '@/components/landing/reveal';
import Seo from '@/components/seo';

type Contents = {
    title: string;
    id: string;
    sections: { title: string; id: string }[];
}[];

const AUDIENCES = [
    { label: 'Member', id: 'part-2--member-guide' },
    { label: 'Partner', id: 'part-3--accountability-partner-guide' },
    { label: 'Admin', id: 'part-4--admin-guide' },
];

const linkClass =
    'text-ink-soft hover:text-primary focus-visible:ring-primary block rounded-sm py-1 leading-snug outline-none focus-visible:ring-[3px]';

// The manual is trusted markdown rendered on the server, so its elements are
// styled here by selector. Every class is a literal so Tailwind can see it.
const proseClass = [
    'text-ink min-w-0 max-w-[72ch] leading-relaxed',
    '[&_h1]:font-display [&_h1]:border-primary [&_h1]:mt-16 [&_h1]:scroll-mt-6 [&_h1]:border-t-4 [&_h1]:pt-6 [&_h1]:text-4xl [&_h1]:leading-tight [&_h1]:font-semibold [&_h1]:tracking-tight [&_h1]:text-balance',
    '[&_h2]:font-display [&_h2]:mt-10 [&_h2]:scroll-mt-6 [&_h2]:text-2xl [&_h2]:leading-snug [&_h2]:font-semibold [&_h2]:text-balance',
    '[&_h3]:mt-8 [&_h3]:text-lg [&_h3]:font-semibold',
    '[&_p]:mt-3',
    '[&_ol]:mt-3 [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-6',
    '[&_li]:mt-1.5 [&_li]:pl-1 [&_li::marker]:text-primary [&_li::marker]:font-semibold',
    '[&_a]:text-ink [&_a]:decoration-primary [&_a:hover]:text-primary [&_a]:underline [&_a]:underline-offset-4',
    '[&_code]:bg-paper-deep [&_code]:rounded [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-[0.9em]',
    '[&_hr]:border-ink/15 [&_hr]:my-10',
    '[&_blockquote]:bg-paper-deep [&_blockquote]:border-primary [&_blockquote]:mt-5 [&_blockquote]:rounded-r-md [&_blockquote]:border-l-4 [&_blockquote]:px-4 [&_blockquote]:py-2',
    '[&_table]:border-ink/15 [&_table]:mt-5 [&_table]:block [&_table]:max-w-full [&_table]:overflow-x-auto [&_table]:rounded-md [&_table]:border [&_table]:text-sm',
    '[&_th]:bg-paper-deep [&_th]:px-3 [&_th]:py-2 [&_th]:text-left [&_th]:font-semibold [&_th]:whitespace-nowrap',
    '[&_td]:border-ink/10 [&_td]:border-t [&_td]:px-3 [&_td]:py-2 [&_td]:align-top',
].join(' ');

export default function Manual({
    html,
    contents,
}: {
    html: string;
    contents: Contents;
}) {
    return (
        <>
            <Seo
                title="User manual"
                description="The complete guide to using Light Shining in the Dark: member guide, groups, quizzes and more."
            />
            <div className="landing bg-paper text-ink min-h-screen">
                <div className="mx-auto max-w-7xl px-5 py-10 md:px-8 lg:py-16">
                    <Link
                        href="/"
                        className="text-ink hover:text-primary decoration-primary focus-visible:ring-primary inline-flex items-center gap-2 rounded-sm font-semibold underline underline-offset-4 outline-none focus-visible:ring-[3px]"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        Back to home
                    </Link>

                    <Reveal className="mt-8 max-w-3xl">
                        <p className="text-primary text-sm font-semibold tracking-[0.18em] uppercase">
                            Light Shining in the Dark
                        </p>
                        <h1 className="font-display text-ink mt-4 text-5xl leading-[0.98] font-semibold tracking-[-0.03em] text-balance md:text-6xl">
                            User manual
                        </h1>
                        <p className="text-ink-soft mt-5 max-w-md text-lg leading-relaxed">
                            Step-by-step instructions for members,
                            accountability partners and admins.
                        </p>
                        <ul className="mt-6 flex flex-wrap gap-2">
                            {AUDIENCES.map((audience) => (
                                <li key={audience.id}>
                                    <a
                                        href={`#${audience.id}`}
                                        className="border-ink text-ink hover:bg-ink hover:text-paper focus-visible:ring-primary inline-block rounded-full border px-4 py-1.5 text-sm font-semibold outline-none focus-visible:ring-[3px]"
                                    >
                                        {audience.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </Reveal>

                    <div className="mt-12 grid gap-10 lg:grid-cols-[17rem_minmax(0,1fr)] lg:gap-14">
                        <aside className="lg:sticky lg:top-6 lg:max-h-[calc(100vh-3rem)] lg:self-start lg:overflow-y-auto">
                            <details className="bg-paper-deep border-ink/15 rounded-md border p-4 lg:hidden">
                                <summary className="cursor-pointer text-sm font-semibold">
                                    Contents
                                </summary>
                                <ContentsList contents={contents} />
                            </details>
                            <div className="hidden lg:block">
                                <p className="text-ink-soft mb-3 text-xs font-semibold tracking-[0.14em] uppercase">
                                    Contents
                                </p>
                                <ContentsList contents={contents} />
                            </div>
                        </aside>

                        <article
                            className={proseClass}
                            dangerouslySetInnerHTML={{ __html: html }}
                        />
                    </div>
                </div>
            </div>
        </>
    );
}

function ContentsList({ contents }: { contents: Contents }) {
    return (
        <nav aria-label="Contents" className="mt-3 text-sm lg:mt-0">
            <ul className="grid gap-4">
                {contents.map((part) => (
                    <li key={part.title}>
                        {part.id ? (
                            <a
                                href={`#${part.id}`}
                                className={`${linkClass} text-ink font-semibold`}
                            >
                                {part.title}
                            </a>
                        ) : (
                            <span className="font-semibold">{part.title}</span>
                        )}
                        <ul className="border-ink/15 mt-1 ml-1 border-l pl-3">
                            {part.sections.map((section) => (
                                <li key={section.id}>
                                    <a
                                        href={`#${section.id}`}
                                        className={linkClass}
                                    >
                                        {section.title}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </li>
                ))}
            </ul>
        </nav>
    );
}

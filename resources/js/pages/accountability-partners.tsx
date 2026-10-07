import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Reveal from '@/components/landing/reveal';

type Partner = {
    name: string;
    photo: string;
};

export default function AccountabilityPartners({
    partners,
}: {
    partners: Partner[];
}) {
    return (
        <>
            <Head title="Accountability Partners" />
            <div className="landing bg-paper text-ink min-h-screen">
                <div className="mx-auto max-w-7xl px-5 py-10 md:px-8 lg:py-16">
                    <Link
                        href="/"
                        className="text-ink hover:text-primary decoration-primary focus-visible:ring-primary inline-flex items-center gap-2 rounded-sm font-semibold underline underline-offset-4 outline-none focus-visible:ring-[3px]"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        Back to home
                    </Link>
                    <h1 className="font-display text-ink mt-8 max-w-3xl text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl">
                        Meet our accountability partners
                    </h1>
                    <ul className="mt-14 grid grid-cols-2 gap-x-6 gap-y-10 md:grid-cols-3 lg:grid-cols-4">
                        {partners.map((partner, index) => (
                            <li key={partner.name}>
                                <Reveal
                                    delay={
                                        Math.min(index % 4, 3) as 0 | 1 | 2 | 3
                                    }
                                >
                                    <figure className="border-ink/20 border-t pt-4">
                                        <div className="bg-ink/5 aspect-[4/5] overflow-hidden rounded-sm">
                                            <img
                                                src={partner.photo}
                                                alt={partner.name}
                                                loading="lazy"
                                                className="size-full object-cover"
                                            />
                                        </div>
                                        <figcaption className="font-display text-ink mt-4 text-xl leading-snug font-semibold">
                                            {partner.name}
                                        </figcaption>
                                    </figure>
                                </Reveal>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </>
    );
}

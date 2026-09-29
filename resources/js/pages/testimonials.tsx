import { Head, Link } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import Reveal from "@/components/landing/reveal";

type Testimonial = {
    quote: string;
    name: string;
    detail: string;
};

const testimonials: Testimonial[] = [
    {
        quote: "Intimacy and consistency with God’s Word is achievable.",
        name: "Abuoma Ogbuka",
        detail: "Public servant, United Kingdom",
    },
    {
        quote: "My accountability partner was amazing. She was always checking in and encouraging me.",
        name: "Success Emebu",
        detail: "Legal practitioner, Nigeria",
    },
    {
        quote: "Trusting God even when we don’t understand what He is doing is key.",
        name: "Omolara Thomas",
        detail: "Children and education safeguarding business support, United Kingdom",
    },
    {
        quote: "Writing those insights forced me to study deeply and not just read for knowledge.",
        name: "Eno Onen",
        detail: "Medical doctor, Nigeria",
    },
    {
        quote: "One thing that stood out was that God walked with me.",
        name: "Onyekwere Maryclara",
        detail: "Financial technology, Nigeria",
    },
    {
        quote: "I became more discerning and disciplined.",
        name: "Inegbenose Ibafidon",
        detail: "Student, Nigeria",
    },
    {
        quote: "I’ve learned to listen more, pray with greater understanding, and trust God’s leading.",
        name: "Adebowale Isaac",
        detail: "Banking, Nigeria",
    },
    {
        quote: "This experience has made me more aware of the person of God and the essence of His judgement.",
        name: "Deji Durotimi",
        detail: "Project manager, United Kingdom",
    },
    {
        quote: "I’ve grown significantly and feel more grounded in my faith.",
        name: "Blessing Ogunsanwo",
        detail: "Medical practitioner, Nigeria",
    },
    {
        quote: "This experience helped me to be consistent with the word.",
        name: "Omolara Adekunbi",
        detail: "Pharmaceutical marketing, Nigeria",
    },
    {
        quote: "I had clearer dreams, and became more discerning, prayed more.",
        name: "Chidimma Ikemka",
        detail: "Business, Nigeria",
    },
    {
        quote: "It has taught me to be consistent with Bible reading and comprehension.",
        name: "Sunkanmi Ogunsanwo",
        detail: "Medical practitioner, Nigeria",
    },
    {
        quote: "My study of the Word has grown deeply.",
        name: "Mosimiloluwa Oduke",
        detail: "Financial compliance analyst, Canada",
    },
];

export default function Testimonials() {
    return (
        <>
            <Head title="Testimonials" />
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
                        See what past participants have to say
                    </h1>
                    <div className="mt-14 gap-x-12 md:columns-2 lg:columns-3">
                        {testimonials.map((item, index) => (
                            <Reveal
                                key={item.name}
                                delay={Math.min(index % 3, 3) as 0 | 1 | 2 | 3}
                                className="mb-10 break-inside-avoid"
                            >
                                <figure className="border-ink/20 border-t pt-6">
                                    <blockquote className="font-display text-ink text-2xl leading-snug italic">
                                        {item.quote}
                                    </blockquote>
                                    <figcaption className="mt-4 text-sm">
                                        <span className="text-primary font-semibold">
                                            {item.name}
                                        </span>
                                        <span className="text-ink-soft block">
                                            {item.detail}
                                        </span>
                                    </figcaption>
                                </figure>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}

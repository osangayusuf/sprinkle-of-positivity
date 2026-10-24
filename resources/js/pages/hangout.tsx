import { Link } from '@inertiajs/react';
import { ArrowLeft, ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import Seo from '@/components/seo';

type Photo = {
    name: string;
    thumb: string;
    full: string;
};

const PAGE_SIZE = 24;

export default function Hangout({ photos }: { photos: Photo[] }) {
    const [visibleCount, setVisibleCount] = useState(PAGE_SIZE);
    const [activeIndex, setActiveIndex] = useState<number | null>(null);

    const visiblePhotos = photos.slice(0, visibleCount);
    const activePhoto = activeIndex === null ? null : photos[activeIndex];

    const isLightboxOpen = activeIndex !== null;

    useEffect(() => {
        if (!isLightboxOpen) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            const direction =
                event.key === 'ArrowRight'
                    ? 1
                    : event.key === 'ArrowLeft'
                      ? -1
                      : 0;

            if (direction !== 0) {
                setActiveIndex(
                    (current) =>
                        ((current ?? 0) + direction + photos.length) %
                        photos.length,
                );
            }
        };

        window.addEventListener('keydown', onKeyDown);

        return () => window.removeEventListener('keydown', onKeyDown);
    }, [isLightboxOpen, photos.length]);

    const step = (direction: 1 | -1) => {
        setActiveIndex(
            (current) =>
                ((current ?? 0) + direction + photos.length) % photos.length,
        );
    };

    return (
        <>
            <Seo
                title="Hangout photos"
                description="Photos from the Light Shining in the Dark community hangout."
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
                    <h1 className="font-display text-ink mt-8 max-w-3xl text-5xl leading-[0.98] font-semibold tracking-[-0.03em] md:text-6xl">
                        Photos from our hangout
                    </h1>

                    {photos.length === 0 ? (
                        <p className="text-ink/70 mt-14">
                            Photos are coming soon.
                        </p>
                    ) : (
                        <ul className="mt-14 grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4">
                            {visiblePhotos.map((photo, index) => (
                                <li key={photo.name}>
                                    <button
                                        type="button"
                                        onClick={() => setActiveIndex(index)}
                                        className="bg-ink/5 focus-visible:ring-primary block aspect-square w-full overflow-hidden rounded-sm outline-none focus-visible:ring-[3px]"
                                        aria-label={`View photo ${index + 1} of ${photos.length}`}
                                    >
                                        <img
                                            src={photo.thumb}
                                            alt=""
                                            loading="lazy"
                                            decoding="async"
                                            className="size-full object-cover transition-transform duration-300 hover:scale-105"
                                        />
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}

                    {visibleCount < photos.length && (
                        <div className="mt-10 flex justify-center">
                            <button
                                type="button"
                                onClick={() =>
                                    setVisibleCount(
                                        (count) => count + PAGE_SIZE,
                                    )
                                }
                                className="bg-ink text-paper focus-visible:ring-primary rounded-sm px-6 py-3 font-semibold outline-none focus-visible:ring-[3px]"
                            >
                                Load more ({photos.length - visibleCount}{' '}
                                remaining)
                            </button>
                        </div>
                    )}
                </div>
            </div>

            <Dialog
                open={activePhoto !== null}
                onOpenChange={(open) => !open && setActiveIndex(null)}
            >
                <DialogContent className="max-w-[calc(100%-1rem)] gap-0 border-0 bg-black/95 p-2 sm:max-w-5xl">
                    <DialogTitle className="sr-only">Hangout photo</DialogTitle>
                    <DialogDescription className="sr-only">
                        Use the arrow keys to move between photos.
                    </DialogDescription>
                    {activePhoto && (
                        <div className="relative flex items-center justify-center">
                            <img
                                key={activePhoto.full}
                                src={activePhoto.full}
                                alt=""
                                className="max-h-[85vh] w-auto max-w-full rounded-sm object-contain"
                            />
                            <button
                                type="button"
                                onClick={() => step(-1)}
                                aria-label="Previous photo"
                                className="absolute left-2 rounded-full bg-black/60 p-2 text-white hover:bg-black/80"
                            >
                                <ChevronLeft className="size-6" />
                            </button>
                            <button
                                type="button"
                                onClick={() => step(1)}
                                aria-label="Next photo"
                                className="absolute right-2 rounded-full bg-black/60 p-2 text-white hover:bg-black/80"
                            >
                                <ChevronRight className="size-6" />
                            </button>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}

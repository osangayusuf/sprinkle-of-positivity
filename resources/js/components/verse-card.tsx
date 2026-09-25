import type { Verse } from '@/types/models';

export function VerseCard({
    verse,
    label = 'Verse of the day',
}: {
    verse: Verse;
    label?: string;
}) {
    return (
        <div className="relative flex min-h-56 flex-col justify-end overflow-hidden rounded-2xl p-5 text-white">
            {verse.image_url && (
                <img
                    src={verse.image_url}
                    alt=""
                    className="absolute inset-0 size-full object-cover"
                />
            )}
            <div
                className={
                    verse.image_url
                        ? 'absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent'
                        : 'from-heading absolute inset-0 bg-gradient-to-b to-black'
                }
            />
            <div className="relative">
                <p className="text-xs text-white/70">
                    {label} • {verse.reference}
                </p>
                <p className="mt-2 text-lg leading-snug font-semibold">
                    &ldquo;{verse.text}&rdquo;
                </p>
            </div>
        </div>
    );
}

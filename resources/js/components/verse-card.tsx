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
                        : 'absolute inset-0 bg-gradient-to-b from-[oklch(0.213_0.103_269.66)] to-black'
                }
            />
            <div className="relative">
                <p className="text-xs text-white/70">
                    {label}
                    {verse.text && ` • ${verse.reference}`}
                    {verse.text &&
                        verse.translation &&
                        ` (${verse.translation})`}
                </p>
                {verse.text ? (
                    <p className="mt-2 text-lg leading-snug font-semibold">
                        &ldquo;{verse.text}&rdquo;
                    </p>
                ) : (
                    <p className="mt-2 text-2xl leading-snug font-semibold">
                        {verse.reference}
                    </p>
                )}
                {verse.copyright && (
                    <p className="mt-2 text-[10px] text-white/60">
                        {verse.copyright}
                    </p>
                )}
            </div>
        </div>
    );
}

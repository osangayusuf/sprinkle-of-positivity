import { useEffect, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { bibleVerseLookup } from '@/routes';
import { books as booksRoute, versions as versionsRoute } from '@/routes/bible';
import { cn } from '@/lib/utils';

export type VerseFields = {
    reference: string;
    text: string;
    translation: string;
    copyright: string;
};

type Version = { id: number; abbreviation: string; title: string };
type Book = { id: string; title: string; chapters: number[] };

type Lookup = {
    text: string | null;
    reference: string | null;
    translation: string | null;
    copyright: string | null;
    source: 'youversion' | 'fallback' | null;
};

type Notice = { tone: 'info' | 'warning'; message: string };

const selectClassName =
    'border-input bg-background h-10 w-full min-w-0 rounded-md border px-2 text-sm outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50';

/**
 * Catalog responses rarely change, so share one request per URL between
 * every picker on the page (the bulk form renders one per row).
 */
const catalogRequests = new Map<string, Promise<unknown>>();

function fetchCatalog<T>(url: string): Promise<T> {
    let request = catalogRequests.get(url);

    if (!request) {
        request = fetch(url, { headers: { Accept: 'application/json' } })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Catalog request failed');
                }

                return response.json();
            })
            .catch((error) => {
                catalogRequests.delete(url);

                throw error;
            });
        catalogRequests.set(url, request);
    }

    return request as Promise<T>;
}

function range(from: number, to: number): number[] {
    return Array.from(
        { length: Math.max(to - from + 1, 0) },
        (_, i) => from + i,
    );
}

/**
 * Pick a Bible passage from version / book / chapter / verse selects and
 * prefill the verse text. Choosing just a book and chapter is enough: the
 * reference becomes e.g. "Hosea 12" and the verse text stays empty, for
 * readings where members pick their own verses. When the YouVersion catalog
 * can't be loaded it degrades to a free-text reference with the same "Look
 * up" button.
 */
export function VersePicker({
    value,
    onChange,
    names = {
        reference: 'reference',
        translation: 'translation',
        copyright: 'copyright',
    },
    idPrefix = '',
    errors = {},
    fieldClassName,
}: {
    value: VerseFields;
    onChange: (patch: Partial<VerseFields>) => void;
    names?: { reference: string; translation: string; copyright: string };
    idPrefix?: string;
    errors?: { reference?: string };
    fieldClassName?: string;
}) {
    const [versions, setVersions] = useState<Version[] | null>(null);
    const [books, setBooks] = useState<Book[]>([]);
    const [versionId, setVersionId] = useState<number | null>(null);
    const [bookId, setBookId] = useState('');
    const [chapter, setChapter] = useState(0);
    const [verseStart, setVerseStart] = useState(0);
    const [verseEnd, setVerseEnd] = useState(0);
    const [looking, setLooking] = useState(false);
    const [notice, setNotice] = useState<Notice | null>(null);
    const latestRequest = useRef(0);

    useEffect(() => {
        let cancelled = false;

        fetchCatalog<{ versions: Version[]; default_version_id: number }>(
            versionsRoute.url(),
        )
            .then((data) => {
                if (cancelled) {
                    return;
                }

                setVersions(data.versions);
                setVersionId(
                    data.versions.find((v) => v.id === data.default_version_id)
                        ?.id ??
                        data.versions[0]?.id ??
                        null,
                );
            })
            .catch(() => {
                if (!cancelled) {
                    setVersions([]);
                }
            });

        return () => {
            cancelled = true;
        };
    }, []);

    useEffect(() => {
        if (versionId === null) {
            return;
        }

        let cancelled = false;

        fetchCatalog<{ books: Book[] }>(booksRoute.url(versionId))
            .then((data) => {
                if (!cancelled) {
                    setBooks(data.books);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setBooks([]);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [versionId]);

    const book = books.find((b) => b.id === bookId);
    const chapterVerses = book && chapter ? book.chapters[chapter - 1] : 0;
    const pickerAvailable = versions !== null && versions.length > 0;

    async function lookUp(
        query: Record<string, string | number>,
        reference: string,
    ) {
        const requestId = ++latestRequest.current;

        setLooking(true);
        setNotice(null);

        try {
            const response = await fetch(
                bibleVerseLookup.url({ query: { reference, ...query } }),
                { headers: { Accept: 'application/json' } },
            );
            const data: Lookup = await response.json();

            if (requestId !== latestRequest.current) {
                return;
            }

            if (!data.text) {
                onChange({ reference, translation: '', copyright: '' });
                setNotice({
                    tone: 'warning',
                    message:
                        "Couldn't fetch that passage. You can type the verse text in yourself.",
                });

                return;
            }

            onChange({
                reference: data.reference || reference,
                text: data.text,
                translation: data.translation ?? '',
                copyright: data.copyright ?? '',
            });

            if (data.source === 'fallback') {
                setNotice({
                    tone: 'warning',
                    message:
                        "Couldn't use the selected version, so this is a public-domain translation. No translation credit is saved.",
                });
            }
        } catch {
            if (requestId === latestRequest.current) {
                setNotice({
                    tone: 'warning',
                    message:
                        "Couldn't fetch that passage. You can type the verse text in yourself.",
                });
            }
        } finally {
            if (requestId === latestRequest.current) {
                setLooking(false);
            }
        }
    }

    function lookUpSelection(next: {
        bookId: string;
        chapter: number;
        verseStart: number;
        verseEnd: number;
    }) {
        const selected = books.find((b) => b.id === next.bookId);

        if (!selected || !next.chapter || !next.verseStart || !versionId) {
            return;
        }

        const hasRange = next.verseEnd > next.verseStart;
        const reference = `${selected.title} ${next.chapter}:${next.verseStart}${
            hasRange ? `-${next.verseEnd}` : ''
        }`;

        void lookUp(
            {
                version_id: versionId,
                book: selected.id,
                chapter: next.chapter,
                verse_start: next.verseStart,
                ...(hasRange ? { verse_end: next.verseEnd } : {}),
            },
            reference,
        );
    }

    function pickBook(id: string) {
        setBookId(id);
        setChapter(0);
        setVerseStart(0);
        setVerseEnd(0);
    }

    /**
     * A whole chapter is a complete selection on its own: invalidate any
     * in-flight verse lookup and clear the previous passage's text.
     */
    function selectWholeChapter(number: number) {
        latestRequest.current++;
        setLooking(false);
        setNotice(null);

        if (book && number) {
            onChange({
                reference: `${book.title} ${number}`,
                text: '',
                translation: '',
                copyright: '',
            });
        }
    }

    function pickChapter(number: number) {
        setChapter(number);
        setVerseStart(0);
        setVerseEnd(0);
        selectWholeChapter(number);
    }

    function pickVerseStart(number: number) {
        const end = verseEnd >= number ? verseEnd : 0;

        setVerseStart(number);
        setVerseEnd(end);

        if (number === 0) {
            selectWholeChapter(chapter);

            return;
        }

        lookUpSelection({ bookId, chapter, verseStart: number, verseEnd: end });
    }

    function pickVerseEnd(number: number) {
        setVerseEnd(number);
        lookUpSelection({ bookId, chapter, verseStart, verseEnd: number });
    }

    function pickVersion(id: number) {
        setVersionId(id);
        setBooks([]);
        pickBook('');
    }

    return (
        <div className="grid gap-3">
            <input
                type="hidden"
                name={names.translation}
                value={value.translation}
            />
            <input
                type="hidden"
                name={names.copyright}
                value={value.copyright}
            />

            {pickerAvailable && (
                <div className="grid gap-3">
                    <div className="grid gap-2">
                        <Label htmlFor={`${idPrefix}version`}>Version</Label>
                        <select
                            id={`${idPrefix}version`}
                            value={versionId ?? ''}
                            onChange={(e) =>
                                pickVersion(Number(e.target.value))
                            }
                            className={cn(selectClassName, fieldClassName)}
                        >
                            {versions.map((version) => (
                                <option key={version.id} value={version.id}>
                                    {version.abbreviation} — {version.title}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="grid grid-cols-[2fr_1fr] gap-3">
                        <div className="grid gap-2">
                            <Label htmlFor={`${idPrefix}book`}>Book</Label>
                            <select
                                id={`${idPrefix}book`}
                                value={bookId}
                                onChange={(e) => pickBook(e.target.value)}
                                disabled={books.length === 0}
                                className={cn(selectClassName, fieldClassName)}
                            >
                                <option value="">Select a book</option>
                                {books.map((b) => (
                                    <option key={b.id} value={b.id}>
                                        {b.title}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`${idPrefix}chapter`}>
                                Chapter
                            </Label>
                            <select
                                id={`${idPrefix}chapter`}
                                value={chapter || ''}
                                onChange={(e) =>
                                    pickChapter(Number(e.target.value))
                                }
                                disabled={!book}
                                className={cn(selectClassName, fieldClassName)}
                            >
                                <option value="">—</option>
                                {book &&
                                    range(1, book.chapters.length).map((n) => (
                                        <option key={n} value={n}>
                                            {n}
                                        </option>
                                    ))}
                            </select>
                        </div>
                    </div>

                    <p className="text-muted-foreground -mb-1 text-xs">
                        Pick a chapter alone to share the whole chapter, or
                        choose verses below to share a passage.
                    </p>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="grid gap-2">
                            <Label htmlFor={`${idPrefix}verse-start`}>
                                From verse (optional)
                            </Label>
                            <select
                                id={`${idPrefix}verse-start`}
                                value={verseStart || ''}
                                onChange={(e) =>
                                    pickVerseStart(Number(e.target.value))
                                }
                                disabled={!chapter}
                                className={cn(selectClassName, fieldClassName)}
                            >
                                <option value="">—</option>
                                {range(1, chapterVerses).map((n) => (
                                    <option key={n} value={n}>
                                        {n}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`${idPrefix}verse-end`}>
                                To verse (optional)
                            </Label>
                            <select
                                id={`${idPrefix}verse-end`}
                                value={verseEnd || ''}
                                onChange={(e) =>
                                    pickVerseEnd(Number(e.target.value))
                                }
                                disabled={!verseStart}
                                className={cn(selectClassName, fieldClassName)}
                            >
                                <option value="">—</option>
                                {range(verseStart + 1, chapterVerses).map(
                                    (n) => (
                                        <option key={n} value={n}>
                                            {n}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>
                    </div>
                </div>
            )}

            <div className="grid gap-2">
                <Label htmlFor={`${idPrefix}reference`}>
                    {pickerAvailable ? 'Selected passage' : 'Reference'}
                </Label>
                <div className="flex gap-2">
                    <Input
                        id={`${idPrefix}reference`}
                        name={names.reference}
                        value={value.reference}
                        onChange={(e) =>
                            onChange({ reference: e.target.value })
                        }
                        readOnly={pickerAvailable}
                        placeholder={
                            pickerAvailable
                                ? 'Choose a book and chapter'
                                : 'John 3:16'
                        }
                        required
                        className={fieldClassName}
                    />
                    {!pickerAvailable && versions !== null && (
                        <Button
                            type="button"
                            variant="outline"
                            disabled={looking || !value.reference.trim()}
                            onClick={() => lookUp({}, value.reference.trim())}
                            className={cn('shrink-0', fieldClassName)}
                        >
                            {looking && <Spinner />}
                            Look up
                        </Button>
                    )}
                    {looking && pickerAvailable && (
                        <Spinner className="self-center" />
                    )}
                </div>
                <InputError message={errors.reference} />
                {versions !== null && versions.length === 0 && (
                    <p className="text-muted-foreground text-xs">
                        Bible versions are unavailable right now. Type a
                        reference like John 3:16 and use Look up.
                    </p>
                )}
                {notice && (
                    <p
                        className={cn(
                            'text-xs',
                            notice.tone === 'warning'
                                ? 'text-amber-600 dark:text-amber-400'
                                : 'text-muted-foreground',
                        )}
                    >
                        {notice.message}
                    </p>
                )}
            </div>
        </div>
    );
}

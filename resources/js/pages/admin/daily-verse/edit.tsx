import { Form, Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import DailyVerseController from '@/actions/App/Http/Controllers/Admin/DailyVerseController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { bibleVerseLookup } from '@/routes';
import type { Verse } from '@/types/models';

function todayDateString(): string {
    return new Date().toLocaleDateString('en-CA');
}

export default function AdminDailyVerseEdit({
    verse,
    date,
}: {
    verse: Verse | null;
    date: string;
}) {
    const [reference, setReference] = useState(verse?.reference ?? '');
    const [text, setText] = useState(verse?.text ?? '');
    const [looking, setLooking] = useState(false);
    const today = todayDateString();
    const isToday = date === today;

    async function lookUp() {
        if (!reference.trim()) {
            return;
        }

        setLooking(true);

        try {
            const response = await fetch(
                bibleVerseLookup.url({ query: { reference } }),
            );
            const data: { text: string | null } = await response.json();

            if (data.text) {
                setText(data.text);
            }
        } finally {
            setLooking(false);
        }
    }

    function changeDate(newDate: string) {
        if (newDate < today || newDate === date) {
            return;
        }

        router.get(`/admin/daily-verse/${newDate}`);
    }

    return (
        <>
            <Head
                title={
                    isToday ? "Today's global verse" : `Global verse — ${date}`
                }
            />

            <div className="max-w-xl space-y-6">
                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title={isToday ? "Today's verse" : `Verse for ${date}`}
                        description="Shown to every user on the Home feed"
                    />
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/daily-verse/history"
                            className="text-primary text-sm font-semibold"
                        >
                            View history
                        </Link>
                        <Link
                            href="/admin/daily-verse/bulk"
                            className="text-primary text-sm font-semibold"
                        >
                            Set many at once
                        </Link>
                    </div>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="date">Date</Label>
                    <Input
                        id="date"
                        type="date"
                        min={today}
                        value={date}
                        onChange={(e) => changeDate(e.target.value)}
                    />
                </div>

                <Form
                    {...DailyVerseController.update.form({ date })}
                    encType="multipart/form-data"
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="reference">Reference</Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="reference"
                                        name="reference"
                                        value={reference}
                                        onChange={(e) =>
                                            setReference(e.target.value)
                                        }
                                        placeholder="John 3:16"
                                        required
                                    />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={looking}
                                        onClick={lookUp}
                                        className="shrink-0"
                                    >
                                        {looking && <Spinner />}
                                        Look up
                                    </Button>
                                </div>
                                <InputError message={errors.reference} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="text">Verse text</Label>
                                <Textarea
                                    id="text"
                                    name="text"
                                    value={text}
                                    onChange={(e) => setText(e.target.value)}
                                    required
                                    rows={6}
                                />
                                <InputError message={errors.text} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="image">Image (optional)</Label>
                                {verse?.image_url && (
                                    <img
                                        src={verse.image_url}
                                        alt=""
                                        className="h-32 w-full rounded-lg object-cover"
                                    />
                                )}
                                <Input
                                    id="image"
                                    name="image"
                                    type="file"
                                    accept="image/*"
                                />
                                <InputError message={errors.image} />
                            </div>

                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner />}
                                Save
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

AdminDailyVerseEdit.layout = {
    breadcrumbs: [{ title: "Today's verse", href: '/admin/daily-verse' }],
};

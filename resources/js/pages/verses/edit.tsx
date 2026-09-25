import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import GroupVerseController from '@/actions/App/Http/Controllers/GroupVerseController';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { bibleVerseLookup } from '@/routes';
import type { Group, Verse } from '@/types/models';

export default function VerseEdit({
    group,
    verse,
}: {
    group: Group;
    verse: Verse | null;
}) {
    const [reference, setReference] = useState(verse?.reference ?? '');
    const [text, setText] = useState(verse?.text ?? '');
    const [looking, setLooking] = useState(false);

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

    return (
        <>
            <Head title={`Set today's verse — ${group.name}`} />
            <PageHeader
                title="Today's verse"
                backHref={`/groups/${group.slug}/verse`}
            />

            <div className="px-4 py-6">
                <Form
                    {...GroupVerseController.update.form({
                        group: group.slug,
                    })}
                    encType="multipart/form-data"
                    className="flex flex-col gap-6"
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
                                        className="bg-muted h-12 rounded-xl border-transparent px-4"
                                    />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={looking}
                                        onClick={lookUp}
                                        className="h-12 shrink-0 rounded-xl"
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
                                    className="bg-muted rounded-xl border-transparent px-4"
                                />
                                <InputError message={errors.text} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="image">Image (optional)</Label>
                                {verse?.image_url && (
                                    <img
                                        src={verse.image_url}
                                        alt=""
                                        className="h-32 w-full rounded-xl object-cover"
                                    />
                                )}
                                <Input
                                    id="image"
                                    name="image"
                                    type="file"
                                    accept="image/*"
                                    className="bg-muted h-12 rounded-xl border-transparent px-4"
                                />
                                <InputError message={errors.image} />
                            </div>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="h-14 w-full rounded-full text-base"
                            >
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

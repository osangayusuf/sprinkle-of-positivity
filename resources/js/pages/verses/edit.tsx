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
import { VersePicker } from '@/components/verse-picker';
import type { VerseFields } from '@/components/verse-picker';
import type { Group, Verse } from '@/types/models';

export default function VerseEdit({
    group,
    verse,
}: {
    group: Group;
    verse: Verse | null;
}) {
    const [fields, setFields] = useState<VerseFields>({
        reference: verse?.reference ?? '',
        text: verse?.text ?? '',
        translation: verse?.translation ?? '',
        copyright: verse?.copyright ?? '',
    });

    function updateFields(patch: Partial<VerseFields>) {
        setFields((current) => ({ ...current, ...patch }));
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
                            <VersePicker
                                value={fields}
                                onChange={updateFields}
                                errors={errors}
                                fieldClassName="bg-muted h-12 rounded-xl border-transparent px-4"
                            />

                            <div className="grid gap-2">
                                <Label htmlFor="text">Verse text</Label>
                                <Textarea
                                    id="text"
                                    name="text"
                                    value={fields.text}
                                    onChange={(e) =>
                                        updateFields({ text: e.target.value })
                                    }
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

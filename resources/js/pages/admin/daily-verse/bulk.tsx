import { Form, Head } from '@inertiajs/react';
import { Plus, X } from 'lucide-react';
import { useState } from 'react';
import DailyVerseBulkController from '@/actions/App/Http/Controllers/Admin/DailyVerseBulkController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { VersePicker } from '@/components/verse-picker';
import type { VerseFields } from '@/components/verse-picker';

const MIN_ROWS = 1;

type Row = { id: number; fields: VerseFields };

function blankRow(id: number): Row {
    return {
        id,
        fields: { reference: '', text: '', translation: '', copyright: '' },
    };
}

export default function AdminDailyVerseBulk() {
    const [rows, setRows] = useState<Row[]>([blankRow(0)]);

    function updateRow(id: number, patch: Partial<VerseFields>) {
        setRows((current) =>
            current.map((row) =>
                row.id === id
                    ? { ...row, fields: { ...row.fields, ...patch } }
                    : row,
            ),
        );
    }

    return (
        <>
            <Head title="Bulk-set the daily verse" />

            <div className="max-w-2xl space-y-10">
                <Heading
                    variant="small"
                    title="Bulk-set the daily verse"
                    description="Schedule the global Home verse for several days at once"
                />

                <div className="space-y-4">
                    <Heading variant="small" title="Add rows" />

                    <Form
                        {...DailyVerseBulkController.store.form()}
                        encType="multipart/form-data"
                        resetOnSuccess
                        onSuccess={() => setRows([blankRow(0)])}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="space-y-4">
                                    {rows.map(({ id, fields }, index) => (
                                        <div
                                            key={id}
                                            className="grid gap-3 rounded-lg border p-4"
                                        >
                                            <div className="flex items-center justify-between">
                                                <p className="text-muted-foreground text-xs font-medium">
                                                    Row {index + 1}
                                                </p>
                                                {rows.length > MIN_ROWS && (
                                                    <button
                                                        type="button"
                                                        aria-label="Remove row"
                                                        onClick={() =>
                                                            setRows((current) =>
                                                                current.filter(
                                                                    (row) =>
                                                                        row.id !==
                                                                        id,
                                                                ),
                                                            )
                                                        }
                                                        className="text-muted-foreground"
                                                    >
                                                        <X className="size-4" />
                                                    </button>
                                                )}
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor={`date-${id}`}>
                                                    Date
                                                </Label>
                                                <Input
                                                    id={`date-${id}`}
                                                    name={`rows[${id}][date]`}
                                                    type="date"
                                                    required
                                                />
                                            </div>

                                            <VersePicker
                                                value={fields}
                                                onChange={(patch) =>
                                                    updateRow(id, patch)
                                                }
                                                idPrefix={`row-${id}-`}
                                                names={{
                                                    reference: `rows[${id}][reference]`,
                                                    translation: `rows[${id}][translation]`,
                                                    copyright: `rows[${id}][copyright]`,
                                                }}
                                            />

                                            <div className="grid gap-2">
                                                <Label htmlFor={`text-${id}`}>
                                                    Verse text
                                                </Label>
                                                <Textarea
                                                    id={`text-${id}`}
                                                    name={`rows[${id}][text]`}
                                                    value={fields.text}
                                                    onChange={(e) =>
                                                        updateRow(id, {
                                                            text: e.target
                                                                .value,
                                                        })
                                                    }
                                                    rows={3}
                                                    required
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor={`image-${id}`}>
                                                    Image (optional)
                                                </Label>
                                                <Input
                                                    id={`image-${id}`}
                                                    name={`rows[${id}][image]`}
                                                    type="file"
                                                    accept="image/*"
                                                />
                                            </div>
                                        </div>
                                    ))}
                                    <InputError message={errors.rows} />

                                    <button
                                        type="button"
                                        onClick={() =>
                                            setRows((current) => [
                                                ...current,
                                                blankRow(
                                                    (current.at(-1)?.id ?? 0) +
                                                        1,
                                                ),
                                            ])
                                        }
                                        className="border-border text-primary flex w-full items-center justify-center gap-2 rounded-lg border border-dashed py-3 text-sm font-semibold"
                                    >
                                        <Plus className="size-4" />
                                        Add another row
                                    </button>
                                </div>

                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    Save all rows
                                </Button>
                            </>
                        )}
                    </Form>
                </div>

                <div className="space-y-4">
                    <Heading
                        variant="small"
                        title="Or import a CSV"
                        description="Columns: date, reference, text — an optional header row is fine"
                    />

                    <Form
                        {...DailyVerseBulkController.store.form()}
                        encType="multipart/form-data"
                        resetOnSuccess
                        className="flex items-start gap-3"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid flex-1 gap-2">
                                    <Input
                                        id="file"
                                        name="file"
                                        type="file"
                                        accept=".csv,.txt"
                                        required
                                    />
                                    <InputError message={errors.file} />
                                </div>
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    Upload
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}

AdminDailyVerseBulk.layout = {
    breadcrumbs: [
        { title: "Today's verse", href: '/admin/daily-verse' },
        { title: 'Bulk-set', href: '/admin/daily-verse/bulk' },
    ],
};

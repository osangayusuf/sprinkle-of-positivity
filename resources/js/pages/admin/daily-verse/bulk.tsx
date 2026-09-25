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

const MIN_ROWS = 1;

export default function AdminDailyVerseBulk() {
    const [rowIds, setRowIds] = useState([0]);

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
                        onSuccess={() => setRowIds([0])}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="space-y-4">
                                    {rowIds.map((id, index) => (
                                        <div
                                            key={id}
                                            className="grid gap-3 rounded-lg border p-4"
                                        >
                                            <div className="flex items-center justify-between">
                                                <p className="text-muted-foreground text-xs font-medium">
                                                    Row {index + 1}
                                                </p>
                                                {rowIds.length > MIN_ROWS && (
                                                    <button
                                                        type="button"
                                                        aria-label="Remove row"
                                                        onClick={() =>
                                                            setRowIds((ids) =>
                                                                ids.filter(
                                                                    (i) =>
                                                                        i !==
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

                                            <div className="grid grid-cols-2 gap-3">
                                                <div className="grid gap-2">
                                                    <Label
                                                        htmlFor={`date-${id}`}
                                                    >
                                                        Date
                                                    </Label>
                                                    <Input
                                                        id={`date-${id}`}
                                                        name={`rows[${id}][date]`}
                                                        type="date"
                                                        required
                                                    />
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label
                                                        htmlFor={`reference-${id}`}
                                                    >
                                                        Reference
                                                    </Label>
                                                    <Input
                                                        id={`reference-${id}`}
                                                        name={`rows[${id}][reference]`}
                                                        placeholder="John 3:16"
                                                        required
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor={`text-${id}`}>
                                                    Verse text
                                                </Label>
                                                <Textarea
                                                    id={`text-${id}`}
                                                    name={`rows[${id}][text]`}
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
                                            setRowIds((ids) => [
                                                ...ids,
                                                (ids.at(-1) ?? 0) + 1,
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

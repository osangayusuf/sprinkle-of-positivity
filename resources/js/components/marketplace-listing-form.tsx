import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import type { MarketplaceListing } from '@/types/models';
import type { RouteFormDefinition } from '@/wayfinder';

function toDateTimeLocal(value: string | null): string | undefined {
    return value ? value.replace(' ', 'T').slice(0, 16) : undefined;
}

export function MarketplaceListingForm({
    formProps,
    listing,
}: {
    formProps: RouteFormDefinition<'post'>;
    listing?: MarketplaceListing;
}) {
    return (
        <Form
            {...formProps}
            encType="multipart/form-data"
            className="space-y-6"
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="title">Title</Label>
                        <Input
                            id="title"
                            name="title"
                            defaultValue={listing?.title}
                            required
                        />
                        <InputError message={errors.title} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            name="description"
                            defaultValue={listing?.description ?? undefined}
                            rows={3}
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="cta_label">Button label</Label>
                            <Input
                                id="cta_label"
                                name="cta_label"
                                defaultValue={listing?.cta_label ?? undefined}
                                placeholder="Read More"
                            />
                            <InputError message={errors.cta_label} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="cta_url">Button link</Label>
                            <Input
                                id="cta_url"
                                name="cta_url"
                                type="url"
                                defaultValue={listing?.cta_url ?? undefined}
                                placeholder="https://example.com"
                            />
                            <InputError message={errors.cta_url} />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="position">
                            Position (lower shows first)
                        </Label>
                        <Input
                            id="position"
                            name="position"
                            type="number"
                            min={0}
                            defaultValue={listing?.position ?? 0}
                        />
                        <InputError message={errors.position} />
                    </div>

                    {listing && (
                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="starts_at">
                                    Starts (optional)
                                </Label>
                                <Input
                                    id="starts_at"
                                    name="starts_at"
                                    type="datetime-local"
                                    defaultValue={toDateTimeLocal(
                                        listing.starts_at,
                                    )}
                                />
                                <InputError message={errors.starts_at} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="ends_at">Ends (optional)</Label>
                                <Input
                                    id="ends_at"
                                    name="ends_at"
                                    type="datetime-local"
                                    defaultValue={toDateTimeLocal(
                                        listing.ends_at,
                                    )}
                                />
                                <InputError message={errors.ends_at} />
                            </div>
                        </div>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="image">
                            Image
                            {listing &&
                                ' (leave blank to keep the current one)'}
                        </Label>
                        {listing?.image_url && (
                            <img
                                src={listing.image_url}
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

                    {listing && (
                        <div className="grid gap-2">
                            <Label htmlFor="is_active">Status</Label>
                            <select
                                id="is_active"
                                name="is_active"
                                defaultValue={listing.is_active ? '1' : '0'}
                                className="bg-background rounded-md border px-3 py-2 text-sm"
                            >
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <InputError message={errors.is_active} />
                        </div>
                    )}

                    <Button type="submit" disabled={processing}>
                        {processing && <Spinner />}
                        {listing ? 'Save changes' : 'Create listing'}
                    </Button>
                </>
            )}
        </Form>
    );
}

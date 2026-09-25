import { Form, Head, Link } from '@inertiajs/react';
import MarketplaceController from '@/actions/App/Http/Controllers/Admin/MarketplaceController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { MarketplaceListing } from '@/types/models';

export default function AdminMarketplaceIndex({
    listings,
}: {
    listings: MarketplaceListing[];
}) {
    return (
        <>
            <Head title="Marketplace" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Marketplace"
                        description="Manage promotional listings shown to members"
                    />
                    <Button asChild>
                        <Link href="/admin/marketplace/create">
                            New listing
                        </Link>
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full min-w-[640px] text-sm">
                        <thead className="bg-muted text-muted-foreground text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">Title</th>
                                <th className="px-4 py-2 font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {listings.map((listing) => (
                                <tr key={listing.id} className="border-t">
                                    <td className="px-4 py-2">
                                        {listing.title}
                                    </td>
                                    <td className="px-4 py-2">
                                        {listing.is_active
                                            ? 'Active'
                                            : 'Inactive'}
                                    </td>
                                    <td className="px-4 py-2 text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button
                                                asChild
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link
                                                    href={`/admin/marketplace/${listing.id}/edit`}
                                                >
                                                    Edit
                                                </Link>
                                            </Button>
                                            <Form
                                                {...MarketplaceController.destroy.form(
                                                    { listing: listing.id },
                                                )}
                                            >
                                                <Button
                                                    type="submit"
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    Remove
                                                </Button>
                                            </Form>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {listings.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={3}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No listings yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

AdminMarketplaceIndex.layout = {
    breadcrumbs: [{ title: 'Marketplace', href: '/admin/marketplace' }],
};

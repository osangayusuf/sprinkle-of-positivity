import { Head } from '@inertiajs/react';
import MarketplaceController from '@/actions/App/Http/Controllers/Admin/MarketplaceController';
import Heading from '@/components/heading';
import { MarketplaceListingForm } from '@/components/marketplace-listing-form';
import type { MarketplaceListing } from '@/types/models';

export default function AdminMarketplaceEdit({
    listing,
}: {
    listing: MarketplaceListing;
}) {
    return (
        <>
            <Head title={`Edit ${listing.title}`} />

            <div className="max-w-xl space-y-6">
                <Heading
                    variant="small"
                    title="Edit listing"
                    description="Shown in the marketplace and Home's featured carousel"
                />

                <MarketplaceListingForm
                    formProps={MarketplaceController.update.form({
                        listing: listing.id,
                    })}
                    listing={listing}
                />
            </div>
        </>
    );
}

AdminMarketplaceEdit.layout = {
    breadcrumbs: [
        { title: 'Marketplace', href: '/admin/marketplace' },
        { title: 'Edit listing', href: '#' },
    ],
};

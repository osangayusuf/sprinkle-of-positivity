import { Head } from '@inertiajs/react';
import MarketplaceController from '@/actions/App/Http/Controllers/Admin/MarketplaceController';
import Heading from '@/components/heading';
import { MarketplaceListingForm } from '@/components/marketplace-listing-form';

export default function AdminMarketplaceCreate() {
    return (
        <>
            <Head title="New listing" />

            <div className="max-w-xl space-y-6">
                <Heading
                    variant="small"
                    title="New listing"
                    description="Shown in the marketplace and Home's featured carousel"
                />

                <MarketplaceListingForm
                    formProps={MarketplaceController.store.form()}
                />
            </div>
        </>
    );
}

AdminMarketplaceCreate.layout = {
    breadcrumbs: [
        { title: 'Marketplace', href: '/admin/marketplace' },
        { title: 'New listing', href: '/admin/marketplace/create' },
    ],
};

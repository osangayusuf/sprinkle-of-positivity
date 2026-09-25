import { Head } from '@inertiajs/react';
import { MarketplaceBannerCard } from '@/components/marketplace-banner-card';
import { PageHeader } from '@/components/page-header';
import type { MarketplaceListing } from '@/types/models';

export default function MarketplaceIndex({
    listings,
}: {
    listings: MarketplaceListing[];
}) {
    return (
        <>
            <Head title="Marketplace" />
            <PageHeader title="Marketplace" backHref="/home" />

            <div className="flex flex-col gap-4 px-4 py-4">
                {listings.map((listing) => (
                    <MarketplaceBannerCard key={listing.id} listing={listing} />
                ))}

                {listings.length === 0 && (
                    <p className="text-muted-foreground py-10 text-center text-sm">
                        No offers available right now.
                    </p>
                )}
            </div>
        </>
    );
}

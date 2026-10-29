import { Head } from "@inertiajs/react";
import { useState } from "react";
import { MarketplaceBannerCard } from "@/components/marketplace-banner-card";
import { PageHeader } from "@/components/page-header";
import { SearchInput } from "@/components/search-input";
import { matchesSearch } from "@/lib/search";
import type { MarketplaceListing } from "@/types/models";

export default function MarketplaceIndex({
    listings,
}: {
    listings: MarketplaceListing[];
}) {
    const [search, setSearch] = useState("");
    const filteredListings = listings.filter((listing) =>
        matchesSearch(search, listing.title, listing.description),
    );

    return (
        <>
            <Head title="Marketplace" />
            <PageHeader title="Marketplace" backHref="/home" />

            <div className="flex flex-col gap-4 px-4 py-4">
                <SearchInput
                    value={search}
                    onChange={setSearch}
                    placeholder="Search offers"
                />

                {filteredListings.map((listing) => (
                    <MarketplaceBannerCard key={listing.id} listing={listing} />
                ))}

                {filteredListings.length === 0 && (
                    <p className="text-muted-foreground py-10 text-center text-sm">
                        {listings.length === 0
                            ? "No offers available right now."
                            : "No offers match your search."}
                    </p>
                )}
            </div>
        </>
    );
}

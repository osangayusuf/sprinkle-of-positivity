import { Button } from '@/components/ui/button';
import type { MarketplaceListing } from '@/types/models';

export function MarketplaceBannerCard({
    listing,
}: {
    listing: MarketplaceListing;
}) {
    return (
        <div className="bg-muted w-full overflow-hidden rounded-2xl border">
            {listing.image_url && (
                <img
                    src={listing.image_url}
                    alt=""
                    className="h-40 w-full object-cover"
                />
            )}

            <div className="flex flex-col gap-3 p-4">
                <div>
                    <p className="text-base font-bold">{listing.title}</p>
                    {listing.description && (
                        <p className="text-muted-foreground mt-1 line-clamp-2 text-sm">
                            {listing.description}
                        </p>
                    )}
                </div>

                {listing.cta_label && listing.cta_url && (
                    <Button
                        asChild
                        size="sm"
                        className="w-fit rounded-full px-5"
                    >
                        <a
                            href={listing.cta_url}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {listing.cta_label}
                        </a>
                    </Button>
                )}
            </div>
        </div>
    );
}

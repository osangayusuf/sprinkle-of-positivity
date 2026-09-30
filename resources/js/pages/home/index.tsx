import { Head, Link, usePage } from '@inertiajs/react';
import { Bell, BookOpenText, ShieldCheck } from 'lucide-react';
import { GroupCard } from '@/components/group-card';
import { MarketplaceBannerCard } from '@/components/marketplace-banner-card';
import { Button } from '@/components/ui/button';
import { VerseCard } from '@/components/verse-card';
import { login, register } from '@/routes';
import type { Auth } from '@/types/auth';
import type { Group, MarketplaceListing, Verse } from '@/types/models';

function timeOfDayGreeting(): string {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    if (hour < 17) {
        return 'Good afternoon';
    }

    return 'Good evening';
}

export default function HomeIndex({
    verse,
    yourGroups,
    featuredListings,
}: {
    verse: Verse | null;
    yourGroups: Group[];
    featuredListings: MarketplaceListing[];
}) {
    const { auth, unreadNotificationsCount } = usePage<{
        auth: Auth;
        unreadNotificationsCount: number;
    }>().props;
    const firstName = auth.user?.name.split(' ')[0];

    return (
        <>
            <Head title="Home" />

            <header className="flex items-center justify-between px-4 pt-6 pb-4">
                <div>
                    <p className="text-lg font-semibold">
                        {firstName
                            ? `${timeOfDayGreeting()}, ${firstName}`
                            : 'Welcome'}
                    </p>
                    <p className="text-muted-foreground text-sm">
                        Keep meditating on the word of God.
                    </p>
                </div>
                {auth.user ? (
                    <div className="flex items-center gap-2">
                        {auth.isAdmin && (
                            <Link
                                href="/admin/dashboard"
                                className="bg-muted text-foreground flex size-10 items-center justify-center rounded-full"
                                aria-label="Go to admin portal"
                            >
                                <ShieldCheck className="size-5" />
                            </Link>
                        )}
                        <Link
                            href="/notifications"
                            className="bg-muted text-foreground relative flex size-10 items-center justify-center rounded-full"
                        >
                            <Bell className="size-5" />
                            {unreadNotificationsCount > 0 && (
                                <span className="bg-primary absolute top-1.5 right-1.5 size-2 rounded-full" />
                            )}
                        </Link>
                    </div>
                ) : (
                    <div className="flex items-center gap-2">
                        <Button
                            asChild
                            variant="ghost"
                            className="rounded-full"
                        >
                            <Link href={login()}>Log in</Link>
                        </Button>
                        <Button asChild className="rounded-full">
                            <Link href={register()}>Join</Link>
                        </Button>
                    </div>
                )}
            </header>

            <div className="px-4">
                {verse ? (
                    <VerseCard verse={verse} />
                ) : (
                    <div className="flex flex-col items-center gap-4 px-2 py-20 text-center">
                        <span className="bg-primary-tint text-primary flex size-16 items-center justify-center rounded-full">
                            <BookOpenText className="size-8" />
                        </span>
                        <h1 className="text-heading text-xl font-bold">
                            Your daily verse is almost here
                        </h1>
                        <p className="text-muted-foreground max-w-xs text-sm">
                            We&apos;re putting the finishing touches on groups,
                            daily verses, and insights. Check back soon.
                        </p>
                    </div>
                )}
            </div>

            {yourGroups.length > 0 && (
                <div className="mt-8 px-4">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-base font-semibold">Your Groups</h2>
                        <Link
                            href="/groups"
                            className="text-primary text-sm font-semibold"
                        >
                            View all
                        </Link>
                    </div>

                    <div className="flex flex-col gap-3">
                        {yourGroups.map((group) => (
                            <GroupCard
                                key={group.id}
                                group={group}
                                membershipState="approved"
                            />
                        ))}
                    </div>
                </div>
            )}

            {featuredListings.length > 0 && (
                <div className="mt-8 px-4">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-base font-semibold">
                            Featured Market Place
                        </h2>
                        <Link
                            href="/marketplace"
                            className="text-primary text-sm font-semibold"
                        >
                            See all
                        </Link>
                    </div>

                    <div className="flex snap-x gap-3 overflow-x-auto pb-2">
                        {featuredListings.map((listing) => (
                            <div
                                key={listing.id}
                                className="w-72 shrink-0 snap-start"
                            >
                                <MarketplaceBannerCard listing={listing} />
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </>
    );
}

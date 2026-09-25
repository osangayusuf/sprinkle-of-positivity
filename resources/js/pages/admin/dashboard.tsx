import { Head, Link } from '@inertiajs/react';
import {
    BookMarked,
    Megaphone,
    ShoppingBag,
    Users as UsersIcon,
} from 'lucide-react';
import Heading from '@/components/heading';

type DashboardCard = {
    title: string;
    description: string;
    href: string;
    icon: typeof UsersIcon;
    count?: number;
};

export default function AdminDashboard({
    counts,
}: {
    counts: { groups: number; users: number; activeListings: number };
}) {
    const cards: DashboardCard[] = [
        {
            title: 'Groups',
            description: 'Create and manage Bible study groups',
            href: '/admin/groups',
            icon: UsersIcon,
            count: counts.groups,
        },
        {
            title: "Today's verse",
            description: 'Set the global daily verse, or set many at once',
            href: '/admin/daily-verse',
            icon: BookMarked,
        },
        {
            title: 'Send announcement',
            description: 'Broadcast a notification to every member',
            href: '/admin/announcements/create',
            icon: Megaphone,
        },
        {
            title: 'Marketplace',
            description: 'Manage promotional listings shown to members',
            href: '/admin/marketplace',
            icon: ShoppingBag,
            count: counts.activeListings,
        },
        {
            title: 'Users',
            description: 'View members and manage admin access',
            href: '/admin/users',
            icon: UsersIcon,
            count: counts.users,
        },
    ];

    return (
        <>
            <Head title="Admin dashboard" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Dashboard"
                    description="Everything you can manage from here"
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {cards.map((card) => (
                        <Link
                            key={card.href}
                            href={card.href}
                            className="hover:border-primary flex flex-col gap-3 rounded-lg border p-4 transition-colors"
                        >
                            <div className="flex items-center justify-between">
                                <card.icon className="text-primary size-5" />
                                {card.count !== undefined && (
                                    <span className="text-muted-foreground text-xs font-semibold tabular-nums">
                                        {card.count}
                                    </span>
                                )}
                            </div>
                            <div>
                                <p className="text-sm font-semibold">
                                    {card.title}
                                </p>
                                <p className="text-muted-foreground text-xs">
                                    {card.description}
                                </p>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: '/admin/dashboard' }],
};

import { Link } from '@inertiajs/react';
import {
    BookMarked,
    ChartColumn,
    LayoutDashboard,
    Megaphone,
    ShoppingBag,
    Users,
    UsersRound,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Analytics',
        href: '/admin/analytics',
        icon: ChartColumn,
    },
    {
        title: 'Groups',
        href: '/admin/groups',
        icon: Users,
    },
    {
        title: "Today's verse",
        href: '/admin/daily-verse',
        icon: BookMarked,
    },
    {
        title: 'Send announcement',
        href: '/admin/announcements/create',
        icon: Megaphone,
    },
    {
        title: 'Marketplace',
        href: '/admin/marketplace',
        icon: ShoppingBag,
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: UsersRound,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/home" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

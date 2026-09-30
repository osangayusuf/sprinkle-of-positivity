import { House, LogIn, Store, Trophy, User, Users } from 'lucide-react';
import type { NavItem } from '@/types/navigation';

/**
 * The four primary destinations surfaced in the mobile bottom tab bar.
 * Centralized here so every consumer of the mobile shell stays in sync.
 */
export const primaryNavItems: NavItem[] = [
    { title: 'Home', href: '/home', icon: House },
    { title: 'Leaderboard', href: '/leaderboard', icon: Trophy },
    { title: 'Groups', href: '/groups', icon: Users },
    { title: 'Profile', href: '/profile', icon: User },
];

/**
 * The bottom tab bar for guests browsing the open pages. The leaderboard
 * and profile need an account, so they're swapped for the marketplace and
 * a way to log in.
 */
export const guestNavItems: NavItem[] = [
    { title: 'Home', href: '/home', icon: House },
    { title: 'Groups', href: '/groups', icon: Users },
    { title: 'Market', href: '/marketplace', icon: Store },
    { title: 'Log in', href: '/login', icon: LogIn },
];

import { House, Trophy, User, Users } from 'lucide-react';
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

import { createInertiaApp } from '@inertiajs/react';
import { registerSW } from 'virtual:pwa-register';
import { CelebrationModal } from '@/components/celebration-modal';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import MobileShellLayout from '@/layouts/mobile-shell-layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const MOBILE_SHELL_PAGE_PREFIXES = [
    'home',
    'groups/',
    'verses/',
    'insights/',
    'leaderboard/',
    'notifications/',
    'profile',
    'marketplace/',
    'settings/',
    'quizzes/',
    'certificates/',
];

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'landing':
            case name === 'certificates/show':
            case name.startsWith('onboarding/'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('admin/'):
                return AppLayout;
            case MOBILE_SHELL_PAGE_PREFIXES.some((prefix) =>
                name.startsWith(prefix),
            ):
                return MobileShellLayout;
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
                <CelebrationModal />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();

// Keep the installed PWA's service worker current.
registerSW({ immediate: true });

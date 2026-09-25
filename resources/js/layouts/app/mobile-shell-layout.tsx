import type { ReactNode } from 'react';
import { BottomNav } from '@/components/bottom-nav';

export default function MobileShellLayout({
    children,
}: {
    children: ReactNode;
}) {
    return (
        <div className="bg-background mx-auto flex min-h-svh max-w-md flex-col">
            <main className="flex-1 pb-20">{children}</main>
            <BottomNav />
        </div>
    );
}

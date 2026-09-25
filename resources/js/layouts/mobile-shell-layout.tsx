import MobileShellLayoutTemplate from '@/layouts/app/mobile-shell-layout';

export default function MobileShellLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return <MobileShellLayoutTemplate>{children}</MobileShellLayoutTemplate>;
}

import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import { SettingsHeader } from '@/components/settings-tabs';

export default function Appearance() {
    return (
        <>
            <Head title="Appearance settings" />
            <SettingsHeader />

            <div className="flex flex-col gap-4 px-4 py-6">
                <Heading
                    variant="small"
                    title="Appearance"
                    description="Choose how Sprinkle of Positivity looks on this device"
                />
                <AppearanceTabs />
            </div>
        </>
    );
}

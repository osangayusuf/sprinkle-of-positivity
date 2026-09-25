import { router } from '@inertiajs/react';
import { PartyPopper } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';

type Celebration = {
    type: 'level_up' | 'achievement';
    title: string;
    body: string;
};

/**
 * Listens for a `celebration` flash prop (set by AwardPoints on a level-up)
 * and shows a full-screen confetti moment — matches the Figma "Level Up" /
 * "Achievement" screens. Mounted once, globally, in app.tsx.
 */
export function CelebrationModal() {
    const [celebration, setCelebration] = useState<Celebration | null>(null);

    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data = flash?.celebration as Celebration | undefined;

            if (data) {
                setCelebration(data);
            }
        });
    }, []);

    return (
        <Dialog
            open={celebration !== null}
            onOpenChange={(open) => !open && setCelebration(null)}
        >
            <DialogContent className="max-w-xs text-center">
                {celebration && (
                    <>
                        <DialogTitle className="text-lg font-bold">
                            {celebration.type === 'level_up'
                                ? 'Level Up'
                                : 'Achievement'}
                        </DialogTitle>

                        <div className="flex flex-col items-center gap-4 py-4">
                            <span className="bg-primary-tint flex size-24 items-center justify-center rounded-full">
                                <PartyPopper className="text-primary size-12" />
                            </span>

                            <div>
                                <p className="text-base font-semibold">
                                    {celebration.type === 'level_up'
                                        ? 'New Level Unlocked!'
                                        : celebration.title}
                                </p>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    {celebration.body}
                                </p>
                            </div>

                            <Button
                                onClick={() => setCelebration(null)}
                                className="h-12 w-full rounded-full"
                            >
                                Continue
                            </Button>
                        </div>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

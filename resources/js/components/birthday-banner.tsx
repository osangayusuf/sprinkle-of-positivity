import { router } from "@inertiajs/react";
import { Check, Gift } from "lucide-react";
import { UserAvatar } from "@/components/user-avatar";
import { Button } from "@/components/ui/button";
import type { Birthday } from "@/types/models";

export function BirthdayBanner({ birthday }: { birthday: Birthday }) {
    const headline = birthday.is_you
        ? `Happy Birthday, ${birthday.name.split(" ")[0]}!`
        : `Today is ${birthday.name}’s Birthday!`;

    return (
        <section
            aria-label={`${birthday.name}'s birthday`}
            className="bg-primary text-primary-foreground relative mb-3 flex items-center gap-4 overflow-hidden rounded-3xl p-5"
        >
            <UserAvatar
                name={birthday.name}
                src={birthday.avatar}
                className="ring-primary-foreground/30 size-20 shrink-0 ring-4"
            />
            <div className="min-w-0">
                <h2 className="text-lg leading-snug font-bold">{headline}</h2>
                <p className="mt-1 text-sm">{birthday.date} 🎉</p>
                {!birthday.is_you && (
                    <Button
                        type="button"
                        size="sm"
                        variant="secondary"
                        disabled={birthday.wished}
                        onClick={() =>
                            router.post(
                                `/birthday-wishes/${birthday.id}`,
                                {},
                                { preserveScroll: true },
                            )
                        }
                        className="mt-3 rounded-full"
                    >
                        {birthday.wished ? (
                            <>
                                <Check /> Wish sent
                            </>
                        ) : (
                            <>
                                <Gift /> Send wish
                            </>
                        )}
                    </Button>
                )}
            </div>
        </section>
    );
}

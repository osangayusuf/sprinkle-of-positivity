import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';

export function UserAvatar({
    name,
    className,
}: {
    name: string;
    className?: string;
}) {
    const getInitials = useInitials();

    return (
        <Avatar className={cn('size-10', className)}>
            <AvatarFallback className="bg-primary-tint text-primary-tint-foreground font-semibold">
                {getInitials(name)}
            </AvatarFallback>
        </Avatar>
    );
}

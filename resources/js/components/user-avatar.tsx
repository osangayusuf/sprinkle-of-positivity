import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';

export function UserAvatar({
    name,
    src,
    className,
}: {
    name: string;
    src?: string | null;
    className?: string;
}) {
    const getInitials = useInitials();

    return (
        <Avatar className={cn('size-10', className)}>
            {src && (
                <AvatarImage src={src} alt={name} className="object-cover" />
            )}
            <AvatarFallback className="bg-primary-tint text-primary-tint-foreground font-semibold">
                {getInitials(name)}
            </AvatarFallback>
        </Avatar>
    );
}

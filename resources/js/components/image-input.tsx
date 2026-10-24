import { useState } from 'react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { imageFileError, MAX_IMAGE_MB } from '@/lib/upload';

/**
 * A file input for images that states the size limit up front and rejects
 * oversized or non-image files before they are uploaded.
 */
export function ImageInput({
    maxMb = MAX_IMAGE_MB,
    onChange,
    'aria-describedby': describedBy,
    ...props
}: Omit<React.ComponentProps<typeof Input>, 'type' | 'accept'> & {
    maxMb?: number;
}) {
    const [error, setError] = useState<string>();
    const hintId = `${props.id ?? props.name}-hint`;

    function handleChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.currentTarget.files?.[0];
        const message = file ? imageFileError(file, maxMb) : null;

        if (message) {
            event.currentTarget.value = '';
        }

        setError(message ?? undefined);
        onChange?.(event);
    }

    return (
        <>
            <Input
                {...props}
                type="file"
                accept="image/*"
                onChange={handleChange}
                aria-invalid={error ? true : undefined}
                aria-describedby={[describedBy, hintId]
                    .filter(Boolean)
                    .join(' ')}
            />
            <p id={hintId} className="text-muted-foreground text-xs">
                Maximum file size: {maxMb} MB
            </p>
            <InputError message={error} role="alert" />
        </>
    );
}

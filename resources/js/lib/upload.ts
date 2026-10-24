export const MAX_IMAGE_MB = 2;
export const MAX_INSIGHT_IMAGE_MB = 5;

/**
 * Returns a friendly message when `file` is over the size limit or is not an
 * image, otherwise null.
 */
export function imageFileError(file: File, maxMb: number): string | null {
    if (!file.type.startsWith('image/')) {
        return 'Please choose an image file (JPG, PNG, GIF or WebP).';
    }

    if (file.size > maxMb * 1024 * 1024) {
        const sizeMb = (file.size / (1024 * 1024)).toFixed(1);

        return `That image is ${sizeMb} MB, which is over the ${maxMb} MB limit. Please choose a smaller one.`;
    }

    return null;
}

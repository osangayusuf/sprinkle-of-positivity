import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ChevronLeft, Image as ImageIcon, X } from 'lucide-react';
import { useRef, useState } from 'react';
import InsightController from '@/actions/App/Http/Controllers/InsightController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { UserAvatar } from '@/components/user-avatar';
import { imageFileError, MAX_INSIGHT_IMAGE_MB } from '@/lib/upload';
import type { AuthenticatedAuth } from '@/types/auth';
import type { Group } from '@/types/models';

const MAX_BODY_LENGTH = 5000;

export default function InsightCreate({
    group,
    verse,
}: {
    group: Group;
    verse: { id: number; reference: string };
}) {
    const { auth } = usePage<{ auth: AuthenticatedAuth }>().props;
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [imagePreview, setImagePreview] = useState<string | null>(null);
    const [imageError, setImageError] = useState<string | null>(null);
    const [body, setBody] = useState('');

    function onImageChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.target.files?.[0];
        const message = file
            ? imageFileError(file, MAX_INSIGHT_IMAGE_MB)
            : null;

        setImageError(message);

        if (!file || message) {
            clearImage();

            return;
        }

        setImagePreview(URL.createObjectURL(file));
    }

    function clearImage() {
        setImagePreview(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    }

    return (
        <>
            <Head title={`Share your reflection on ${verse.reference}`} />

            <div className="bg-background sticky top-0 z-20 flex h-14 items-center px-4">
                <Link href={`/groups/${group.slug}/verse`} aria-label="Go back">
                    <ChevronLeft className="size-6" />
                </Link>
            </div>

            <div className="flex flex-col gap-6 px-4 pb-6">
                <div className="flex items-center gap-3">
                    <UserAvatar name={auth.user.name} src={auth.user.avatar} />
                    <div>
                        <p className="text-base font-bold">
                            Share Your Reflection on {verse.reference}
                        </p>
                        <span className="bg-muted text-muted-foreground mt-1 inline-block rounded-full px-3 py-1 text-xs">
                            {auth.user.name}
                        </span>
                    </div>
                </div>

                <Form
                    {...InsightController.store.form({ group: group.slug })}
                    className="flex flex-col gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <Textarea
                                name="body"
                                required
                                rows={8}
                                value={body}
                                onChange={(event) =>
                                    setBody(event.target.value)
                                }
                                maxLength={MAX_BODY_LENGTH}
                                placeholder="Write your insight here..."
                                className="resize-none border-none px-0 shadow-none focus-visible:ring-0"
                            />
                            <p
                                className={`text-xs ${body.length >= MAX_BODY_LENGTH ? 'text-red-600 dark:text-red-400' : 'text-muted-foreground'}`}
                                aria-live="polite"
                            >
                                {body.length.toLocaleString()} /{' '}
                                {MAX_BODY_LENGTH.toLocaleString()} characters
                            </p>
                            <InputError message={errors.body} />

                            {imagePreview && (
                                <div className="relative">
                                    <img
                                        src={imagePreview}
                                        alt=""
                                        className="max-h-72 w-full rounded-xl object-cover"
                                    />
                                    <button
                                        type="button"
                                        onClick={clearImage}
                                        aria-label="Remove image"
                                        className="absolute top-2 right-2 flex size-7 items-center justify-center rounded-full bg-black/60 text-white"
                                    >
                                        <X className="size-4" />
                                    </button>
                                </div>
                            )}
                            <InputError
                                message={imageError ?? errors.image}
                                role="alert"
                            />

                            <input
                                ref={fileInputRef}
                                type="file"
                                name="image"
                                accept="image/*"
                                onChange={onImageChange}
                                className="hidden"
                                id="insight-image"
                            />

                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <label
                                        htmlFor="insight-image"
                                        className="bg-muted flex size-11 cursor-pointer items-center justify-center rounded-xl"
                                    >
                                        <ImageIcon className="text-muted-foreground size-5" />
                                        <span className="sr-only">
                                            Add an image
                                        </span>
                                    </label>
                                    <span className="text-muted-foreground text-xs">
                                        Max image size: {MAX_INSIGHT_IMAGE_MB}{' '}
                                        MB
                                    </span>
                                </div>

                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="h-11 rounded-full px-8"
                                >
                                    {processing && <Spinner />}
                                    Submit
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

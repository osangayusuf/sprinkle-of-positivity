import { Form, Head } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import GroupApplicationController from '@/actions/App/Http/Controllers/GroupApplicationController';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { Group } from '@/types/models';

const COMMITMENTS = [
    'I commit to sticking to the stipulated submission guidelines for each group activity.',
    'I commit to studying my bible and submitting my study insights daily.',
    'I commit to ensuring that I communicate to the group or to the convener privately when I am unable to meet submission deadlines for group activity.',
    'I consent to being courteous and respectful of the opinions of other group members.',
    'I understand that not meeting the code of conduct listed above may lead to my removal from the group.',
];

export default function GroupJoin({ group }: { group: Group }) {
    const [checked, setChecked] = useState<boolean[]>(
        COMMITMENTS.map(() => false),
    );
    const allChecked = checked.every(Boolean);

    return (
        <>
            <Head title={`Join ${group.name}`} />

            <Form
                {...GroupApplicationController.store.form({
                    group: group.slug,
                })}
                className="flex flex-col px-6 pt-10 pb-10"
            >
                {({ processing }) => (
                    <>
                        <span className="bg-primary-tint mx-auto flex size-16 items-center justify-center rounded-full">
                            <ShieldCheck className="text-primary size-8" />
                        </span>

                        <div className="mt-8 flex flex-col gap-6">
                            {COMMITMENTS.map((commitment, index) => (
                                <label
                                    key={commitment}
                                    className="flex items-start gap-3"
                                >
                                    <Checkbox
                                        checked={checked[index]}
                                        onCheckedChange={(value) =>
                                            setChecked((current) =>
                                                current.map((c, i) =>
                                                    i === index
                                                        ? value === true
                                                        : c,
                                                ),
                                            )
                                        }
                                        className="mt-0.5"
                                    />
                                    <Label className="text-sm leading-relaxed font-normal">
                                        {commitment}
                                    </Label>
                                </label>
                            ))}
                        </div>

                        <input
                            type="hidden"
                            name="code_of_conduct_agreed"
                            value={allChecked ? '1' : ''}
                        />

                        <Button
                            type="submit"
                            disabled={!allChecked || processing}
                            className="mt-8 h-14 w-full rounded-full text-base"
                        >
                            {processing && <Spinner />}
                            Continue
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}

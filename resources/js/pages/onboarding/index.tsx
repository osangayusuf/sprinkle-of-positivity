import { Form, Head } from '@inertiajs/react';
import { Check, ChevronLeft } from 'lucide-react';
import { useEffect, useState } from 'react';
import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

const STEPS = ['name', 'whatsapp', 'goals', 'birthday'] as const;

type Step = (typeof STEPS)[number];

const GOAL_OPTIONS = [
    'Know the Bible better',
    'Get closer to God',
    'Grow in community',
    'Build a daily habit',
    'Deepen my faith',
];

const MONTHS = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
];

const STEP_FIELDS: Record<Step, string[]> = {
    name: ['name'],
    whatsapp: ['whatsapp_number'],
    goals: ['goals'],
    birthday: ['birthday_day', 'birthday_month'],
};

export default function OnboardingIndex({ name }: { name: string }) {
    const [step, setStep] = useState<Step>('name');
    const [selectedGoals, setSelectedGoals] = useState<string[]>([]);
    const [birthdayDay, setBirthdayDay] = useState<string>('');
    const [birthdayMonth, setBirthdayMonth] = useState<string>('');

    const stepIndex = STEPS.indexOf(step);

    function toggleGoal(goal: string) {
        setSelectedGoals((current) =>
            current.includes(goal)
                ? current.filter((g) => g !== goal)
                : [...current, goal],
        );
    }

    return (
        <>
            <Head title="Complete your profile" />

            <Form
                {...OnboardingController.update.form()}
                className="flex min-h-svh flex-col px-6 pt-6 pb-10"
            >
                {({ processing, errors }) => {
                    // Jump back to the earliest step with a validation error
                    // so it's never hidden from the user.
                    useEffect(() => {
                        const erroredStep = STEPS.find((s) =>
                            STEP_FIELDS[s].some((field) => field in errors),
                        );

                        if (erroredStep) {
                            setStep(erroredStep);
                        }
                        // eslint-disable-next-line react-hooks/exhaustive-deps
                    }, [errors]);

                    return (
                        <>
                            <div className="mb-8 h-6">
                                {stepIndex > 0 && (
                                    <button
                                        type="button"
                                        aria-label="Go back"
                                        onClick={() =>
                                            setStep(STEPS[stepIndex - 1])
                                        }
                                    >
                                        <ChevronLeft className="size-6" />
                                    </button>
                                )}
                            </div>

                            <div className={cn(step !== 'name' && 'hidden')}>
                                <h1 className="text-heading text-2xl font-bold">
                                    Tell us your name
                                </h1>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    Enter your full name
                                </p>

                                <div className="mt-8 grid gap-2">
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={name}
                                        placeholder="Full Name"
                                        required
                                        className="bg-muted h-14 rounded-xl border-transparent px-4"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <Button
                                    type="button"
                                    className="mt-8 h-14 w-full rounded-full text-base"
                                    onClick={() => setStep('whatsapp')}
                                >
                                    Continue
                                </Button>
                            </div>

                            <div
                                className={cn(step !== 'whatsapp' && 'hidden')}
                            >
                                <h1 className="text-heading text-2xl font-bold">
                                    Share your WhatsApp number
                                </h1>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    Enter your WhatsApp phone number
                                </p>

                                <div className="mt-8 grid gap-2">
                                    <Label
                                        htmlFor="whatsapp_number"
                                        className="sr-only"
                                    >
                                        WhatsApp number
                                    </Label>
                                    <Input
                                        id="whatsapp_number"
                                        name="whatsapp_number"
                                        type="tel"
                                        placeholder="Phone number"
                                        required
                                        className="bg-muted h-14 rounded-xl border-transparent px-4"
                                    />
                                    <InputError
                                        message={errors.whatsapp_number}
                                    />
                                </div>

                                <Button
                                    type="button"
                                    className="mt-8 h-14 w-full rounded-full text-base"
                                    onClick={() => setStep('goals')}
                                >
                                    Continue
                                </Button>
                            </div>

                            <div className={cn(step !== 'goals' && 'hidden')}>
                                <h1 className="text-heading text-2xl font-bold">
                                    What are your goals for joining the Bible
                                    study group?
                                </h1>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    Select as many as possible
                                </p>

                                <div className="mt-8 flex flex-col gap-3">
                                    {GOAL_OPTIONS.map((goal) => {
                                        const selected =
                                            selectedGoals.includes(goal);

                                        return (
                                            <button
                                                key={goal}
                                                type="button"
                                                onClick={() => toggleGoal(goal)}
                                                className={cn(
                                                    'flex h-14 items-center justify-between rounded-full px-5 text-left text-sm font-semibold',
                                                    selected
                                                        ? 'bg-primary-tint text-primary-tint-foreground'
                                                        : 'bg-muted text-foreground',
                                                )}
                                            >
                                                {goal}
                                                {selected && (
                                                    <span className="bg-primary text-primary-foreground flex size-5 items-center justify-center rounded-full">
                                                        <Check className="size-3.5" />
                                                    </span>
                                                )}
                                            </button>
                                        );
                                    })}
                                </div>
                                {selectedGoals.map((goal) => (
                                    <input
                                        key={goal}
                                        type="hidden"
                                        name="goals[]"
                                        value={goal}
                                    />
                                ))}
                                <InputError message={errors.goals} />

                                <Button
                                    type="button"
                                    disabled={selectedGoals.length === 0}
                                    className="mt-8 h-14 w-full rounded-full text-base"
                                    onClick={() => setStep('birthday')}
                                >
                                    Continue
                                </Button>
                            </div>

                            <div
                                className={cn(step !== 'birthday' && 'hidden')}
                            >
                                <h1 className="text-heading text-2xl font-bold">
                                    When is your birthday?
                                </h1>

                                <div className="mt-8 flex gap-3">
                                    <input
                                        type="hidden"
                                        name="birthday_day"
                                        value={birthdayDay}
                                    />
                                    <Select
                                        value={birthdayDay}
                                        onValueChange={setBirthdayDay}
                                    >
                                        <SelectTrigger className="bg-muted h-14 flex-1 rounded-xl border-transparent px-4">
                                            <SelectValue placeholder="Select Day" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Array.from(
                                                { length: 31 },
                                                (_, i) => i + 1,
                                            ).map((day) => (
                                                <SelectItem
                                                    key={day}
                                                    value={String(day)}
                                                >
                                                    {day}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <input
                                        type="hidden"
                                        name="birthday_month"
                                        value={birthdayMonth}
                                    />
                                    <Select
                                        value={birthdayMonth}
                                        onValueChange={setBirthdayMonth}
                                    >
                                        <SelectTrigger className="bg-muted h-14 flex-1 rounded-xl border-transparent px-4">
                                            <SelectValue placeholder="Select Month" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {MONTHS.map((month, i) => (
                                                <SelectItem
                                                    key={month}
                                                    value={String(i + 1)}
                                                >
                                                    {month}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <InputError message={errors.birthday_day} />
                                <InputError message={errors.birthday_month} />

                                <button
                                    type="submit"
                                    className="text-primary mt-8 block w-full text-center text-sm font-semibold"
                                >
                                    Skip
                                </button>

                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="mt-4 h-14 w-full rounded-full text-base"
                                >
                                    {processing && <Spinner />}
                                    Continue
                                </Button>
                            </div>
                        </>
                    );
                }}
            </Form>
        </>
    );
}

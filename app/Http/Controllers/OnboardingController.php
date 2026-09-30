<?php

namespace App\Http\Controllers;

use App\Actions\CompleteOnboarding;
use App\Http\Requests\Onboarding\CompleteOnboardingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Show the one-time profile-completion flow.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('onboarding/index', [
            'name' => $request->user()->name,
        ]);
    }

    /**
     * Complete the profile-completion flow and continue to the app.
     */
    public function update(CompleteOnboardingRequest $request, CompleteOnboarding $action): RedirectResponse
    {
        $action->handle($request->user(), [
            'name' => $request->validated('name'),
            'whatsapp_number' => $request->validated('whatsapp_number'),
            'goals' => $request->validated('goals'),
            'birthday_day' => $request->validated('birthday_day'),
            'birthday_month' => $request->validated('birthday_month'),
        ]);

        return redirect()->intended(route('home'));
    }
}

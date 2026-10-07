<?php

namespace App\Http\Controllers\Partner;

use App\Actions\RegisterPartner;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PartnerRegistrationController extends Controller
{
    /**
     * Show the accountability partner sign-up form.
     */
    public function create(): Response
    {
        return Inertia::render('auth/partner-register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Create a partner account and send them to the waiting screen.
     */
    public function store(Request $request, RegisterPartner $action): RedirectResponse
    {
        $user = $action->handle($request->only(['name', 'email', 'password', 'password_confirmation']));

        Auth::login($user);

        return to_route('partner.pending');
    }

    /**
     * The screen partners see until an admin approves their account.
     */
    public function pending(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user->isPartner() || $user->isApprovedPartner()) {
            return to_route('home');
        }

        return Inertia::render('auth/partner-pending', [
            'rejected' => $user->partner_status?->value === 'rejected',
        ]);
    }
}

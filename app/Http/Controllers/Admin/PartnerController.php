<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DecidePartnerApplication;
use App\Enums\PartnerStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PartnerController extends Controller
{
    /**
     * Approve or reject an accountability partner's sign-up.
     */
    public function update(Request $request, User $user, DecidePartnerApplication $action): RedirectResponse
    {
        $this->authorize('access-admin');
        abort_unless($user->isPartner(), 404);

        $decision = $request->validate([
            'decision' => ['required', Rule::in([PartnerStatus::Approved->value, PartnerStatus::Rejected->value])],
        ])['decision'];

        $action->handle($user, PartnerStatus::from($decision));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Partner updated.')]);

        return back();
    }
}

<?php

namespace App\Http\Controllers\Partner;

use App\Actions\ApplyToBePartner;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerApplicationController extends Controller
{
    /**
     * Let a signed-in user apply to become an accountability partner. An
     * admin still has to approve it.
     */
    public function store(Request $request, ApplyToBePartner $action): RedirectResponse
    {
        abort_unless($request->user()->canApplyAsPartner(), 403);

        $action->handle($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Application sent. An admin will review it.')]);

        return back();
    }
}

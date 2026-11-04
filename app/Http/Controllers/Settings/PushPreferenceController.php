<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePushPreferencesRequest;
use Illuminate\Http\RedirectResponse;

class PushPreferenceController extends Controller
{
    /**
     * Switch push alerts for group chat and/or insight activity on or off.
     */
    public function update(UpdatePushPreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->has('chat')) {
            $user->push_chat_enabled = $request->boolean('chat');
        }

        if ($request->has('insights')) {
            $user->push_insights_enabled = $request->boolean('insights');
        }

        $user->save();

        return back();
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\GroupMembershipStatus;
use App\Http\Requests\Groups\UpdateGroupNotificationsRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;

class GroupNotificationController extends Controller
{
    /**
     * Mute or unmute push alerts from one group for the current member.
     */
    public function update(UpdateGroupNotificationsRequest $request, Group $group): RedirectResponse
    {
        $membership = $group->memberships()
            ->where('user_id', $request->user()->id)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->firstOrFail();

        $membership->notifications_muted = $request->boolean('muted');
        $membership->save();

        return back();
    }
}

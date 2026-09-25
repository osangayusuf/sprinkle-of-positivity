<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePushSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Subscribe (or re-subscribe) the current user's browser to web push.
     */
    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        $request->user()->updatePushSubscription(
            $request->string('endpoint')->value(),
            $request->string('keys.p256dh')->value(),
            $request->string('keys.auth')->value(),
        );

        return response()->json(['subscribed' => true]);
    }

    /**
     * Unsubscribe the current user's browser from web push.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->deletePushSubscription(
            $request->string('endpoint')->value()
        );

        return response()->json(['subscribed' => false]);
    }
}

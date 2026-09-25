<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * List the user's most recent notifications.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('notifications/index', [
            'notifications' => NotificationResource::collection(
                $request->user()->notifications()->limit(50)->get()
            ),
        ]);
    }

    /**
     * Show a single notification, marking it as read.
     */
    public function show(Request $request, string $notification): Response
    {
        $model = $request->user()->notifications()->findOrFail($notification);

        if (! $model->read_at) {
            $model->markAsRead();
        }

        return Inertia::render('notifications/show', [
            'notification' => new NotificationResource($model),
        ]);
    }
}

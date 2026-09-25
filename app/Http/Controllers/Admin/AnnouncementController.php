<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendAnnouncement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendAnnouncementRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * Show the announcement composer.
     */
    public function create(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/announcements/create');
    }

    /**
     * Send an announcement to every user on the platform.
     */
    public function store(SendAnnouncementRequest $request, SendAnnouncement $action): RedirectResponse
    {
        $action->handle($request->string('title')->value(), $request->string('body')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Announcement sent.')]);

        return to_route('admin.announcements.create');
    }
}

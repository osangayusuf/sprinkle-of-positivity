<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendAnnouncement;
use App\Actions\UpdateAnnouncement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * List every announcement ever sent, newest first.
     */
    public function index(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/announcements/index', [
            'announcements' => AnnouncementResource::collection(
                Announcement::query()->latest()->get()
            ),
        ]);
    }

    /**
     * Show the announcement composer.
     */
    public function create(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/announcements/create');
    }

    /**
     * Record an announcement and send it to every user on the platform.
     */
    public function store(SendAnnouncementRequest $request, SendAnnouncement $action): RedirectResponse
    {
        $action->handle($request->user(), $request->string('title')->value(), $request->string('body')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Announcement sent.')]);

        return to_route('admin.announcements.index');
    }

    /**
     * Show the form for correcting a sent announcement.
     */
    public function edit(Announcement $announcement): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/announcements/edit', [
            'announcement' => new AnnouncementResource($announcement),
        ]);
    }

    /**
     * Correct a sent announcement, optionally sending it out again.
     */
    public function update(SendAnnouncementRequest $request, Announcement $announcement, UpdateAnnouncement $action): RedirectResponse
    {
        $resend = $request->boolean('resend');

        $action->handle(
            $announcement,
            $request->string('title')->value(),
            $request->string('body')->value(),
            $resend,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $resend ? __('Announcement updated and sent again.') : __('Announcement updated.'),
        ]);

        return to_route('admin.announcements.index');
    }
}

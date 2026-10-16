<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * Every announcement sent to the community, newest first.
     */
    public function index(): Response
    {
        return Inertia::render('announcements/index', [
            'announcements' => AnnouncementResource::collection(
                Announcement::query()->latest()->limit(100)->get()
            ),
        ]);
    }
}

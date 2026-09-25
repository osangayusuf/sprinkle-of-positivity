<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetDailyVerseRequest;
use App\Http\Resources\VerseResource;
use App\Models\DailyVerse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DailyVerseController extends Controller
{
    /**
     * Show the form for setting the global verse for today or a future date.
     */
    public function edit(?string $date = null): Response|RedirectResponse
    {
        $this->authorize('access-admin');

        $resolvedDate = $date ? Carbon::parse($date) : today();

        if ($resolvedDate->lt(today())) {
            return to_route('admin.daily-verse.edit');
        }

        $verse = DailyVerse::query()->whereDate('date', $resolvedDate)->first();

        return Inertia::render('admin/daily-verse/edit', [
            'verse' => $verse ? new VerseResource($verse) : null,
            'date' => $resolvedDate->toDateString(),
        ]);
    }

    /**
     * Set the global verse for today or a future date.
     */
    public function update(SetDailyVerseRequest $request, ?string $date = null): RedirectResponse
    {
        $resolvedDate = $date ? Carbon::parse($date) : today();

        abort_if($resolvedDate->lt(today()), 403);

        $verse = DailyVerse::query()->whereDate('date', $resolvedDate)->first() ?? new DailyVerse;
        $verse->date = $resolvedDate;
        $verse->reference = $request->string('reference')->value();
        $verse->text = $request->string('text')->value();
        $verse->created_by = $request->user()->id;

        if ($request->hasFile('image')) {
            $verse->image_path = $request->file('image')->store('daily-verses', 'public');
        }

        $verse->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('The verse for :date was updated.', ['date' => $resolvedDate->toFormattedDateString()])]);

        return to_route('admin.daily-verse.edit', ['date' => $resolvedDate->toDateString()]);
    }

    /**
     * List every global verse that has ever been set, most recent first.
     */
    public function history(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/daily-verse/history', [
            'verses' => VerseResource::collection(
                DailyVerse::query()->orderByDesc('date')->get()
            ),
        ]);
    }
}

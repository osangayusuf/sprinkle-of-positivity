<?php

namespace App\Http\Controllers;

use App\Actions\PostComment;
use App\Http\Requests\Groups\PostCommentRequest;
use App\Models\Comment;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CommentController extends Controller
{
    /**
     * Post a comment or threaded reply on an insight.
     */
    public function store(PostCommentRequest $request, Group $group, Insight $insight, PostComment $action): RedirectResponse
    {
        abort_unless($this->belongsToGroup($insight, $group), 404);

        $parent = $request->integer('parent_id')
            ? Comment::query()->find($request->integer('parent_id'))
            : null;

        $action->handle($insight, $request->user(), $request->string('body')->value(), $parent);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Comment posted. +5 points')]);

        return back();
    }

    /**
     * Confirm the given insight was posted under this group's verse.
     */
    private function belongsToGroup(Insight $insight, Group $group): bool
    {
        return $insight->verseable instanceof GroupVerse
            && $insight->verseable->group_id === $group->id;
    }
}

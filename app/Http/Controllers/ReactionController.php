<?php

namespace App\Http\Controllers;

use App\Actions\ToggleReaction;
use App\Http\Requests\Groups\ToggleReactionRequest;
use App\Models\Comment;
use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use Illuminate\Http\RedirectResponse;

class ReactionController extends Controller
{
    /**
     * Toggle the current user's reaction on an insight.
     */
    public function forInsight(ToggleReactionRequest $request, Group $group, Insight $insight, ToggleReaction $action): RedirectResponse
    {
        abort_unless($this->insightBelongsToGroup($insight, $group), 404);

        $action->handle($insight, $request->user(), $request->string('emoji')->value());

        return back();
    }

    /**
     * Toggle the current user's reaction on a comment.
     */
    public function forComment(ToggleReactionRequest $request, Group $group, Insight $insight, Comment $comment, ToggleReaction $action): RedirectResponse
    {
        abort_unless($this->insightBelongsToGroup($insight, $group), 404);
        abort_unless($comment->commentable_id === $insight->id && $comment->commentable_type === $insight->getMorphClass(), 404);

        $action->handle($comment, $request->user(), $request->string('emoji')->value());

        return back();
    }

    /**
     * Confirm the given insight was posted under this group's verse.
     */
    private function insightBelongsToGroup(Insight $insight, Group $group): bool
    {
        return $insight->verseable instanceof GroupVerse
            && $insight->verseable->group_id === $group->id;
    }
}

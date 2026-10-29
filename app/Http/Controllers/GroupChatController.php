<?php

namespace App\Http\Controllers;

use App\Actions\PostGroupMessage;
use App\Actions\UpdateGroupMessage;
use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Http\Requests\Groups\PostGroupMessageRequest;
use App\Http\Requests\Groups\UpdateGroupMessageRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupChatController extends Controller
{
    private const PAGE_SIZE = 50;

    private const MAX_MESSAGES = 500;

    /**
     * Show a group's chat: the latest messages, oldest first. The page polls
     * for new ones and raises `limit` to load earlier history. Only approved
     * members, managers and admins can read it, even in a public group.
     */
    public function show(Request $request, Group $group): Response
    {
        $this->authorize('participate', $group);

        $user = $request->user();
        $canManage = $user->can('manage', $group);
        $limit = min(max($request->integer('limit', self::PAGE_SIZE), 1), self::MAX_MESSAGES);

        $latest = GroupMessage::query()
            ->where('group_id', $group->id)
            ->with(['user.roles', 'mentions:users.id,users.name', 'replyTo.user:id,name', 'replyTo.mentions:users.id,users.name'])
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $managerIds = $group->memberships()
            ->where('role', GroupMembershipRole::Manager->value)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->pluck('user_id');

        return Inertia::render('groups/chat', [
            'group' => new GroupResource($group),
            'messages' => $latest->take($limit)->reverse()->values()->map(fn (GroupMessage $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'preview' => $message->preview(),
                'edited' => $message->edited_at !== null,
                'created_at' => $message->created_at->toIso8601String(),
                'author' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'avatar' => $message->user->avatar,
                    'is_manager' => $managerIds->contains($message->user_id) || $message->user->hasRole(Role::ADMIN),
                ],
                'mentions' => $message->mentions->map(fn ($mentioned) => [
                    'id' => $mentioned->id,
                    'name' => $mentioned->name,
                ])->values(),
                'reply_to' => $message->replyTo ? [
                    'id' => $message->replyTo->id,
                    'author_name' => $message->replyTo->user->name,
                    'preview' => $message->replyTo->preview(),
                ] : null,
                'mine' => $message->user_id === $user->id,
                'can_edit' => $message->user_id === $user->id,
                'can_delete' => $canManage || $message->user_id === $user->id,
            ]),
            'members' => $group->approvedMembers()
                ->whereKeyNot($user->id)
                ->orderBy('users.name')
                ->get(['users.id', 'users.name', 'users.avatar_path'])
                ->map(fn ($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'avatar' => $member->avatar,
                ]),
            'hasMore' => $latest->count() > $limit,
            'limit' => $limit,
        ]);
    }

    /**
     * Post a message to the group's chat.
     */
    public function store(PostGroupMessageRequest $request, Group $group, PostGroupMessage $action): RedirectResponse
    {
        $action->handle($group, $request->user(), $request->string('body')->value(), $request->integer('reply_to_id') ?: null);

        return back();
    }

    /**
     * Edit a message: only its author can.
     */
    public function update(UpdateGroupMessageRequest $request, Group $group, GroupMessage $message, UpdateGroupMessage $action): RedirectResponse
    {
        abort_unless($message->group_id === $group->id, 404);

        $action->handle($message, $request->string('body')->value());

        return back();
    }

    /**
     * Delete a message: its author can, and so can the group's managers.
     */
    public function destroy(Request $request, Group $group, GroupMessage $message): RedirectResponse
    {
        abort_unless($message->group_id === $group->id, 404);
        abort_unless(
            $request->user()->can('participate', $group)
                && ($message->user_id === $request->user()->id || $request->user()->can('manage', $group)),
            403,
        );

        $message->delete();

        return back();
    }
}

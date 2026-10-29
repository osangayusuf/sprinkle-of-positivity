<?php

use App\Enums\GroupMembershipStatus;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use App\Notifications\MentionedInGroupChat;
use Illuminate\Support\Facades\Notification;

test('a member sees the group chat oldest message first', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    GroupMessage::factory()->for($group)->create(['body' => 'First']);
    GroupMessage::factory()->for($group)->for($member)->create(['body' => 'Second']);

    $this->actingAs($member)->get(route('groups.chat.show', $group))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('groups/chat')
            ->where('messages.0.body', 'First')
            ->where('messages.0.mine', false)
            ->where('messages.1.body', 'Second')
            ->where('messages.1.mine', true)
            ->where('messages.1.can_delete', true)
            ->where('messages.0.can_delete', false)
            ->where('hasMore', false));
});

test('other groups messages are not shown', function () {
    $group = Group::factory()->create();
    GroupMessage::factory()->create(['body' => 'Elsewhere']);
    GroupMessage::factory()->for($group)->create(['body' => 'Here']);

    $this->actingAs(createApprovedMember($group))->get(route('groups.chat.show', $group))
        ->assertInertia(fn ($page) => $page->has('messages', 1)->where('messages.0.body', 'Here'));
});

test('only members and admins can read the chat', function () {
    $group = Group::factory()->create();

    $this->actingAs(User::factory()->create())->get(route('groups.chat.show', $group))->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->get(route('groups.chat.show', $group))->assertOk();
});

test('a private group chat is closed to outsiders too', function () {
    $group = Group::factory()->create(['is_private' => true]);

    $this->actingAs(User::factory()->create())->get(route('groups.chat.show', $group))->assertForbidden();
});

test('guests are sent to log in', function () {
    $group = Group::factory()->create();

    $this->get(route('groups.chat.show', $group))->assertRedirect(route('login'));
    $this->post(route('groups.chat.store', $group), ['body' => 'Hi'])->assertRedirect(route('login'));
});

test('a member can post a message', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);

    $this->actingAs($member)
        ->post(route('groups.chat.store', $group), ['body' => 'Hello everyone'])
        ->assertRedirect();

    expect(GroupMessage::query()->sole())
        ->group_id->toBe($group->id)
        ->user_id->toBe($member->id)
        ->body->toBe('Hello everyone');
});

test('someone outside the group cannot post', function () {
    $group = Group::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('groups.chat.store', $group), ['body' => 'Hello'])
        ->assertForbidden();

    expect(GroupMessage::query()->count())->toBe(0);
});

test('messages must have a body of a sensible length', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);

    $this->actingAs($member)->post(route('groups.chat.store', $group), ['body' => ''])->assertInvalid('body');
    $this->actingAs($member)->post(route('groups.chat.store', $group), ['body' => str_repeat('a', 1001)])->assertInvalid('body');

    expect(GroupMessage::query()->count())->toBe(0);
});

test('the chat returns the latest page and offers earlier history', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    foreach (range(1, 60) as $number) {
        GroupMessage::factory()->for($group)->create(['body' => "Message $number"]);
    }

    $this->actingAs($member)->get(route('groups.chat.show', $group))
        ->assertInertia(fn ($page) => $page
            ->has('messages', 50)
            ->where('messages.0.body', 'Message 11')
            ->where('messages.49.body', 'Message 60')
            ->where('hasMore', true));

    $this->actingAs($member)->get(route('groups.chat.show', [$group, 'limit' => 100]))
        ->assertInertia(fn ($page) => $page
            ->has('messages', 60)
            ->where('messages.0.body', 'Message 1')
            ->where('hasMore', false));
});

test('the history limit is clamped', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);

    $this->actingAs($member)->get(route('groups.chat.show', [$group, 'limit' => 100000]))
        ->assertInertia(fn ($page) => $page->where('limit', 500));
    $this->actingAs($member)->get(route('groups.chat.show', [$group, 'limit' => -5]))
        ->assertInertia(fn ($page) => $page->where('limit', 1));
});

test('messages from managers and admins are marked', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    GroupMessage::factory()->for($group)->for(createApprovedManager($group))->create();
    GroupMessage::factory()->for($group)->for(User::factory()->admin()->create())->create();
    GroupMessage::factory()->for($group)->for(createApprovedMember($group))->create();

    $this->actingAs($member)->get(route('groups.chat.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('messages.0.author.is_manager', true)
            ->where('messages.1.author.is_manager', true)
            ->where('messages.2.author.is_manager', false));
});

test('an author can delete their own message', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $message = GroupMessage::factory()->for($group)->for($member)->create();

    $this->actingAs($member)->delete(route('groups.chat.destroy', [$group, $message]))->assertRedirect();

    expect(GroupMessage::query()->count())->toBe(0);
});

test('a manager can delete anyones message but a member cannot', function () {
    $group = Group::factory()->create();
    $author = createApprovedMember($group);
    $message = GroupMessage::factory()->for($group)->for($author)->create();

    $this->actingAs(createApprovedMember($group))->delete(route('groups.chat.destroy', [$group, $message]))->assertForbidden();
    expect(GroupMessage::query()->count())->toBe(1);

    $this->actingAs(createApprovedManager($group))->delete(route('groups.chat.destroy', [$group, $message]))->assertRedirect();
    expect(GroupMessage::query()->count())->toBe(0);
});

test('a message cannot be deleted through another groups chat', function () {
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);
    $foreign = GroupMessage::factory()->create();

    $this->actingAs($manager)->delete(route('groups.chat.destroy', [$group, $foreign]))->assertNotFound();

    expect(GroupMessage::query()->count())->toBe(1);
});

test('the group page offers the chat only to people who can use it', function () {
    $group = Group::factory()->create();

    $this->actingAs(createApprovedMember($group))->get(route('groups.show', $group))
        ->assertInertia(fn ($page) => $page->where('canChat', true));
    $this->actingAs(User::factory()->create())->get(route('groups.show', $group))
        ->assertInertia(fn ($page) => $page->where('canChat', false));
});

test('tagged members are recorded and notified', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = createApprovedMember($group);
    $tagged = createApprovedMember($group);
    $bystander = createApprovedMember($group);

    $this->actingAs($author)
        ->post(route('groups.chat.store', $group), ['body' => "Hi @[{$tagged->id}], welcome!"])
        ->assertRedirect();

    expect(GroupMessage::query()->sole()->mentions->modelKeys())->toBe([$tagged->id]);
    Notification::assertSentTo($tagged, MentionedInGroupChat::class);
    Notification::assertNotSentTo($bystander, MentionedInGroupChat::class);
});

test('mention tokens for outsiders, pending members and yourself are ignored', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = createApprovedMember($group);
    $outsider = User::factory()->create();
    $pending = User::factory()->create();
    $membership = joinAsApprovedMember($group, $pending);
    $membership->status = GroupMembershipStatus::Pending;
    $membership->save();

    $this->actingAs($author)
        ->post(route('groups.chat.store', $group), [
            'body' => "@[{$outsider->id}] @[{$pending->id}] @[{$author->id}]",
        ]);

    expect(GroupMessage::query()->sole()->mentions)->toHaveCount(0);
    Notification::assertNothingSent();
});

test('the chat lists each message mentions and the members who can be tagged', function () {
    $group = Group::factory()->create();
    $viewer = createApprovedMember($group);
    $other = createApprovedMember($group);
    $outsider = User::factory()->create();
    $message = GroupMessage::factory()->for($group)->create(['body' => "Hello @[{$other->id}]"]);
    $message->mentions()->attach($other->id);

    $this->actingAs($viewer)->get(route('groups.chat.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('messages.0.mentions.0.id', $other->id)
            ->where('messages.0.mentions.0.name', $other->name)
            ->has('members', 1)
            ->where('members.0.id', $other->id));
});

test('a member can reply to an earlier message', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $original = GroupMessage::factory()->for($group)->create(['body' => 'Original']);

    $this->actingAs($member)
        ->post(route('groups.chat.store', $group), ['body' => 'Agreed', 'reply_to_id' => $original->id])
        ->assertRedirect();

    expect(GroupMessage::query()->latest('id')->first()->reply_to_id)->toBe($original->id);

    $this->actingAs($member)->get(route('groups.chat.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('messages.1.reply_to.id', $original->id)
            ->where('messages.1.reply_to.author_name', $original->user->name)
            ->where('messages.1.reply_to.preview', 'Original')
            ->where('messages.0.reply_to', null));
});

test('a reply cannot point at a message from another group or one that does not exist', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $elsewhere = GroupMessage::factory()->create();

    $this->actingAs($member)
        ->post(route('groups.chat.store', $group), ['body' => 'Hi', 'reply_to_id' => $elsewhere->id])
        ->assertInvalid('reply_to_id');
    $this->actingAs($member)
        ->post(route('groups.chat.store', $group), ['body' => 'Hi', 'reply_to_id' => 99999])
        ->assertInvalid('reply_to_id');

    expect(GroupMessage::query()->where('group_id', $group->id)->count())->toBe(0);
});

test('deleting a message keeps the replies to it', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $original = GroupMessage::factory()->for($group)->for($member)->create();
    $reply = GroupMessage::factory()->for($group)->create(['reply_to_id' => $original->id]);

    $this->actingAs($member)->delete(route('groups.chat.destroy', [$group, $original]))->assertRedirect();

    expect($reply->fresh()->reply_to_id)->toBeNull();
});

test('a reply quote shows tagged names instead of mention tokens', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $tagged = createApprovedMember($group);
    $original = GroupMessage::factory()->for($group)->create(['body' => "Hey @[{$tagged->id}]"]);
    $original->mentions()->attach($tagged->id);
    GroupMessage::factory()->for($group)->create(['reply_to_id' => $original->id]);

    $this->actingAs($member)->get(route('groups.chat.show', $group))
        ->assertInertia(fn ($page) => $page->where('messages.1.reply_to.preview', "Hey @{$tagged->name}"));
});

test('@everyone notifies every other approved member but is not recorded as individual mentions', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = createApprovedMember($group);
    $first = createApprovedMember($group);
    $second = createApprovedMember($group);
    $outsider = User::factory()->create();

    $this->actingAs($author)->post(route('groups.chat.store', $group), ['body' => 'Hello @everyone!']);

    Notification::assertSentTo([$first, $second], MentionedInGroupChat::class);
    Notification::assertNotSentTo([$author, $outsider], MentionedInGroupChat::class);
    expect(GroupMessage::query()->sole()->mentions)->toHaveCount(0);
});

test('@everyone and a direct tag notify a member only once', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = createApprovedMember($group);
    $tagged = createApprovedMember($group);

    $this->actingAs($author)->post(route('groups.chat.store', $group), ['body' => "@everyone and @[{$tagged->id}]"]);

    Notification::assertSentToTimes($tagged, MentionedInGroupChat::class, 1);
});

test('text that merely contains everyone does not notify the group', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = createApprovedMember($group);
    createApprovedMember($group);

    $this->actingAs($author)->post(route('groups.chat.store', $group), ['body' => 'hi everyone, mail me at a@everyone.com']);

    Notification::assertNothingSent();
});

test('an author can edit their message and it is marked as edited', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $message = GroupMessage::factory()->for($group)->for($member)->create(['body' => 'Old']);

    $this->actingAs($member)
        ->patch(route('groups.chat.update', [$group, $message]), ['body' => 'New'])
        ->assertRedirect();

    expect($message->fresh())->body->toBe('New')->edited_at->not->toBeNull();

    $this->actingAs($member)->get(route('groups.chat.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('messages.0.body', 'New')
            ->where('messages.0.edited', true)
            ->where('messages.0.can_edit', true));
});

test('only the author can edit a message, not even a manager', function () {
    $group = Group::factory()->create();
    $message = GroupMessage::factory()->for($group)->create(['body' => 'Mine']);

    $this->actingAs(createApprovedMember($group))
        ->patch(route('groups.chat.update', [$group, $message]), ['body' => 'Hijack'])
        ->assertForbidden();
    $this->actingAs(createApprovedManager($group))
        ->patch(route('groups.chat.update', [$group, $message]), ['body' => 'Hijack'])
        ->assertForbidden();

    expect($message->fresh()->body)->toBe('Mine');
});

test('an edited message must still have a valid body and belong to the group', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $message = GroupMessage::factory()->for($group)->for($member)->create();
    $foreign = GroupMessage::factory()->for($member)->create();

    $this->actingAs($member)->patch(route('groups.chat.update', [$group, $message]), ['body' => ''])->assertInvalid('body');
    $this->actingAs($member)->patch(route('groups.chat.update', [$group, $foreign]), ['body' => 'Moved'])->assertNotFound();
});

test('editing a message re-syncs its mentions and only notifies newly tagged members', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = createApprovedMember($group);
    $kept = createApprovedMember($group);
    $dropped = createApprovedMember($group);
    $added = createApprovedMember($group);

    $this->actingAs($author)->post(route('groups.chat.store', $group), ['body' => "@[{$kept->id}] @[{$dropped->id}]"]);
    $message = GroupMessage::query()->sole();

    $this->actingAs($author)->patch(route('groups.chat.update', [$group, $message]), ['body' => "@[{$kept->id}] @[{$added->id}]"]);

    expect($message->fresh()->mentions->modelKeys())->toEqualCanonicalizing([$kept->id, $added->id]);
    Notification::assertSentToTimes($kept, MentionedInGroupChat::class, 1);
    Notification::assertSentToTimes($added, MentionedInGroupChat::class, 1);
});

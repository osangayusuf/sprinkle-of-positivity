<?php

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;

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

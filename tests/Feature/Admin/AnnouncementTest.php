<?php

use App\Models\User;
use App\Notifications\Announcement;
use Illuminate\Support\Facades\Notification;

test('an admin sending an announcement fans it out to every user', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $members = User::factory()->count(3)->create();
    $this->actingAs($admin);

    $this->post(route('admin.announcements.store'), [
        'title' => 'Weekly Fast Alert! 🎉',
        'body' => 'This is to remind you that we will be fasting from tomorrow.',
    ])->assertRedirect(route('admin.announcements.create'));

    Notification::assertSentTo($admin, Announcement::class);

    foreach ($members as $member) {
        Notification::assertSentTo($member, Announcement::class);
    }
});

test('a non-admin cannot send an announcement', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.announcements.create'))->assertForbidden();

    $this->post(route('admin.announcements.store'), [
        'title' => 'Hi',
        'body' => 'Hi there.',
    ])->assertForbidden();
});

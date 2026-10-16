<?php

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\Announcement as AnnouncementNotification;
use Illuminate\Support\Facades\Notification;

test('an admin sending an announcement records it and fans it out to every user', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $members = User::factory()->count(3)->create();
    $this->actingAs($admin);

    $this->post(route('admin.announcements.store'), [
        'title' => 'Weekly Fast Alert! 🎉',
        'body' => 'This is to remind you that we will be fasting from tomorrow.',
    ])->assertRedirect(route('admin.announcements.index'));

    $announcement = Announcement::query()->sole();
    expect($announcement->created_by)->toBe($admin->id);
    expect($announcement->last_sent_at)->not->toBeNull();

    Notification::assertSentTo($admin, AnnouncementNotification::class);

    foreach ($members as $member) {
        Notification::assertSentTo($member, AnnouncementNotification::class);
    }
});

test('admins can review previously sent announcements', function () {
    $this->actingAs(User::factory()->admin()->create());
    Announcement::factory()->count(2)->create();

    $this->get(route('admin.announcements.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('announcements', 2));
});

test('editing an announcement rewrites notifications members already received', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $announcement = Announcement::factory()->create(['title' => 'Fast on Fridya', 'body' => 'Old text']);
    $member->notify(new AnnouncementNotification($announcement->title, $announcement->body, $announcement->id));
    $other = User::factory()->create();
    $other->notify(new AnnouncementNotification('Unrelated', 'Leave me alone', announcementId: 999));
    $this->actingAs($admin);

    $this->put(route('admin.announcements.update', $announcement), [
        'title' => 'Fast on Friday',
        'body' => 'Corrected text',
    ])->assertRedirect(route('admin.announcements.index'));

    expect($announcement->fresh())
        ->title->toBe('Fast on Friday')
        ->body->toBe('Corrected text');
    expect($member->notifications()->sole()->data)
        ->toMatchArray(['title' => 'Fast on Friday', 'body' => 'Corrected text']);
    expect($other->notifications()->sole()->data['title'])->toBe('Unrelated');
});

test('an announcement is only sent again when the admin asks', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $announcement = Announcement::factory()->create(['last_sent_at' => now()->subDay()]);
    $this->actingAs($admin);

    $payload = ['title' => 'Updated', 'body' => 'Updated body'];

    $this->put(route('admin.announcements.update', $announcement), $payload)->assertRedirect();
    Notification::assertNothingSent();

    $this->put(route('admin.announcements.update', $announcement), $payload + ['resend' => true])->assertRedirect();
    Notification::assertSentTo($admin, AnnouncementNotification::class);
    expect($announcement->fresh()->last_sent_at->isToday())->toBeTrue();
});

test('a non-admin cannot send, review or edit announcements', function () {
    $user = User::factory()->create();
    $announcement = Announcement::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.announcements.index'))->assertForbidden();
    $this->get(route('admin.announcements.create'))->assertForbidden();
    $this->get(route('admin.announcements.edit', $announcement))->assertForbidden();

    $this->post(route('admin.announcements.store'), [
        'title' => 'Hi',
        'body' => 'Hi there.',
    ])->assertForbidden();

    $this->put(route('admin.announcements.update', $announcement), [
        'title' => 'Hi',
        'body' => 'Hi there.',
    ])->assertForbidden();
});

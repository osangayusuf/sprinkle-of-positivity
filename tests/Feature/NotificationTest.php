<?php

use App\Models\User;
use App\Notifications\Announcement;

test('a user can see their notifications', function () {
    $user = User::factory()->create();
    $user->notify(new Announcement('Time for Prayers 🎉', 'Engage in more quiet time.'));
    $this->actingAs($user);

    $response = $this->get(route('notifications.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('notifications', 1)
        ->where('notifications.0.title', 'Time for Prayers 🎉')
        ->where('notifications.0.read', false)
    );
});

test('viewing a notification marks it as read', function () {
    $user = User::factory()->create();
    $user->notify(new Announcement('Time for Prayers 🎉', 'Engage in more quiet time.'));
    $notification = $user->notifications()->sole();
    $this->actingAs($user);

    $this->get(route('notifications.show', $notification->id))->assertOk();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('a user cannot view another users notification', function () {
    $owner = User::factory()->create();
    $owner->notify(new Announcement('Time for Prayers 🎉', 'Engage in more quiet time.'));
    $notification = $owner->notifications()->sole();

    $outsider = User::factory()->create();
    $this->actingAs($outsider);

    $this->get(route('notifications.show', $notification->id))->assertNotFound();
});

<?php

use App\Models\User;
use NotificationChannels\WebPush\PushSubscription;

test('a user can subscribe to push notifications', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson('/push-subscriptions', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        'keys' => [
            'p256dh' => 'test-public-key',
            'auth' => 'test-auth-token',
        ],
    ]);

    $response->assertOk();
    $subscription = PushSubscription::query()->sole();
    expect($subscription->subscribable_id)->toBe($user->id);
    expect($subscription->endpoint)->toBe('https://fcm.googleapis.com/fcm/send/abc123');
});

test('a user can unsubscribe from push notifications', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $user->updatePushSubscription('https://fcm.googleapis.com/fcm/send/abc123', 'key', 'token');

    $response = $this->deleteJson('/push-subscriptions', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
    ]);

    $response->assertOk();
    expect(PushSubscription::query()->count())->toBe(0);
});

test('subscribing requires a valid endpoint and keys', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson('/push-subscriptions', [])
        ->assertInvalid(['endpoint', 'keys.p256dh', 'keys.auth']);
});

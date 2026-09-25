<?php

use App\Models\DailyVerse;
use App\Models\User;

test('home renders without a verse of the day set', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('home'))->assertOk();
});

test('home shows the verse of the day once one is set', function () {
    $user = User::factory()->create();
    $verse = DailyVerse::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('verse.reference', $verse->reference)
    );
});

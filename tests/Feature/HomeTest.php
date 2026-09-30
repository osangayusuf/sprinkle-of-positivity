<?php

use App\Models\User;

test('guests can visit home without any groups of their own', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('home/index')
            ->where('yourGroups', [])
        );
});

test('users who have not completed onboarding are redirected to it', function () {
    $user = User::factory()->pendingOnboarding()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertRedirect(route('onboarding.edit'));
});

test('onboarded users can visit home', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertOk();
});

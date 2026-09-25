<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('home'));
    $response->assertRedirect(route('login'));
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

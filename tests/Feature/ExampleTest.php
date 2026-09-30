<?php

use App\Models\User;

test('guests see the public landing page', function () {
    $this->get(route('root'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('landing'));
});

test('authenticated users are redirected to home', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('root'));

    $response->assertRedirect(route('home'));
});

test('anyone can see the testimonials page', function () {
    $this->get(route('testimonials'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('testimonials'));
});

test('anyone can see the support page', function () {
    $this->get(route('support'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('support'));
});

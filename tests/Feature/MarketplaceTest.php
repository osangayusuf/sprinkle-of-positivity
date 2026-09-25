<?php

use App\Models\MarketplaceListing;
use App\Models\User;

test('members only see active listings', function () {
    $active = MarketplaceListing::factory()->create(['title' => 'Active Sale']);
    $inactive = MarketplaceListing::factory()->inactive()->create(['title' => 'Inactive Sale']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('marketplace.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('listings', 1)
        ->where('listings.0.title', $active->title)
    );
});

test('listings outside their date window are hidden', function () {
    MarketplaceListing::factory()->create([
        'title' => 'Expired',
        'ends_at' => now()->subDay(),
    ]);
    MarketplaceListing::factory()->create([
        'title' => 'Not started yet',
        'starts_at' => now()->addDay(),
    ]);
    MarketplaceListing::factory()->create(['title' => 'Current']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('marketplace.index'));

    $response->assertInertia(fn ($page) => $page
        ->has('listings', 1)
        ->where('listings.0.title', 'Current')
    );
});

test('home includes featured active listings', function () {
    MarketplaceListing::factory()->count(3)->create();
    MarketplaceListing::factory()->inactive()->create();

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertInertia(fn ($page) => $page->has('featuredListings', 3));
});

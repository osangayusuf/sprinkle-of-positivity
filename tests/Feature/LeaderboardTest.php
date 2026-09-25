<?php

use App\Models\User;

test('the leaderboard ranks users by points descending', function () {
    $leader = User::factory()->create(['points' => 500]);
    $runnerUp = User::factory()->create(['points' => 200]);
    $this->actingAs($runnerUp);

    $response = $this->get(route('leaderboard.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('entries.0.id', $leader->id)
        ->where('entries.0.rank', 1)
        ->where('entries.1.id', $runnerUp->id)
        ->where('entries.1.rank', 2)
        ->where('entries.1.is_me', true)
    );
});

test('my level reflects my points threshold', function () {
    $user = User::factory()->create(['points' => 2050]);
    $this->actingAs($user);

    $response = $this->get(route('leaderboard.index'));

    $response->assertInertia(fn ($page) => $page->where('myLevel', 'Leader'));
});

test('the levels page lists every tier', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('leaderboard.levels'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('levels', 8));
});

test('the profile page shows points and level', function () {
    $user = User::factory()->create(['points' => 1500]);
    $this->actingAs($user);

    $response = $this->get(route('profile.show'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('points', 1500)
        ->where('level', 'Novice')
    );
});

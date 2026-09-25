<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\Quiz;
use App\Models\User;

test('home shows no groups section data when the user has no approved groups', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('yourGroups', []));
});

test('home shows the members approved groups ordered by most recent activity, capped at three', function () {
    $user = User::factory()->create();

    $groupA = Group::factory()->create(['name' => 'Group A', 'created_at' => now()->subDays(10)]);
    $groupB = Group::factory()->create(['name' => 'Group B', 'created_at' => now()->subDays(10)]);
    $groupC = Group::factory()->create(['name' => 'Group C', 'created_at' => now()->subDays(10)]);
    $groupD = Group::factory()->create(['name' => 'Group D', 'created_at' => now()->subDays(10)]);

    foreach ([$groupA, $groupB, $groupC, $groupD] as $group) {
        joinAsApprovedMember($group, $user);
    }

    // Oldest activity first, so the expected order is D, C, B (A drops out).
    GroupVerse::factory()->for($groupA)->create(['created_at' => now()->subDays(4)]);
    GroupVerse::factory()->for($groupB)->create(['created_at' => now()->subDays(3)]);
    $verseC = GroupVerse::factory()->for($groupC)->create(['created_at' => now()->subDays(2)]);
    Quiz::factory()->for($groupD)->create(['created_at' => now()->subDay()]);

    // A later insight is what makes group C rank above group D, even though
    // C's own verse predates D's quiz.
    Insight::factory()->for($verseC, 'verseable')->create(['created_at' => now()->subHours(12)]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('yourGroups', 3)
        ->where('yourGroups.0.name', 'Group C')
        ->where('yourGroups.1.name', 'Group D')
        ->where('yourGroups.2.name', 'Group B'));
});

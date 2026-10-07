<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('the verse page shows an empty state when no verse has been set', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('groups.verse.show', $group));

    $response->assertOk();
});

test('a manager can set the group verse for today', function () {
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $response = $this->put(route('groups.verse.update', $group), [
        'reference' => 'Joshua 2:1',
        'text' => 'Then Joshua son of Nun secretly sent two spies...',
    ]);

    $response->assertRedirect(route('groups.verse.show', $group));
    $response->assertInertiaFlash('toast');

    $verse = GroupVerse::query()->sole();
    expect($verse->group_id)->toBe($group->id);
    expect($verse->reference)->toBe('Joshua 2:1');
    expect($verse->created_by)->toBe($manager->id);
});

test('a manager can attach an image when setting the group verse', function () {
    Storage::fake('public');
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $this->put(route('groups.verse.update', $group), [
        'reference' => 'Joshua 2:1',
        'text' => 'Then Joshua son of Nun secretly sent two spies...',
        'image' => UploadedFile::fake()->image('verse.jpg'),
    ]);

    $verse = GroupVerse::query()->sole();
    expect($verse->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($verse->image_path);
});

test('a non-manager cannot set the group verse', function () {
    $group = Group::factory()->create();
    $outsider = User::factory()->create();
    $this->actingAs($outsider);

    $this->get(route('groups.verse.edit', $group))->assertForbidden();

    $this->put(route('groups.verse.update', $group), [
        'reference' => 'Joshua 2:1',
        'text' => 'Some text.',
    ])->assertForbidden();

    expect(GroupVerse::query()->count())->toBe(0);
});

test('setting the group verse again the same day updates it instead of duplicating', function () {
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $this->put(route('groups.verse.update', $group), [
        'reference' => 'Joshua 2:1',
        'text' => 'First text.',
    ]);

    $this->put(route('groups.verse.update', $group), [
        'reference' => 'Romans 8:28',
        'text' => 'Second text.',
    ]);

    expect(GroupVerse::query()->where('group_id', $group->id)->count())->toBe(1);
});

test('the verse page shows an earlier day\'s verse and insights from the date in the URL', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $manager = createApprovedManager($group);
    $member = createApprovedMember($group);
    completeDay($group, $member, 1);
    $date = $group->starts_on->toDateString();

    $this->actingAs($manager)
        ->get(route('groups.verse.show', ['group' => $group, 'date' => $date]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('viewingDate', $date)
            ->where('isToday', false)
            ->where('verse.date', $date)
            ->has('insights', 1)
            ->where('canParticipate', false));
});

test('the verse page lists past days with their insight counts, newest first', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $member = createApprovedMember($group);
    completeDay($group, $member, 1);

    $this->actingAs($member)
        ->get(route('groups.verse.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('isToday', true)
            ->has('pastDays', 4)
            ->where('pastDays.0.date', today()->toDateString())
            ->where('pastDays.3.date', $group->starts_on->toDateString())
            ->where('pastDays.3.insights_count', 1));
});

test('future and malformed dates fall back to today', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 3);
    $user = User::factory()->create();

    foreach ([today()->addDay()->toDateString(), 'not-a-date', '2026-13-45'] as $date) {
        $this->actingAs($user)
            ->get(route('groups.verse.show', ['group' => $group, 'date' => $date]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('isToday', true));
    }
});

test('past days of a private group stay hidden from non-members', function () {
    $group = Group::factory()->private()->create(['duration_days' => 5, 'starts_on' => today()->subDays(2)]);

    $this->get(route('groups.verse.show', ['group' => $group, 'date' => today()->subDay()->toDateString()]))
        ->assertRedirect(route('groups.show', $group));
});

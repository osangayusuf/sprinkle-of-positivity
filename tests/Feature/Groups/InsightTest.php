<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an approved member can share an insight on todays group verse', function () {
    Storage::fake('public');

    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $response = $this->post(route('groups.insights.store', $group), [
        'body' => 'God is faithful.',
        'image' => UploadedFile::fake()->image('reflection.jpg'),
    ]);

    $insight = Insight::query()->sole();
    $response->assertRedirect(route('groups.insights.show', [$group, $insight]));

    expect($insight->user_id)->toBe($member->id);
    expect($insight->body)->toBe('God is faithful.');
    expect($insight->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($insight->image_path);
});

test('a non-member cannot share an insight', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $outsider = User::factory()->create();
    $this->actingAs($outsider);

    $this->post(route('groups.insights.store', $group), [
        'body' => 'God is faithful.',
    ])->assertForbidden();

    expect(Insight::query()->count())->toBe(0);
});

test('an insight from another group 404s', function () {
    $group = Group::factory()->create();
    $otherGroup = Group::factory()->create();
    $insight = insightFor($otherGroup);

    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->get(route('groups.insights.show', [$group, $insight]))->assertNotFound();
});

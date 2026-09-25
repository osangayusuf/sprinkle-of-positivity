<?php

use App\Models\Group;
use App\Models\Reaction;
use App\Models\User;

test('a member can react to an insight', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.insights.reactions.store', [$group, $insight]), [
        'emoji' => '🔥',
    ])->assertRedirect();

    $reaction = Reaction::query()->sole();
    expect($reaction->user_id)->toBe($member->id);
    expect($reaction->reactable_id)->toBe($insight->id);
    expect($reaction->emoji)->toBe('🔥');
});

test('reacting with the same emoji again removes the reaction', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.insights.reactions.store', [$group, $insight]), ['emoji' => '🔥']);
    $this->post(route('groups.insights.reactions.store', [$group, $insight]), ['emoji' => '🔥']);

    expect(Reaction::query()->count())->toBe(0);
});

test('reacting with a different emoji replaces the previous reaction', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.insights.reactions.store', [$group, $insight]), ['emoji' => '🔥']);
    $this->post(route('groups.insights.reactions.store', [$group, $insight]), ['emoji' => '😊']);

    $reaction = Reaction::query()->sole();
    expect($reaction->emoji)->toBe('😊');
});

test('a non-member cannot react', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $outsider = User::factory()->create();
    $this->actingAs($outsider);

    $this->post(route('groups.insights.reactions.store', [$group, $insight]), [
        'emoji' => '🔥',
    ])->assertForbidden();
});

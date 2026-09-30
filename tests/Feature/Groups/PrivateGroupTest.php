<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\User;
use App\Policies\GroupPolicy;

test('private groups are still listed and their page is visible to anyone', function () {
    $group = Group::factory()->private()->create();

    $this->get(route('groups.index'))
        ->assertInertia(fn ($page) => $page
            ->where('discoverGroups.0.id', $group->id)
            ->where('discoverGroups.0.is_private', true)
        );

    $this->get(route('groups.show', $group))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('canViewContent', false));
});

test('non-members are sent back to the group page from a private group\'s content', function (bool $signedIn) {
    $group = Group::factory()->private()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $insight = insightFor($group);

    if ($signedIn) {
        $this->actingAs(User::factory()->create());
    }

    $this->get(route('groups.verse.show', $group))
        ->assertRedirect(route('groups.show', $group))
        ->assertInertiaFlash('toast.message', 'This is a private group. Join it to read its Bible Study Insights.');

    $this->get(route('groups.insights.show', [$group, $insight]))
        ->assertRedirect(route('groups.show', $group));
})->with([
    'guest' => false,
    'signed-in non-member' => true,
]);

test('approved members can read a private group\'s content', function () {
    $group = Group::factory()->private()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $insight = insightFor($group);
    $this->actingAs(createApprovedMember($group));

    $this->get(route('groups.verse.show', $group))->assertOk();
    $this->get(route('groups.insights.show', [$group, $insight]))->assertOk();
});

test('only approved members and admins can view private content', function (Closure $makeUser, bool $expected) {
    $group = Group::factory()->private()->create();

    expect((new GroupPolicy)->viewContent($makeUser($group), $group))->toBe($expected);
})->with([
    'guest' => [fn () => null, false],
    'non-member' => [fn () => User::factory()->create(), false],
    'pending applicant' => [function (Group $group) {
        $user = User::factory()->create();
        $group->users()->attach($user, ['status' => 'pending', 'role' => 'member', 'applied_at' => now()]);

        return $user;
    }, false],
    'approved member' => [fn (Group $group) => createApprovedMember($group), true],
    'admin' => [fn () => User::factory()->admin()->create(), true],
]);

test('anyone can view a public group\'s content', function () {
    $group = Group::factory()->create();

    expect((new GroupPolicy)->viewContent(null, $group))->toBeTrue();
});

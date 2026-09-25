<?php

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create a user who is an approved manager of the given group.
 */
function createApprovedManager(Group $group): User
{
    return createApprovedMember($group, GroupMembershipRole::Manager);
}

/**
 * Create a user who is an approved member (or manager) of the given group.
 */
function createApprovedMember(Group $group, GroupMembershipRole $role = GroupMembershipRole::Member): User
{
    $user = User::factory()->create();

    $membership = new GroupMembership;
    $membership->group_id = $group->id;
    $membership->user_id = $user->id;
    $membership->role = $role;
    $membership->status = GroupMembershipStatus::Approved;
    $membership->applied_at = now();
    $membership->decided_at = now();
    $membership->save();

    return $user;
}

/**
 * Create an insight on a fresh group verse, for tests that need one but
 * aren't exercising verse or insight creation themselves.
 */
function insightFor(Group $group): Insight
{
    $verse = GroupVerse::factory()->for($group)->create([
        'date' => today()->subDays(fake()->unique()->numberBetween(0, 100000)),
    ]);

    return Insight::factory()->for($verse, 'verseable')->create();
}

/**
 * Join a specific, already-existing user to a group as an approved member —
 * unlike createApprovedMember(), which always creates a brand-new user.
 */
function joinAsApprovedMember(Group $group, User $user, GroupMembershipRole $role = GroupMembershipRole::Member): GroupMembership
{
    $membership = new GroupMembership;
    $membership->group_id = $group->id;
    $membership->user_id = $user->id;
    $membership->role = $role;
    $membership->status = GroupMembershipStatus::Approved;
    $membership->save();

    return $membership;
}

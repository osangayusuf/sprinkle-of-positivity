<?php

use App\Models\Comment;
use App\Models\Group;
use App\Models\PageVisit;
use App\Models\User;
use App\Models\UserLogin;

test('non-admins cannot view analytics', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.analytics'))
        ->assertForbidden();
});

test('logging in records a login', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect(UserLogin::query()->sole()->user_id)->toBe($user->id);
});

test('admins see visitor, login and activity numbers for the chosen range', function () {
    $this->travelTo(now()->setTime(12, 0));
    $admin = User::factory()->admin()->create(['created_at' => now()->subYear()]);
    $member = User::factory()->create(['created_at' => now()->subYear()]);
    $newMember = User::factory()->create(['created_at' => now()->subDays(2)]);

    PageVisit::factory()->create(['visitor_id' => 'guest-a', 'path' => '/home']);
    PageVisit::factory()->create(['visitor_id' => 'guest-a', 'path' => '/groups']);
    PageVisit::factory()->create(['visitor_id' => 'member-b', 'user_id' => $member->id, 'path' => '/home']);
    PageVisit::factory()->create(['visitor_id' => 'too-old', 'path' => '/home', 'created_at' => now()->subDays(10)]);

    UserLogin::factory()->for($member)->create();
    UserLogin::factory()->for($member)->create();
    UserLogin::factory()->for($newMember)->create(['created_at' => now()->subDays(10)]);

    $this->actingAs($admin)
        ->get(route('admin.analytics', ['days' => 7]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/analytics')
            ->where('days', 7)
            ->where('summary.page_views', 3)
            ->where('summary.unique_visitors', 2)
            ->where('summary.guest_visitors', 1)
            ->where('summary.signed_in_visitors', 1)
            ->where('summary.logins', 2)
            ->where('summary.users_logged_in', 1)
            ->where('summary.new_signups', 1)
            ->has('daily', 7)
            ->where('daily.6.page_views', 3)
            ->where('daily.6.unique_visitors', 2)
            ->where('topPages.0', ['path' => '/home', 'page_views' => 2, 'unique_visitors' => 2])
            ->where('recentLogins.0.user.name', $member->name)
        );
});

test('active members counts each member once across every kind of interaction', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $commenter = User::factory()->create();
    Comment::factory()->for($insight, 'commentable')->for($commenter)->count(2)->create();
    Comment::factory()->for($insight, 'commentable')->for($insight->user)->create();

    $this->actingAs($admin)
        ->get(route('admin.analytics'))
        ->assertInertia(fn ($page) => $page
            ->where('summary.active_users', 2)
            ->where('summary.interactions.insights', 1)
            ->where('summary.interactions.comments', 3)
        );
});

test('an unsupported range falls back to 30 days', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.analytics', ['days' => 5000]))
        ->assertInertia(fn ($page) => $page->where('days', 30)->has('daily', 30));
});

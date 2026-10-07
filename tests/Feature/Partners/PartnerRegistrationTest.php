<?php

use App\Enums\PartnerStatus;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;

test('anyone can see the partner sign-up page', function () {
    $this->get(route('partner.register'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/partner-register'));
});

test('signing up as a partner creates a pending partner account and alerts admins', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->post(route('partner.register.store'), [
        'name' => 'Ada Obi',
        'email' => 'ada@example.com',
        'password' => 'a-Strong-pass-123',
        'password_confirmation' => 'a-Strong-pass-123',
    ])->assertRedirect(route('partner.pending'));

    $partner = User::query()->where('email', 'ada@example.com')->sole();

    expect($partner->partner_status)->toBe(PartnerStatus::Pending)
        ->and($partner->hasRole(Role::PARTNER))->toBeTrue()
        ->and($partner->isApprovedPartner())->toBeFalse();
    $this->assertAuthenticatedAs($partner);
    Notification::assertSentTo($admin, ChallengeAlert::class);
});

test('partner sign-up validates its input', function () {
    $this->post(route('partner.register.store'), ['name' => '', 'email' => 'not-an-email', 'password' => 'x'])
        ->assertSessionHasErrors(['name', 'email', 'password']);

    expect(User::query()->count())->toBe(0);
});

test('a pending partner is held on the waiting screen', function () {
    $partner = User::factory()->pendingPartner()->create();
    $this->actingAs($partner);

    $this->get(route('home'))->assertRedirect(route('partner.pending'));
    $this->get(route('groups.index'))->assertRedirect(route('partner.pending'));
    $this->get(route('partner.pending'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('rejected', false));
});

test('a rejected partner stays locked out and is told so', function () {
    $partner = User::factory()->pendingPartner()->create(['partner_status' => PartnerStatus::Rejected]);

    $this->actingAs($partner)->get(route('home'))->assertRedirect(route('partner.pending'));
    $this->actingAs($partner)->get(route('partner.pending'))
        ->assertInertia(fn ($page) => $page->where('rejected', true));
});

test('an approved partner is not sent to the waiting screen', function () {
    $partner = User::factory()->partner()->create();

    $this->actingAs($partner)->get(route('home'))->assertOk();
    $this->actingAs($partner)->get(route('partner.pending'))->assertRedirect(route('home'));
});

test('a regular member never sees the waiting screen', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('partner.pending'))
        ->assertRedirect(route('home'));
});

test('admins approve a pending partner and the partner is told', function () {
    Notification::fake();
    $partner = User::factory()->pendingPartner()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-partner', $partner), ['decision' => 'approved'])
        ->assertRedirect();

    expect($partner->fresh()->isApprovedPartner())->toBeTrue();
    Notification::assertSentTo($partner, ChallengeAlert::class);
});

test('admins can reject a pending partner', function () {
    $partner = User::factory()->pendingPartner()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-partner', $partner), ['decision' => 'rejected']);

    expect($partner->fresh()->partner_status)->toBe(PartnerStatus::Rejected);
});

test('only admins can decide on a partner', function () {
    $partner = User::factory()->pendingPartner()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.users.update-partner', $partner), ['decision' => 'approved'])
        ->assertForbidden();

    expect($partner->fresh()->partner_status)->toBe(PartnerStatus::Pending);
});

test('deciding on a user who is not a partner is a 404', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-partner', User::factory()->create()), ['decision' => 'approved'])
        ->assertNotFound();
});

test('an admin can make a member an approved partner from the users page', function () {
    $user = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-role', $user), ['role' => Role::PARTNER])
        ->assertRedirect();

    expect($user->fresh()->isApprovedPartner())->toBeTrue();
});

test('a partner who manages a group cannot be demoted until removed from it', function () {
    $group = Group::factory()->create();
    $manager = createApprovedManager($group);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-role', $manager), ['role' => Role::MEMBER])
        ->assertSessionHasErrors('role');

    expect($manager->fresh()->isApprovedPartner())->toBeTrue();
});

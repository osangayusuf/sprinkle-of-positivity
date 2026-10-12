<?php

use App\Enums\PartnerStatus;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChallengeAlert;
use Illuminate\Support\Facades\Notification;

test('a signed-in member can apply to be a partner and keeps their access while waiting', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('partner.apply'))
        ->assertRedirect()
        ->assertInertiaFlash('toast');

    expect($member->refresh())
        ->partner_status->toBe(PartnerStatus::Pending)
        ->and($member->isPartner())->toBeFalse();

    Notification::assertSentTo($admin, ChallengeAlert::class);

    $this->actingAs($member)->get(route('home'))->assertOk();
});

test('the apply option is shared with the page only to users who can use it', function () {
    $member = User::factory()->create();

    $this->actingAs($member)->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('auth.canApplyAsPartner', true));

    $this->actingAs(User::factory()->admin()->create())->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('auth.canApplyAsPartner', false));
});

test('admins, partners and pending applicants cannot apply', function (User $user) {
    Notification::fake();

    $this->actingAs($user)->post(route('partner.apply'))->assertForbidden();

    Notification::assertNothingSent();
})->with([
    'admin' => fn () => User::factory()->admin()->create(),
    'approved partner' => fn () => User::factory()->partner()->create(),
    'pending applicant' => fn () => User::factory()->create(['partner_status' => PartnerStatus::Pending]),
]);

test('a declined applicant can apply again', function () {
    $member = User::factory()->create(['partner_status' => PartnerStatus::Rejected]);

    $this->actingAs($member)->post(route('partner.apply'))->assertRedirect();

    expect($member->refresh()->partner_status)->toBe(PartnerStatus::Pending);
});

test('guests cannot apply', function () {
    $this->post(route('partner.apply'))->assertRedirect(route('login'));
});

test('approving an applicant makes them a partner', function () {
    $applicant = User::factory()->create(['partner_status' => PartnerStatus::Pending]);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-partner', $applicant), ['decision' => 'approved'])
        ->assertRedirect();

    expect($applicant->refresh())
        ->isApprovedPartner()->toBeTrue()
        ->and($applicant->roles->pluck('name')->all())->toBe([Role::PARTNER]);
});

test('declining an applicant leaves them a member with full access', function () {
    $applicant = User::factory()->create(['partner_status' => PartnerStatus::Pending]);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-partner', $applicant), ['decision' => 'rejected'])
        ->assertRedirect();

    expect($applicant->refresh())
        ->partner_status->toBe(PartnerStatus::Rejected)
        ->and($applicant->isPartner())->toBeFalse();

    $this->actingAs($applicant)->get(route('home'))->assertOk();
});

test('approving someone who became an admin meanwhile keeps their admin role', function () {
    $admin = User::factory()->admin()->create(['partner_status' => PartnerStatus::Pending]);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update-partner', $admin), ['decision' => 'approved'])
        ->assertRedirect();

    expect($admin->refresh()->hasRole(Role::ADMIN))->toBeTrue()
        ->and($admin->isPartner())->toBeFalse();
});

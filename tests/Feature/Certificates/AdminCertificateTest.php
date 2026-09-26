<?php

use App\Models\Certificate;
use App\Models\User;

test('non-admins cannot use any admin certificate route', function () {
    $this->actingAs(User::factory()->create());
    $certificate = Certificate::factory()->create();
    $group = $certificate->group;

    $this->get(route('admin.certificates.index'))->assertForbidden();
    $this->get(route('admin.certificates.show', $group))->assertForbidden();
    $this->post(route('admin.certificates.issue', [$group, $certificate->user]), ['reason' => 'x'])->assertForbidden();
    $this->patch(route('admin.certificates.revoke', $certificate), ['reason' => 'x'])->assertForbidden();
    $this->patch(route('admin.certificates.restore', $certificate))->assertForbidden();
    $this->patch(route('admin.certificates.update-name', $certificate), ['recipient_name' => 'X'])->assertForbidden();
});

test('an admin sees each member\'s progress and certificate status', function () {
    $group = challengeGroup(durationDays: 2, startedDaysAgo: 4);
    $member = User::factory()->create();
    joinAsApprovedMember($group, $member);
    completeDay($group, $member, 1);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.certificates.show', $group))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('members.0.completed_count', 1)
            ->where('members.0.required_count', 2)
            ->where('members.0.eligible', false)
            ->where('members.0.certificate', null));
});

test('an admin can revoke a false positive, and the daily command does not reissue it', function () {
    $group = challengeGroup(durationDays: 2, startedDaysAgo: 4);
    $member = User::factory()->create();
    joinAsApprovedMember($group, $member);
    completeDay($group, $member, 1);
    completeDay($group, $member, 2);
    $this->artisan('certificates:issue')->assertSuccessful();
    $certificate = Certificate::query()->sole();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.certificates.revoke', $certificate), ['reason' => 'Insights were copied.'])
        ->assertRedirect();

    $certificate->refresh();
    expect($certificate->isRevoked())->toBeTrue()
        ->and($certificate->revoke_reason)->toBe('Insights were copied.')
        ->and($certificate->revoked_by)->toBe($admin->id);

    $this->artisan('certificates:issue')->assertSuccessful();
    expect(Certificate::query()->count())->toBe(1)
        ->and($certificate->fresh()->isRevoked())->toBeTrue();
});

test('revoking needs a reason', function () {
    $certificate = Certificate::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.certificates.revoke', $certificate), ['reason' => ''])
        ->assertSessionHasErrors('reason');

    expect($certificate->fresh()->isRevoked())->toBeFalse();
});

test('an admin can restore a revoked certificate', function () {
    $certificate = Certificate::factory()->revoked()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.certificates.restore', $certificate))
        ->assertRedirect();

    expect($certificate->fresh())
        ->isRevoked()->toBeFalse()
        ->revoke_reason->toBeNull();
});

test('an admin can issue a certificate to an ineligible member with a recorded reason', function () {
    $group = challengeGroup(durationDays: 2, startedDaysAgo: 4);
    $member = User::factory()->create();
    joinAsApprovedMember($group, $member);
    completeDay($group, $member, 1);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.certificates.issue', [$group, $member]), ['reason' => 'Outage on day 2.'])
        ->assertRedirect();

    $certificate = Certificate::query()->sole();
    expect($certificate->user_id)->toBe($member->id)
        ->and($certificate->override_reason)->toBe('Outage on day 2.')
        ->and($certificate->issued_by)->toBe($admin->id);
});

test('an admin cannot issue to someone who is not an approved member of the group', function () {
    $group = challengeGroup(durationDays: 2, startedDaysAgo: 4);
    $stranger = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.certificates.issue', [$group, $stranger]), ['reason' => 'Because.'])
        ->assertNotFound();

    expect(Certificate::query()->count())->toBe(0);
});

test('an admin can correct the name on a certificate', function () {
    $certificate = Certificate::factory()->create(['recipient_name' => 'Ada Obbi']);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.certificates.update-name', $certificate), ['recipient_name' => 'Ada Obi'])
        ->assertRedirect();

    expect($certificate->fresh()->recipient_name)->toBe('Ada Obi');
});

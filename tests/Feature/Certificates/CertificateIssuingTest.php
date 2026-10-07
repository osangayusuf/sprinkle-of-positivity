<?php

use App\Actions\IssueCertificate;
use App\Models\Certificate;
use App\Models\User;
use App\Notifications\CertificateIssued;
use App\Services\ChallengeProgress;
use Illuminate\Support\Facades\Notification;

test('the verse page exposes the member\'s streak progress', function () {
    $group = challengeGroup(durationDays: 5, startedDaysAgo: 2);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 2);

    $this->actingAs($user)
        ->get(route('groups.verse.show', $group))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('progress.completed_days', [1, 2])
            ->where('progress.current_streak', 2));
});

test('a member who completed every day of the challenge is eligible', function () {
    $group = challengeGroup(durationDays: 3, startedDaysAgo: 5);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);

    foreach ([1, 2, 3] as $day) {
        completeDay($group, $user, $day);
    }

    expect(app(ChallengeProgress::class)->forMember($group, $user)['eligible'])->toBeTrue();
});

test('a missed day makes a member ineligible', function () {
    $group = challengeGroup(durationDays: 3, startedDaysAgo: 5);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 3);

    $progress = app(ChallengeProgress::class)->forMember($group, $user);

    expect($progress['eligible'])->toBeFalse()
        ->and($progress['missing_days'])->toBe([2]);
});

test('days the manager never set a verse for are not required', function () {
    $group = challengeGroup(durationDays: 3, startedDaysAgo: 5, daysWithoutVerse: [2]);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 3);

    expect(app(ChallengeProgress::class)->forMember($group, $user)['eligible'])->toBeTrue();
});

test('a member is eligible as soon as their run reaches the challenge length', function () {
    $group = challengeGroup(durationDays: 3, startedDaysAgo: 2);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);

    foreach ([1, 2, 3] as $day) {
        completeDay($group, $user, $day);
    }

    expect(app(ChallengeProgress::class)->forMember($group, $user)['eligible'])->toBeTrue();
});

test('a member who restarted after a missed day is not eligible until a full new run', function () {
    $group = challengeGroup(durationDays: 4, startedDaysAgo: 5);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 2);
    completeDay($group, $user, 4);

    $progress = app(ChallengeProgress::class)->forMember($group, $user);

    expect($progress['eligible'])->toBeFalse()
        ->and($progress['reset_days'])->toBe([3])
        ->and($progress['current_streak'])->toBe(1);
});

test('issuing a certificate is idempotent and notifies the member once', function () {
    Notification::fake();
    $group = challengeGroup(durationDays: 2, startedDaysAgo: 4);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);
    completeDay($group, $user, 2);

    $first = app(IssueCertificate::class)->handle($group, $user);
    $second = app(IssueCertificate::class)->handle($group, $user);

    expect($first->is($second))->toBeTrue()
        ->and(Certificate::query()->count())->toBe(1)
        ->and($first->recipient_name)->toBe($user->name)
        ->and($first->duration_days)->toBe(2);
    Notification::assertSentToTimes($user, CertificateIssued::class, 1);
});

test('the system does not issue to an ineligible member', function () {
    $group = challengeGroup(durationDays: 2, startedDaysAgo: 4);
    $user = User::factory()->create();
    joinAsApprovedMember($group, $user);
    completeDay($group, $user, 1);

    expect(app(IssueCertificate::class)->handle($group, $user))->toBeNull()
        ->and(Certificate::query()->count())->toBe(0);
});

test('the issue command only certifies approved members who completed a full run', function () {
    Notification::fake();
    $finished = challengeGroup(durationDays: 2, startedDaysAgo: 4);
    $running = challengeGroup(durationDays: 2, startedDaysAgo: 1);
    $done = User::factory()->create();
    $early = User::factory()->create();
    $pending = User::factory()->create();
    joinAsApprovedMember($finished, $done);
    joinAsApprovedMember($running, $early);
    $pending->groups()->attach($finished->id, ['role' => 'member', 'status' => 'pending', 'applied_at' => now()]);

    foreach ([1, 2] as $day) {
        completeDay($finished, $done, $day);
        completeDay($finished, $pending, $day);
    }
    completeDay($running, $early, 1);

    $this->artisan('certificates:issue')->assertSuccessful();

    expect(Certificate::query()->pluck('user_id')->all())->toBe([$done->id]);
});

test('an issued certificate page is public by its code', function () {
    $certificate = Certificate::factory()->create(['recipient_name' => 'Ada Obi']);

    $this->get(route('certificates.show', $certificate->code))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('certificates/show')
            ->where('certificate.recipient_name', 'Ada Obi'));
});

test('an unknown certificate code is a 404', function () {
    $this->get('/certificates/not-a-real-code')->assertNotFound();
});

test('a revoked certificate page hides the award and its reason', function () {
    $certificate = Certificate::factory()->revoked()->create(['recipient_name' => 'Ada Obi']);

    $this->get(route('certificates.show', $certificate->code))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('certificate.revoked', true)
            ->missing('certificate.recipient_name')
            ->missing('certificate.revoke_reason'));
});

test('members only see their own, unrevoked certificates', function () {
    $user = User::factory()->create();
    $mine = Certificate::factory()->for($user)->create();
    Certificate::factory()->for($user)->revoked()->create();
    Certificate::factory()->create();

    $this->actingAs($user)
        ->get(route('certificates.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('certificates', 1)
            ->where('certificates.0.code', $mine->code));
});

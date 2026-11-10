<?php

use App\Actions\DecideGroupApplication;
use App\Actions\RejectAssignment;
use App\Enums\GroupMembershipStatus;
use App\Models\Group;
use App\Models\User;
use App\Notifications\ParticipantAssigned;
use Illuminate\Support\Facades\Notification;

test('the assigned partner is notified in-app and by email', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $partner = createApprovedManager($group);
    $applicant = User::factory()->create();
    $applicant->groups()->attach($group->id, ['role' => 'member', 'status' => 'pending', 'applied_at' => now()]);
    $membership = $applicant->groupMemberships()->where('group_id', $group->id)->sole();

    app(DecideGroupApplication::class)->handle($membership, GroupMembershipStatus::Approved, $partner);

    Notification::assertSentTo(
        $partner,
        ParticipantAssigned::class,
        fn (ParticipantAssigned $notification, array $channels) => $channels === ['database', 'mail']
            && $notification->participant->is($applicant),
    );
});

test('the email and in-app message tell the partner to engage within 24 hours', function () {
    $group = Group::factory()->create(['name' => 'Morning Manna']);
    $partner = createApprovedManager($group);
    $participant = User::factory()->create(['name' => 'Ada Obi']);
    $notification = new ParticipantAssigned($participant, $group);

    $mail = $notification->toMail($partner);

    expect($mail->subject)->toBe('New participant assigned');
    expect(implode(' ', $mail->introLines))
        ->toContain('Ada Obi')
        ->toContain('Morning Manna')
        ->toContain('within the next 24 hours');
    expect($mail->actionUrl)->toBe(route('partner.participants'));

    expect($notification->toArray($partner)['body'])
        ->toContain('Ada Obi')
        ->toContain('within the next 24 hours');
});

test('a partner who takes over after a decline is told as well', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $first = createApprovedManager($group);
    $second = createApprovedManager($group);
    $membership = joinAsApprovedMember($group, User::factory()->create());
    $membership->partner_id = $first->id;
    $membership->save();

    app(RejectAssignment::class)->handle($membership, $first);

    expect($membership->fresh()->partner_id)->toBe($second->id);
    Notification::assertSentTo($second, ParticipantAssigned::class);
    Notification::assertNotSentTo($first, ParticipantAssigned::class);
});

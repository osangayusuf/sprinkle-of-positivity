<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\PointsLedgerEntry;
use App\Models\Quiz;
use App\Models\QuizOption;

test('sharing an insight awards points', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $response = $this->post(route('groups.insights.store', $group), ['body' => 'Faithful.']);

    expect($member->fresh()->points)->toBe(10);
    $entry = PointsLedgerEntry::query()->sole();
    expect($entry->reason)->toBe(PointsLedgerEntry::REASON_INSIGHT_SUBMITTED);
    expect($entry->points)->toBe(10);
    $response->assertInertiaFlash('toast.message', 'Your insight has been shared. +10 points');
});

test('commenting awards points', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $response = $this->post(route('groups.insights.comments.store', [$group, $insight]), ['body' => 'Amen.']);

    expect($member->fresh()->points)->toBe(5);
    $response->assertInertiaFlash('toast.message', 'Comment posted. +5 points');
});

test('answering a quiz awards points', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    $option = QuizOption::factory()->for($quiz)->create();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $response = $this->post(route('groups.quizzes.responses.store', [$group, $quiz]), ['quiz_option_id' => $option->id]);

    expect($member->fresh()->points)->toBe(5);
    $response->assertInertiaFlash('toast.message', 'Answer submitted. +5 points');
});

test('answering a quiz correctly awards a bonus on top of participation points', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    $option = QuizOption::factory()->for($quiz)->create();
    $quiz->correct_quiz_option_id = $option->id;
    $quiz->save();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $response = $this->post(route('groups.quizzes.responses.store', [$group, $quiz]), ['quiz_option_id' => $option->id]);

    expect($member->fresh()->points)->toBe(20);
    expect(PointsLedgerEntry::query()->count())->toBe(2);
    $response->assertInertiaFlash('toast.message', 'Answer submitted. +20 points — correct!');
});

test('crossing a level threshold flashes a celebration', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $member = createApprovedMember($group);
    $member->points = 495;
    $member->save();
    $this->actingAs($member);

    $response = $this->post(route('groups.insights.store', $group), ['body' => 'Faithful.']);

    $response->assertInertiaFlash('celebration');
});

test('staying within the same level does not flash a celebration', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $member = createApprovedMember($group);
    $member->points = 100;
    $member->save();
    $this->actingAs($member);

    $response = $this->post(route('groups.insights.store', $group), ['body' => 'Faithful.']);

    $response->assertInertiaFlashMissing('celebration');
});

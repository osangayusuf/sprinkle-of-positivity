<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\PointsLedgerEntry;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizResponse;
use App\Models\User;

/**
 * A poll on today's verse with two options, plus its group.
 *
 * @return array{0: Group, 1: Quiz, 2: QuizOption, 3: QuizOption}
 */
function pollWithOptions(): array
{
    $group = Group::factory()->create();
    $verse = GroupVerse::factory()->for($group)->create(['date' => today()]);
    $poll = Quiz::factory()->poll()->for($group)->create(['group_verse_id' => $verse->id]);

    return [
        $group,
        $poll,
        QuizOption::factory()->for($poll)->create(['label' => 'Yes', 'position' => 0]),
        QuizOption::factory()->for($poll)->create(['label' => 'No', 'position' => 1]),
    ];
}

test('a manager can post a poll without a correct answer', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $manager = createApprovedManager($group);

    $this->actingAs($manager)->post(route('groups.quizzes.store', $group), [
        'type' => 'poll',
        'question' => 'Was the training session helpful?',
        'options' => ['Yes', 'Somewhat', 'No'],
    ])->assertRedirect()->assertInertiaFlash('toast');

    $poll = Quiz::query()->sole();
    expect($poll->isPoll())->toBeTrue()
        ->and($poll->correct_quiz_option_id)->toBeNull()
        ->and($poll->options)->toHaveCount(3);
});

test('a quiz still needs a correct answer', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);

    $this->actingAs(createApprovedManager($group))->post(route('groups.quizzes.store', $group), [
        'question' => 'Who floated in a basket down the Nile?',
        'options' => ['His Sister Miriam', 'Mary'],
    ])->assertInvalid('correct_index');

    expect(Quiz::query()->count())->toBe(0);
});

test('a poll type must be one we know', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);

    $this->actingAs(createApprovedManager($group))->post(route('groups.quizzes.store', $group), [
        'type' => 'survey',
        'question' => 'Anything?',
        'options' => ['A', 'B'],
    ])->assertInvalid('type');
});

test('a member can vote in a poll without earning points', function () {
    [$group, $poll, $yes] = pollWithOptions();
    $member = createApprovedMember($group);

    $this->actingAs($member)
        ->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id])
        ->assertRedirect()
        ->assertInertiaFlash('toast');

    expect(QuizResponse::query()->sole())
        ->user_id->toBe($member->id)
        ->quiz_option_id->toBe($yes->id)
        ->and($member->fresh()->points)->toBe(0)
        ->and(PointsLedgerEntry::query()->count())->toBe(0);
});

test('a member can change their poll vote and still has a single response', function () {
    [$group, $poll, $yes, $no] = pollWithOptions();
    $member = createApprovedMember($group);

    $this->actingAs($member)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id]);
    $this->actingAs($member)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $no->id])
        ->assertRedirect();

    expect(QuizResponse::query()->sole()->quiz_option_id)->toBe($no->id)
        ->and($member->fresh()->points)->toBe(0);
});

test('a closed poll rejects new and changed votes', function () {
    [$group, $poll, $yes, $no] = pollWithOptions();
    $member = createApprovedMember($group);
    $this->actingAs($member)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id]);

    $poll->closed_at = now();
    $poll->save();

    $this->actingAs($member)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $no->id])
        ->assertForbidden();
    $this->actingAs(createApprovedMember($group))->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $no->id])
        ->assertForbidden();

    expect(QuizResponse::query()->sole()->quiz_option_id)->toBe($yes->id);
});

test('someone outside the group cannot vote', function () {
    [$group, $poll, $yes] = pollWithOptions();

    $this->actingAs(User::factory()->create())
        ->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id])
        ->assertForbidden();

    expect(QuizResponse::query()->count())->toBe(0);
});

test('poll results stay hidden until the member votes', function () {
    [$group, $poll, $yes] = pollWithOptions();
    $voter = createApprovedMember($group);
    $viewer = createApprovedMember($group);
    $this->actingAs($voter)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id]);

    $this->actingAs($viewer)->get(route('groups.verse.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('quizzes.0.type', 'poll')
            ->where('quizzes.0.options.0.votes', null)
            ->where('quizzes.0.responses_count', 1));

    $this->actingAs($viewer)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id]);

    $this->actingAs($viewer)->get(route('groups.verse.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('quizzes.0.options.0.votes', 2)
            ->where('quizzes.0.options.1.votes', 0));
});

test('managers always see poll results and a closed poll shows them to everyone', function () {
    [$group, $poll, $yes] = pollWithOptions();
    $voter = createApprovedMember($group);
    $this->actingAs($voter)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id]);

    $this->actingAs(createApprovedManager($group))->get(route('groups.verse.show', $group))
        ->assertInertia(fn ($page) => $page->where('quizzes.0.options.0.votes', 1));

    $poll->closed_at = now();
    $poll->save();

    $this->actingAs(createApprovedMember($group))->get(route('groups.verse.show', $group))
        ->assertInertia(fn ($page) => $page->where('quizzes.0.closed', true)->where('quizzes.0.options.0.votes', 1));
});

test('a manager can close and reopen a poll', function () {
    [$group, $poll] = pollWithOptions();
    $manager = createApprovedManager($group);

    $this->actingAs($manager)->patch(route('groups.quizzes.close', [$group, $poll]))->assertRedirect();
    expect($poll->fresh()->isClosed())->toBeTrue();

    $this->actingAs($manager)->patch(route('groups.quizzes.reopen', [$group, $poll]))->assertRedirect();
    expect($poll->fresh()->isClosed())->toBeFalse();
});

test('members cannot close a poll, and a quiz cannot be closed', function () {
    [$group, $poll] = pollWithOptions();

    $this->actingAs(createApprovedMember($group))->patch(route('groups.quizzes.close', [$group, $poll]))->assertForbidden();
    expect($poll->fresh()->isClosed())->toBeFalse();

    $quiz = Quiz::factory()->for($group)->create();
    $this->actingAs(createApprovedManager($group))->patch(route('groups.quizzes.close', [$group, $quiz]))->assertNotFound();
});

test('a poll without votes can be edited without picking a correct answer', function () {
    [$group, $poll] = pollWithOptions();
    $manager = createApprovedManager($group);

    $this->actingAs($manager)->put(route('groups.quizzes.update', [$group, $poll]), [
        'question' => 'Reworded question?',
        'options' => ['Agree', 'Disagree'],
    ])->assertRedirect(route('groups.verse.show', $group));

    expect($poll->fresh())
        ->question->toBe('Reworded question?')
        ->correct_quiz_option_id->toBeNull()
        ->and($poll->options()->pluck('label')->all())->toBe(['Agree', 'Disagree']);
});

test('a poll with votes can still be deleted', function () {
    [$group, $poll, $yes] = pollWithOptions();
    $member = createApprovedMember($group);
    $this->actingAs($member)->post(route('groups.quizzes.responses.store', [$group, $poll]), ['quiz_option_id' => $yes->id]);

    $this->actingAs(createApprovedManager($group))->delete(route('groups.quizzes.destroy', [$group, $poll]))->assertRedirect();

    expect(Quiz::query()->count())->toBe(0)
        ->and($member->fresh()->points)->toBe(0);
});

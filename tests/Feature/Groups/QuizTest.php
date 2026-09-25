<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\PointsLedgerEntry;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizResponse;

test('a manager can post a quiz question with options for todays verse', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $this->post(route('groups.quizzes.store', $group), [
        'question' => 'Who floated in a basket down the Nile?',
        'options' => ['His Sister Miriam', 'Mary', 'Queen Esther', 'Diotrephes'],
        'correct_index' => 0,
    ])->assertRedirect();

    $quiz = Quiz::query()->sole();
    expect($quiz->group_id)->toBe($group->id);
    expect($quiz->created_by)->toBe($manager->id);
    expect($quiz->options)->toHaveCount(4);
    expect($quiz->correct_quiz_option_id)->toBe(
        $quiz->options->firstWhere('label', 'His Sister Miriam')->id
    );
});

test('correct_index must point at a real option', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $this->post(route('groups.quizzes.store', $group), [
        'question' => 'Who floated in a basket down the Nile?',
        'options' => ['His Sister Miriam', 'Mary'],
        'correct_index' => 5,
    ])->assertInvalid('correct_index');

    expect(Quiz::query()->count())->toBe(0);
});

test('a non-manager cannot post a quiz', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.quizzes.store', $group), [
        'question' => 'Who floated in a basket down the Nile?',
        'options' => ['His Sister Miriam', 'Mary'],
    ])->assertForbidden();

    expect(Quiz::query()->count())->toBe(0);
});

test('a member can answer a quiz once', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    $option = QuizOption::factory()->for($quiz)->create();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.quizzes.responses.store', [$group, $quiz]), [
        'quiz_option_id' => $option->id,
    ])->assertRedirect();

    $response = QuizResponse::query()->sole();
    expect($response->user_id)->toBe($member->id);
    expect($response->quiz_option_id)->toBe($option->id);
});

test('a member cannot answer the same quiz twice', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    $optionOne = QuizOption::factory()->for($quiz)->create();
    $optionTwo = QuizOption::factory()->for($quiz)->create();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.quizzes.responses.store', [$group, $quiz]), [
        'quiz_option_id' => $optionOne->id,
    ]);

    $this->post(route('groups.quizzes.responses.store', [$group, $quiz]), [
        'quiz_option_id' => $optionTwo->id,
    ])->assertForbidden();

    expect(QuizResponse::query()->count())->toBe(1);
});

test('answering with an option from a different quiz fails validation', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    $foreignOption = QuizOption::factory()->create();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.quizzes.responses.store', [$group, $quiz]), [
        'quiz_option_id' => $foreignOption->id,
    ])->assertInvalid('quiz_option_id');
});

test('the correct answer is hidden until the current user has answered', function () {
    $group = Group::factory()->create();
    $verse = GroupVerse::factory()->for($group)->create(['date' => today()]);
    $quiz = Quiz::factory()->for($group)->for($verse, 'groupVerse')->create();
    $option = QuizOption::factory()->for($quiz)->create();
    $quiz->correct_quiz_option_id = $option->id;
    $quiz->save();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->get(route('groups.verse.show', $group))
        ->assertInertia(fn ($page) => $page->where('quizzes.0.correct_quiz_option_id', null));

    $this->post(route('groups.quizzes.responses.store', [$group, $quiz]), [
        'quiz_option_id' => $option->id,
    ]);

    $this->get(route('groups.verse.show', $group))
        ->assertInertia(fn ($page) => $page->where('quizzes.0.correct_quiz_option_id', $option->id));
});

test('a manager can edit a quiz that has zero responses', function () {
    $group = Group::factory()->create();
    $verse = GroupVerse::factory()->for($group)->create();
    $quiz = Quiz::factory()->for($group)->for($verse, 'groupVerse')->create(['question' => 'Old question?']);
    QuizOption::factory()->for($quiz)->create(['label' => 'Old A', 'position' => 0]);
    QuizOption::factory()->for($quiz)->create(['label' => 'Old B', 'position' => 1]);
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $response = $this->put(route('groups.quizzes.update', [$group, $quiz]), [
        'question' => 'New question?',
        'options' => ['New A', 'New B', 'New C'],
        'correct_index' => 2,
    ]);

    $response->assertRedirect(route('groups.verse.show', $group));
    $response->assertInertiaFlash('toast');

    $quiz->refresh();
    expect($quiz->question)->toBe('New question?');
    expect($quiz->options)->toHaveCount(3);
    expect($quiz->correctOption->label)->toBe('New C');
});

test('editing a quiz is blocked once it has a response', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create(['question' => 'Old question?']);
    $option = QuizOption::factory()->for($quiz)->create();
    $member = createApprovedMember($group);
    QuizResponse::factory()->for($quiz)->for($member)->create(['quiz_option_id' => $option->id]);
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $this->get(route('groups.quizzes.edit', [$group, $quiz]))->assertForbidden();

    $this->put(route('groups.quizzes.update', [$group, $quiz]), [
        'question' => 'New question?',
        'options' => ['A', 'B'],
        'correct_index' => 0,
    ])->assertForbidden();

    expect($quiz->fresh()->question)->toBe('Old question?');
});

test('a non-manager cannot edit or delete a quiz', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    QuizOption::factory()->for($quiz)->create();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->get(route('groups.quizzes.edit', [$group, $quiz]))->assertForbidden();
    $this->delete(route('groups.quizzes.destroy', [$group, $quiz]))->assertForbidden();

    expect(Quiz::query()->count())->toBe(1);
});

test('a manager can delete a quiz with zero responses', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    QuizOption::factory()->for($quiz)->create();
    $manager = createApprovedManager($group);
    $this->actingAs($manager);

    $response = $this->delete(route('groups.quizzes.destroy', [$group, $quiz]));

    $response->assertRedirect(route('groups.verse.show', $group));
    $response->assertInertiaFlash('toast.message', 'Question deleted.');
    expect(Quiz::query()->count())->toBe(0);
});

test('deleting a quiz with responses reverses the points those responses earned', function () {
    $group = Group::factory()->create();
    $quiz = Quiz::factory()->for($group)->create();
    $correctOption = QuizOption::factory()->for($quiz)->create();
    QuizOption::factory()->for($quiz)->create();
    $quiz->correct_quiz_option_id = $correctOption->id;
    $quiz->save();

    $correctMember = createApprovedMember($group);
    $this->actingAs($correctMember)->post(route('groups.quizzes.responses.store', [$group, $quiz]), [
        'quiz_option_id' => $correctOption->id,
    ]);

    $participantMember = createApprovedMember($group);
    $this->actingAs($participantMember)->post(route('groups.quizzes.responses.store', [$group, $quiz]), [
        'quiz_option_id' => $quiz->options()->where('id', '!=', $correctOption->id)->first()->id,
    ]);

    expect($correctMember->fresh()->points)->toBe(20);
    expect($participantMember->fresh()->points)->toBe(5);

    $manager = createApprovedManager($group);
    $response = $this->actingAs($manager)->delete(route('groups.quizzes.destroy', [$group, $quiz]));

    $response->assertInertiaFlash('toast.message', 'Question deleted. Points earned from it were reversed.');
    expect($correctMember->fresh()->points)->toBe(0);
    expect($participantMember->fresh()->points)->toBe(0);
    expect(PointsLedgerEntry::query()->count())->toBe(0);
    expect(Quiz::query()->count())->toBe(0);
});

<?php

use App\Models\Comment;
use App\Models\Group;
use App\Models\User;

test('an approved member can comment on an insight', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.insights.comments.store', [$group, $insight]), [
        'body' => 'Amen to that.',
    ])->assertRedirect();

    $comment = Comment::query()->sole();
    expect($comment->user_id)->toBe($member->id);
    expect($comment->commentable_id)->toBe($insight->id);
    expect($comment->parent_id)->toBeNull();
});

test('a member can reply to an existing comment', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $root = Comment::factory()->for($insight, 'commentable')->create();

    $this->actingAs($member);

    $this->post(route('groups.insights.comments.store', [$group, $insight]), [
        'body' => 'Well said.',
        'parent_id' => $root->id,
    ])->assertRedirect();

    $reply = Comment::query()->where('parent_id', $root->id)->sole();
    expect($reply->body)->toBe('Well said.');
});

test('a reply cannot reference a comment from a different insight', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $otherInsight = insightFor($group);
    $foreignComment = Comment::factory()->for($otherInsight, 'commentable')->create();

    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->post(route('groups.insights.comments.store', [$group, $insight]), [
        'body' => 'Nice try.',
        'parent_id' => $foreignComment->id,
    ])->assertInvalid('parent_id');
});

test('a non-member cannot comment', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $outsider = User::factory()->create();
    $this->actingAs($outsider);

    $this->post(route('groups.insights.comments.store', [$group, $insight]), [
        'body' => 'Hi.',
    ])->assertForbidden();

    expect(Comment::query()->count())->toBe(0);
});

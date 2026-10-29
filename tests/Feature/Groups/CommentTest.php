<?php

use App\Models\Comment;
use App\Models\Group;
use App\Models\User;
use App\Notifications\MentionedInComment;
use Illuminate\Support\Facades\Notification;

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

test('members tagged in a comment are recorded and notified, outsiders are ignored', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $author = createApprovedMember($group);
    $tagged = createApprovedMember($group);
    $bystander = createApprovedMember($group);
    $outsider = User::factory()->create();

    $this->actingAs($author)->post(route('groups.insights.comments.store', [$group, $insight]), [
        'body' => "Thoughts @[{$tagged->id}] @[{$outsider->id}]?",
    ])->assertRedirect();

    expect(Comment::query()->sole()->mentions->modelKeys())->toBe([$tagged->id]);
    Notification::assertSentTo($tagged, MentionedInComment::class);
    Notification::assertNotSentTo([$bystander, $outsider, $author], MentionedInComment::class);
});

test('@everyone in a comment notifies the other approved members', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $author = createApprovedMember($group);
    $other = createApprovedMember($group);

    $this->actingAs($author)->post(route('groups.insights.comments.store', [$group, $insight]), [
        'body' => '@everyone please read this',
    ]);

    Notification::assertSentTo($other, MentionedInComment::class);
    Notification::assertNotSentTo($author, MentionedInComment::class);
});

test('the insight page lists comment mentions and the members who can be tagged', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $viewer = createApprovedMember($group);
    $tagged = createApprovedMember($group);
    $comment = Comment::factory()->for($insight, 'commentable')->create(['body' => "Hi @[{$tagged->id}]"]);
    $comment->mentions()->attach($tagged->id);

    $this->actingAs($viewer)->get(route('groups.insights.show', [$group, $insight]))
        ->assertInertia(fn ($page) => $page
            ->where('comments.0.mentions.0.id', $tagged->id)
            ->has('members', 1)
            ->where('members.0.id', $tagged->id));
});

test('an author can edit their comment and it is marked as edited', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $comment = Comment::factory()->for($insight, 'commentable')->for($member)->create(['body' => 'Old']);

    $this->actingAs($member)
        ->patch(route('groups.insights.comments.update', [$group, $insight, $comment]), ['body' => 'New'])
        ->assertRedirect();

    expect($comment->fresh())->body->toBe('New')->edited_at->not->toBeNull();

    $this->get(route('groups.insights.show', [$group, $insight]))
        ->assertInertia(fn ($page) => $page
            ->where('comments.0.edited', true)
            ->where('comments.0.can_edit', true));
});

test('only the author can edit a comment', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $comment = Comment::factory()->for($insight, 'commentable')->create(['body' => 'Mine']);

    $this->actingAs(createApprovedManager($group))
        ->patch(route('groups.insights.comments.update', [$group, $insight, $comment]), ['body' => 'Hijack'])
        ->assertForbidden();

    expect($comment->fresh()->body)->toBe('Mine');
});

test('a comment from another insight cannot be edited through this one', function () {
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $member = createApprovedMember($group);
    $foreign = Comment::factory()->for(insightFor($group), 'commentable')->for($member)->create();

    $this->actingAs($member)
        ->patch(route('groups.insights.comments.update', [$group, $insight, $foreign]), ['body' => 'Moved'])
        ->assertNotFound();
});

test('editing a comment re-syncs its mentions and only notifies newly tagged members', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $insight = insightFor($group);
    $author = createApprovedMember($group);
    $kept = createApprovedMember($group);
    $added = createApprovedMember($group);

    $this->actingAs($author)->post(route('groups.insights.comments.store', [$group, $insight]), ['body' => "@[{$kept->id}]"]);
    $comment = Comment::query()->sole();

    $this->actingAs($author)->patch(route('groups.insights.comments.update', [$group, $insight, $comment]), [
        'body' => "@[{$kept->id}] @[{$added->id}]",
    ]);

    expect($comment->fresh()->mentions->modelKeys())->toEqualCanonicalizing([$kept->id, $added->id]);
    Notification::assertSentToTimes($kept, MentionedInComment::class, 1);
    Notification::assertSentToTimes($added, MentionedInComment::class, 1);
});

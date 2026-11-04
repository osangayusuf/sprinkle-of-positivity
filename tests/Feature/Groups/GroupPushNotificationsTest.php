<?php

use App\Enums\GroupMembershipStatus;
use App\Models\Comment;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\GroupMessage;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use App\Notifications\MentionedInGroupChat;
use App\Notifications\NewGroupInsight;
use App\Notifications\NewGroupMessage;
use App\Notifications\NewInsightComment;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * Create an approved member who has a push subscription, so they are
 * eligible for group activity alerts.
 */
function subscribedMember(Group $group): User
{
    $member = createApprovedMember($group);
    $member->updatePushSubscription('https://push.example.test/'.$member->id, 'p256dh-key', 'auth-token');

    return $member;
}

test('a new chat message is pushed to the other subscribed members only', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = subscribedMember($group);
    $reader = subscribedMember($group);
    $noSubscription = createApprovedMember($group);
    $outsider = User::factory()->create();
    $outsider->updatePushSubscription('https://push.example.test/outsider', 'p256dh-key', 'auth-token');

    $this->actingAs($author)
        ->post(route('groups.chat.store', $group), ['body' => 'Good morning, everyone!'])
        ->assertRedirect();

    Notification::assertSentTo($reader, NewGroupMessage::class);
    Notification::assertNotSentTo($author, NewGroupMessage::class);
    Notification::assertNotSentTo($noSubscription, NewGroupMessage::class);
    Notification::assertNotSentTo($outsider, NewGroupMessage::class);
});

test('a pending member gets no group alerts', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = subscribedMember($group);
    $pending = subscribedMember($group);
    GroupMembership::query()->where('user_id', $pending->id)->update(['status' => GroupMembershipStatus::Pending->value]);

    $this->actingAs($author)
        ->post(route('groups.chat.store', $group), ['body' => 'Anyone here?'])
        ->assertRedirect();

    Notification::assertNotSentTo($pending, NewGroupMessage::class);
});

test('a muted group or a global chat switch silences chat pushes', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = subscribedMember($group);
    $muted = subscribedMember($group);
    $chatOff = subscribedMember($group);
    $listening = subscribedMember($group);

    GroupMembership::query()->where('user_id', $muted->id)->update(['notifications_muted' => true]);
    $chatOff->push_chat_enabled = false;
    $chatOff->save();

    $this->actingAs($author)
        ->post(route('groups.chat.store', $group), ['body' => 'Hello'])
        ->assertRedirect();

    Notification::assertSentTo($listening, NewGroupMessage::class);
    Notification::assertNotSentTo($muted, NewGroupMessage::class);
    Notification::assertNotSentTo($chatOff, NewGroupMessage::class);
});

test('a tagged member gets the mention alert instead of a second chat push', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $author = subscribedMember($group);
    $tagged = subscribedMember($group);
    $bystander = subscribedMember($group);

    $this->actingAs($author)
        ->post(route('groups.chat.store', $group), ['body' => "Hi @[{$tagged->id}]"])
        ->assertRedirect();

    Notification::assertSentTo($tagged, MentionedInGroupChat::class);
    Notification::assertNotSentTo($tagged, NewGroupMessage::class);
    Notification::assertSentTo($bystander, NewGroupMessage::class);
});

test('a new insight is pushed to the other members unless they switched insights off', function () {
    Notification::fake();
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $author = subscribedMember($group);
    $reader = subscribedMember($group);
    $insightsOff = subscribedMember($group);
    $insightsOff->push_insights_enabled = false;
    $insightsOff->save();
    $chatOff = subscribedMember($group);
    $chatOff->push_chat_enabled = false;
    $chatOff->save();

    $this->actingAs($author)
        ->post(route('groups.insights.store', $group), ['body' => 'God is faithful.'])
        ->assertRedirect();

    Notification::assertSentTo($reader, NewGroupInsight::class);
    Notification::assertSentTo($chatOff, NewGroupInsight::class);
    Notification::assertNotSentTo($author, NewGroupInsight::class);
    Notification::assertNotSentTo($insightsOff, NewGroupInsight::class);
});

test('a comment is pushed to the insight author and earlier commenters only', function () {
    Notification::fake();
    $group = Group::factory()->create();
    $insightAuthor = subscribedMember($group);
    $earlierCommenter = subscribedMember($group);
    $bystander = subscribedMember($group);
    $commenter = subscribedMember($group);

    $verse = GroupVerse::factory()->for($group)->create(['date' => today()]);
    $insight = Insight::factory()->for($verse, 'verseable')->for($insightAuthor)->create();
    Comment::factory()->for($insight, 'commentable')->for($earlierCommenter)->create();

    $this->actingAs($commenter)
        ->post(route('groups.insights.comments.store', [$group, $insight]), ['body' => 'Amen.'])
        ->assertRedirect();

    Notification::assertSentTo($insightAuthor, NewInsightComment::class);
    Notification::assertSentTo($earlierCommenter, NewInsightComment::class);
    Notification::assertNotSentTo($bystander, NewInsightComment::class);
    Notification::assertNotSentTo($commenter, NewInsightComment::class);
});

test('group alerts are push-only and deep link to the right page', function () {
    $group = Group::factory()->create();
    $author = subscribedMember($group);
    $reader = subscribedMember($group);
    $verse = GroupVerse::factory()->for($group)->create(['date' => today()]);
    $insight = Insight::factory()->for($verse, 'verseable')->for($author)->create();

    $chat = new NewGroupMessage($author, $group, GroupMessage::factory()->for($group)->for($author)->create());
    $newInsight = new NewGroupInsight($author, $group, $insight);

    expect($chat->via($reader))->toBe([WebPushChannel::class]);
    expect($chat->toWebPush($reader)->toArray()['data']['url'])->toBe(route('groups.chat.show', $group, absolute: false));
    expect($newInsight->toWebPush($reader)->toArray()['data']['url'])->toBe(route('groups.insights.show', [$group, $insight], absolute: false));
});

test('a member can mute and unmute a group', function () {
    $group = Group::factory()->create();
    $member = createApprovedMember($group);
    $this->actingAs($member);

    $this->patch(route('groups.notifications.update', $group), ['muted' => true])->assertRedirect();
    expect(GroupMembership::query()->where('user_id', $member->id)->sole()->notifications_muted)->toBeTrue();

    $this->get(route('groups.show', $group))
        ->assertInertia(fn ($page) => $page->where('notificationsMuted', true));

    $this->patch(route('groups.notifications.update', $group), ['muted' => false])->assertRedirect();
    expect(GroupMembership::query()->where('user_id', $member->id)->sole()->notifications_muted)->toBeFalse();
});

test('only approved members can mute a group', function () {
    $group = Group::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->patch(route('groups.notifications.update', $group), ['muted' => true])->assertNotFound();
});

test('a member can switch chat and insight alerts on and off globally', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->patch(route('push-preferences.update'), ['chat' => false])->assertRedirect();
    expect($user->fresh()->push_chat_enabled)->toBeFalse();
    expect($user->fresh()->push_insights_enabled)->toBeTrue();

    $this->patch(route('push-preferences.update'), ['insights' => false])->assertRedirect();
    expect($user->fresh()->push_insights_enabled)->toBeFalse();

    $this->patch(route('push-preferences.update'), ['chat' => 'nope'])->assertSessionHasErrors('chat');
});

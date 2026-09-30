<?php

use App\Models\Group;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\Quiz;

test('guests can browse the open pages', function (string $routeName) {
    $this->get(route($routeName))->assertOk();
})->with([
    'home' => 'home',
    'marketplace' => 'marketplace.index',
    'groups list' => 'groups.index',
]);

test('guests are sent to log in from the leaderboard', function () {
    $this->get(route('leaderboard.index'))->assertRedirect(route('login'));
});

test('guests see every active group under discover', function () {
    $group = Group::factory()->create();

    $this->get(route('groups.index'))
        ->assertInertia(fn ($page) => $page
            ->where('myGroups', [])
            ->where('discoverGroups.0.id', $group->id)
        );
});

test('guests can view a public group, its verse, and an insight without being able to participate', function () {
    $group = Group::factory()->create();
    GroupVerse::factory()->for($group)->create(['date' => today()]);
    $insight = insightFor($group);

    $this->get(route('groups.show', $group))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('membership', null)
            ->where('canManage', false)
            ->where('canViewContent', true)
        );

    $this->get(route('groups.verse.show', $group))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canParticipate', false)
            ->where('progress', null)
        );

    $this->get(route('groups.insights.show', [$group, $insight]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('canParticipate', false));
});

test('guests are sent to log in when they try to participate', function (string $method, Closure $url) {
    $group = Group::factory()->create();
    $verse = GroupVerse::factory()->for($group)->create(['date' => today()]);
    $insight = Insight::factory()->for($verse, 'verseable')->create();
    $quiz = Quiz::factory()->for($group)->for($verse, 'groupVerse')->create();

    $this->{$method}($url($group, $insight, $quiz))->assertRedirect(route('login'));
})->with([
    'join' => ['get', fn ($group) => route('groups.join', $group)],
    'share an insight' => ['post', fn ($group) => route('groups.insights.store', $group)],
    'comment' => ['post', fn ($group, $insight) => route('groups.insights.comments.store', [$group, $insight])],
    'react' => ['post', fn ($group, $insight) => route('groups.insights.reactions.store', [$group, $insight])],
    'answer a quiz' => ['post', fn ($group, $insight, $quiz) => route('groups.quizzes.responses.store', [$group, $quiz])],
]);
